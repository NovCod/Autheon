<?php
	namespace App\Controllers;
	
	use App\Models\User;
	use App\Config\Config;
	use App\Utils\Utils;
	use App\Utils\Mailer;
	use App\Utils\CSRFProtection;
	
	class AuthController {
		private $userModel;
		private $tableConfig;
		private $loginColumns;
		private $passwordColumn;
		private $csrf;
		
		public function __construct() {
			$this->userModel = new User();
			$this->tableConfig = Config::USER_TABLE_CONFIG;
			$loginPassword = $this->userModel->getLoginAndPasswordColumns();
			$this->csrf = new CSRFProtection();
			if ($loginPassword['check']) {
				$this->loginColumns = $loginPassword['login_columns'];
				$this->passwordColumn = $loginPassword['password_column'];
				} else {
				return ['check' => false, 'message' => $loginPassword['message']];
			}
		}
		
		public function register($fields) {
			$this->validateCSRFToken();
			$validationResult = \App\Utils\Validator::validateFields($fields);
			
			if (!$validationResult['is_valid']) {
				return ['check' => false, 'message' => $validationResult['error_messages']];
			}
			
			// Map the input field names to the actual database column names
			$mappingResult = $this->mapFieldsToColumns($fields);
			$mappedFields = $mappingResult['mappedFields'];
			$originalFields = $mappingResult['originalFields'];
			
			if (!$this->checkRequired($mappedFields)) {
				return ['check' => false, 'message' => "Error: One of the login columns or password field is missing."];
			}
			
			$passwordValue = $mappedFields[$this->passwordColumn];
			unset($mappedFields[$this->passwordColumn]);
			
			$hashedPassword = password_hash($passwordValue, Config::SECURITY_CONFIG['password_hash_algo']);
			$registrationResult = $this->userModel->register($hashedPassword, $mappedFields, $originalFields);
			
			if ($registrationResult['check']) {
				// Send activation email
				$columns=$this->userModel->getLoginAndPasswordColumns();
				$userEmail = $mappedFields[$columns['email_column']]; 
				$activationToken = $registrationResult['token'];
				if (isset(Config::EMAIL_CONFIG['smtp_password']) && Config::EMAIL_CONFIG['smtp_password'] !== '') {
				echo "hello" ;
					$mailer = new Mailer();
					$mailer->sendActivationEmail($userEmail, $activationToken);
				}
			}
			return $registrationResult;
		}
		
		public function login($fields) {
			$this->validateCSRFToken();
			$mappingResult = $this->mapFieldsToColumns($fields);
			$mappedFields = $mappingResult['mappedFields'];
			
			if (!$this->checkRequired($mappedFields)) {
				return ['check' => false, 'message' => "Error: One of the login columns or password field is missing."];
			}
			// Find the login column that has a value in the posted fields
			$loginValue = null;
			foreach ($this->loginColumns as $column) {
				if (isset($mappedFields[$column])) {
					$loginValue = $mappedFields[$column];
					break;
				}
			}
			$passwordValue = $mappedFields[$this->passwordColumn];
			$rememberMe = isset($mappedFields[$this->tableConfig['remember_me_token_column']]) && $mappedFields[$this->tableConfig['remember_me_token_column']] === 'on';
			$user = $this->userModel->login($loginValue, $passwordValue, $rememberMe);
			if ($user) {
				$utils = new Utils();
				$utils->initializeSession();
				$this->startLoginSession($user);
				if ($rememberMe) {
					$this->setRememberMeToken($user[Config::USER_TABLE_CONFIG['primary_key']]);
				}
				return ['check' => true, 'message' => 'Login successfully id: ' . $user[Config::USER_TABLE_CONFIG['primary_key']]];
				} else {
				return ['check' => false, 'message' => 'Login error'];
				
			}
		}
		
		private function startLoginSession($user) {
			$_SESSION['user_id_'.Config::SECURITY_CONFIG['secret_salt']] = $user[Config::USER_TABLE_CONFIG['primary_key']];
			$_SESSION['loggedin_'.Config::SECURITY_CONFIG['secret_salt']] = true;
			$_SESSION['last_activity'] = time();
		}
		
		public function rememberMeController($cookieToken) {
			if (strlen($cookieToken) >= Config::SECURITY_CONFIG['rememberMe_token_length']) {
				$user = $this->userModel->validateToken($cookieToken);
				if ($user) {
					$this->startLoginSession($user);
				}
				} else {
				return false;
			}
		}
		
		private function setRememberMeToken($userId) {
			$token = bin2hex(random_bytes(Config::SECURITY_CONFIG['rememberMe_token_length']));
			setcookie(
            Config::SECURITY_CONFIG['remember_me_cookie_name'],
            $token,
            time() + Config::SECURITY_CONFIG['rememberMe_token_expiry_time'],
            '/',
            '',
            true,  // Secure
            true   // HttpOnly
			);
			$this->userModel->storeRememberMeToken($userId, $token);
		}
		
		public function logout() {
			if (isset($_SESSION['user_id_'.Config::SECURITY_CONFIG['secret_salt']])) {
				$this->userModel->logout($_SESSION['user_id_'.Config::SECURITY_CONFIG['secret_salt']]);
			}
			$_SESSION = [];
			if (ini_get("session.use_cookies")) {
				$params = session_get_cookie_params();
				setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
				);
			}
			session_destroy();
			setcookie(
            Config::SECURITY_CONFIG['remember_me_cookie_name'],
            '',
            time() - 42000,
            '/',
            '',
            true,  // Secure
            true   // HttpOnly
			);
			$utils = new Utils();
			$utils->initializeSession();
			return ['check' => true, 'message' => "User logged out successfully."];
		}
		
		public function checkRequired($fields) {
			// Check if at least one login column is present and password field is present
			$loginColumnPresent = false;
			foreach ($this->loginColumns as $column) {
				if (isset($fields[$column])) {
					$loginColumnPresent = true;
					break;
				}
			}
			return $loginColumnPresent && isset($fields[$this->passwordColumn]);
		}
		
		private function mapFieldsToColumns($fields) {
			$mappedFields = [];
			$originalFields = [];
			foreach ($fields as $inputName => $value) {
				if (isset(Config::FIELD_VALIDATION[$inputName]['column'])) {
					$columnName = Config::FIELD_VALIDATION[$inputName]['column'];
					$mappedFields[$columnName] = $value;
					$originalFields[$columnName] = $inputName;
					} else {
					// If no mapping is found, keep the original input name
					$mappedFields[$inputName] = $value;
					$originalFields[$inputName] = $inputName;
				}
			}
			return ['mappedFields' => $mappedFields, 'originalFields' => $originalFields];
		}
		public function sendPasswordReset($fields) {
			$this->validateCSRFToken();
			$email = $fields['email'];
			$user = $this->userModel->findUserByEmail($email);
			if ($user) {
				$resetToken = bin2hex(random_bytes(Config::SECURITY_CONFIG['resetPassword_token_length'])); // Generate token
				if (isset(Config::EMAIL_CONFIG['smtp_password']) && Config::EMAIL_CONFIG['smtp_password'] !== null) {
					$mailer = new Mailer();
					$mailer->sendPasswordResetEmail($email, $resetToken);
				}
				$this->userModel->storeResetToken($user[Config::USER_TABLE_CONFIG['primary_key']],$resetToken);
				return ['check' => true, 'message' => 'Password reset email sent.'];
				
			}
			return ['check' => false, 'message' => 'User not found.'];
		}
		
		
		public function verifyResetToken($token) {
			return $this->userModel->verifyResetToken($token);
		}
		
		
		public function resetPassword($fields) {
			$this->validateCSRFToken();
			$userId=$this->userModel->findUserByToken($fields['token'],'reset_password');
			if($userId){
				$this->userModel->resetPassword($userId, $fields['password']);
				return ['check' => true, 'message' => 'Your password has been changed successfully.'];
				
			}
			else{
				return ['check' => false, 'message' => 'Token not found or has expired.'];
			}
		}
		
		
		public function getUser($userId) {
			$verifiedColumn = Config::USER_TABLE_CONFIG['verified'] ?? null;
			return $this->userModel->getUser($userId, $verifiedColumn);
		}
		
		public function getUserByToken($token) {
			return $this->userModel->findUserByToken($token, 'activate_account');
		}
		
		public function activateUser($token) {
			$userId = $this->getUserByToken($token);
			if ($userId){
				$activationStatus = $this->userModel->activateUser($userId);
				if ($activationStatus['check']) {
					return ['check' => true, 'message' => $activationStatus['message']];
					} else {
					return ['check' => false, 'message' => $activationStatus['message']];
				}
				}else{
				return ['check' => false, 'message' => 'Invalid token or user already activated.'];
			}
			
		}
		private function validateCSRFToken() {
			$csrfResult = $this->csrf->validateToken();
			if (!$csrfResult['check']) {
				die($csrfResult['message']);
			}
		}
	}
