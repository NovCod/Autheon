<?php
namespace App\Models;

use PDO;
use PDOException;
use App\Config\Config;

class User {
    private $db;
    private $user;
    private $tableConfig;
    private $emailColumn;
    private $loginColumns;
    private $securityConfig;
    private $passwordColumn;
    private $originalFields;

    public function __construct() {
        $this->initDatabase();
        $this->tableConfig = Config::USER_TABLE_CONFIG;
        $loginPassword = $this->getLoginAndPasswordColumns();
        if ($loginPassword['check']) {
            $this->loginColumns = $loginPassword['login_columns'];
            $this->passwordColumn = $loginPassword['password_column'];
            $this->emailColumn = $loginPassword['email_column'];
			
        } else {
            return ['check' => false, 'message' => $loginPassword['message']];
        }
        $this->securityConfig = Config::SECURITY_CONFIG;
    }

    private function initDatabase() {
        $dbConfig = Config::DB_CONFIG;
        $dsn = "mysql:host={$dbConfig['db_host']};dbname={$dbConfig['db_name']}";
        try {
            $this->db = new PDO($dsn, $dbConfig['db_username'], $dbConfig['db_password']);
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die('Database connection failed: ' . $e->getMessage());
        }
    }

    public function register($hashedPassword, array $additionalFields, $originalFields) {
        $this->originalFields = $originalFields;
        $fieldsToStore = $this->prepareFieldsForInsertion($additionalFields);
        if (isset($fieldsToStore['check']) && (!$fieldsToStore['check'])) {
            return ['check' => false, 'message' => $fieldsToStore['message']];
        }
        if (!$this->checkRequiredFields($fieldsToStore, $errorMessage)) {
            return ['check' => false, 'message' => $errorMessage];
        }

        $columns = [$this->passwordColumn => $hashedPassword] + $fieldsToStore;
		
		// Check and add activation token and expiry if configured
		$activationTokenColumn = Config::USER_TABLE_CONFIG['activateUser_token_column'];
		$activationTokenExpiryColumn = Config::USER_TABLE_CONFIG['activateUser_token_expiry'];
		$verified = Config::USER_TABLE_CONFIG['verified'];
		$activationTokenExpiryTime = Config::SECURITY_CONFIG['activateUser_token_expiry_time'];
		$activationTokenLength = Config::SECURITY_CONFIG['activateUser_token_length'];

		if ($this->fieldExists($activationTokenColumn) && $this->fieldExists($activationTokenExpiryColumn)) {
			$activationToken = bin2hex(random_bytes($activationTokenLength));
			$activationTokenExpiry = date('Y-m-d H:i:s', time()+$activationTokenExpiryTime); 
			$columns[$activationTokenColumn] = $activationToken;
			$columns[$activationTokenExpiryColumn] = $activationTokenExpiry;
			$columns[$verified] = 0;
		}
	
        $sql = $this->buildInsertQuery($this->tableConfig['users_table'], $columns);
        $stmt = $this->db->prepare($sql);

        foreach ($columns as $field => $value) {
            $stmt->bindValue(":$field", $value);
        }

        $stmt->execute();
        $response = ['check' => true, 'message' => 'Registration successful'];
			if (!empty($activationToken)) {
				$response['token'] = $activationToken;
			}
		return $response;
    }

    public function login($loginValue, $password) {
        $sqlParts = [];
        foreach ($this->loginColumns as $column) {
            $sqlParts[] = "{$column} = :loginValue";
        }
        $sql = "SELECT * FROM {$this->tableConfig['users_table']} WHERE (" . implode(' OR ', $sqlParts) . ")";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(":loginValue", $loginValue);
        $stmt->execute();

        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user) {
            $hashedPassword = $user[$this->passwordColumn];
            if (password_verify($password, $hashedPassword)) {
				$this->user = $user;
                return $user;
            }
        }
        return false;
    }
	
    public function fieldExists($fieldName) {
        $columns = $this->getTableColumns();
        foreach ($columns as $column) {
            if ($column['Field'] === $fieldName) {
                return true;
            }
        }
        return false;
    }

    public function logout($userId) {
                if (($this->fieldExists($this->tableConfig['remember_me_token_column']))&&($this->fieldExists($this->tableConfig['remember_token_expiry']))) {

            $sql = "UPDATE {$this->tableConfig['users_table']} SET {$this->tableConfig['remember_me_token_column']} = NULL, {$this->tableConfig['remember_token_expiry']} = NULL WHERE id = :userId";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':userId', $userId);
            $stmt->execute();
        }
    }

    private function isDuplicate($uniqueColumn, $fieldValue) {
        $sql = "SELECT COUNT(*) FROM {$this->tableConfig['users_table']} WHERE $uniqueColumn = :fieldValue";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':fieldValue', $fieldValue);
        $stmt->execute();

        return $stmt->fetchColumn() > 0;
    }

    public function storeRememberMeToken($userId, $token) {
        if (($this->fieldExists($this->tableConfig['remember_me_token_column']))&&($this->fieldExists($this->tableConfig['remember_token_expiry']))) {
            $expiry = date('Y-m-d H:i:s', time() + $this->securityConfig['rememberMe_token_expiry_time']);
            $sql = "UPDATE {$this->tableConfig['users_table']} SET {$this->tableConfig['remember_me_token_column']} = :token, {$this->tableConfig['remember_token_expiry']} = :expiry WHERE id = :userId";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':token', $token);
            $stmt->bindValue(':expiry', $expiry);
            $stmt->bindValue(':userId', $userId);
            $stmt->execute();
        }
    }

    public function validateToken($token) {
                if (($this->fieldExists($this->tableConfig['remember_me_token_column']))&&($this->fieldExists($this->tableConfig['remember_token_expiry']))) {

            $sql = "SELECT * FROM {$this->tableConfig['users_table']} WHERE {$this->tableConfig['remember_me_token_column']} = :remember_token AND {$this->tableConfig['remember_token_expiry']} > NOW()";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':remember_token', $token);
            $stmt->execute();

            return $stmt->fetch(PDO::FETCH_ASSOC) ?: false;
        }
        return false;
    }

    private function getTableColumns() {
        $stmt = $this->db->query("SHOW COLUMNS FROM {$this->tableConfig['users_table']}");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function prepareFieldsForInsertion(array $additionalFields) {
        $fieldsToStore = [];
        $columnsInfo = $this->getTableColumns();
        // Check for duplicate values in login columns
        foreach ($this->loginColumns as $loginColumn) {
            if (array_key_exists($loginColumn, $additionalFields)) {
                $value = $additionalFields[$loginColumn];
                if ($this->isDuplicate($loginColumn, $value)) {
                    return ['check' => false, 'message' => 'Error: ' . $this->originalFields[$loginColumn] . ' already exists.'];
                }
            }
        }
        foreach ($columnsInfo as $column) {
            if ($column['Extra'] === 'auto_increment') {
                unset($additionalFields[$column['Field']]);
            } elseif (array_key_exists($column['Field'], $additionalFields)) {
                if ($column['Key'] === 'UNI' && $this->isDuplicate($column['Field'], $additionalFields[$column['Field']])) {
                    return ['check' => false, 'message' => 'Error: ' . $this->originalFields[$column['Field']] . ' already exists.'];
                }
                $fieldsToStore[$column['Field']] = $additionalFields[$column['Field']];
            }
        }
        return $fieldsToStore;
    }

    private function buildInsertQuery($table, array $columns) {
        $columnsList = implode(', ', array_keys($columns));
        $placeholders = implode(', ', array_map(fn($col) => ":$col", array_keys($columns)));
        return "INSERT INTO $table ($columnsList) VALUES ($placeholders)";
    }

    private function checkRequiredFields(array $additionalFields, &$errorMessage) {
        $columnsInfo = $this->getTableColumns();

        $requiredFields = [];
        foreach ($columnsInfo as $column) {
            if (($column['Extra'] === 'auto_increment') || ($column['Field'] === $this->passwordColumn)) continue;
            if ($column['Null'] === 'NO' && !array_key_exists($column['Field'], $additionalFields) && $column['Default'] === null) {
                $requiredFields[] = $column['Field'];
            }
        }

        if ($requiredFields) {
            $errorMessage = count($requiredFields) > 1
                ? "Error: The following fields are required: " . implode(', ', $requiredFields)
                : "Error: The field '{$requiredFields[0]}' is required.";
            return false;
        }
        return true;
    }

    public function getLoginAndPasswordColumns() {
        $loginColumns = [];
        $passwordColumn = null;
        $emailColumn = null;
        $message = '';

        foreach (Config::FIELD_VALIDATION as $field => $validation) {
            if (isset($validation['login_field']) && $validation['login_field'] === true) {
                $loginColumns[] = $validation['column'];
            }
            if (isset($validation['password_field']) && $validation['password_field'] === true) {
                $passwordColumn = $validation['column'];
            }
			if (isset($validation['type']) && $validation['type'] === 'Email') {
                $emailColumn = $validation['column'];
            }
        }

        if (empty($loginColumns)) {
            $message = 'No login columns are defined in the FIELD_VALIDATION config.';
            return ['check' => false, 'message' => $message];
        }

        if ($passwordColumn === null) {
            $message = 'No password column is defined in the FIELD_VALIDATION config.';
            return ['check' => false, 'message' => $message];
        }

        return ['check' => true, 'login_columns' => $loginColumns, 'password_column' => $passwordColumn, 'email_column' => $emailColumn];
    }

    

    public function verifyResetToken($token) {
        if ($this->fieldExists($this->tableConfig['resetPassword_token_column']) && $this->fieldExists($this->tableConfig['resetPassword_token_expiry'])) {
            $sql = "SELECT * FROM {$this->tableConfig['users_table']} WHERE {$this->tableConfig['resetPassword_token_column']} = :token AND {$this->tableConfig['resetPassword_token_expiry']} > NOW()";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':token', $token);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: false;
        }
        return false;
    }





    public function resetPassword($userId, $newPassword) {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $sql = "UPDATE {$this->tableConfig['users_table']} SET {$this->passwordColumn} = :hashedPassword, {$this->tableConfig['resetPassword_token_column']} = NULL, {$this->tableConfig['resetPassword_token_expiry']} = NULL WHERE id = :userId";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':hashedPassword', $hashedPassword);
        $stmt->bindValue(':userId', $userId);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }
	public function findUserByEmail($email) { 
    $sql = "SELECT * FROM {$this->tableConfig['users_table']} WHERE {$this->emailColumn} = :email";
    $stmt = $this->db->prepare($sql);
    $stmt->bindValue(':email', $email);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
} 
public function storeResetToken($userId, $token) {
	$expiry  =  date('Y-m-d H:i:s', time() +Config::SECURITY_CONFIG['resetPassword_token_expiry_time']); 
    $sql = "UPDATE {$this->tableConfig['users_table']} SET {$this->tableConfig['resetPassword_token_column']} = :token, {$this->tableConfig['resetPassword_token_expiry']} = :expiry WHERE id = :userId";
    $stmt = $this->db->prepare($sql);
    $stmt->bindValue(':token', $token);
    $stmt->bindValue(':expiry', $expiry);
    $stmt->bindValue(':userId', $userId);
    return $stmt->execute();
}

public function getUser($userId, $verifiedColumn = null) {
	
			$stmt = $this->db->prepare("SELECT * FROM {$this->tableConfig['users_table']}  WHERE {$this->tableConfig['primary_key']} = :userId");
            $stmt->bindParam(':userId', $userId);
            $stmt->execute();
            $this->user = $stmt->fetch(PDO::FETCH_ASSOC );
		if ($this->user){
        if ($verifiedColumn && $this->fieldExists($verifiedColumn)) {
            if ($this->user[$verifiedColumn]) {
                    return $this->user;
                
            } else {
                return ['check' => false, 'message' => 'User not activated.'];
            }
        }
          else {
                return $this->user;
            }
        }else {
		return ['check' => false, 'message' => 'User not found.'];
		}
    }
	
public function findUserByToken($token, $action) {
    $tokenColumn = '';
    $expiryColumn = '';
    
    switch ($action) {
        case 'reset_password':
            $tokenColumn = Config::USER_TABLE_CONFIG['resetPassword_token_column'];
            $expiryColumn = Config::USER_TABLE_CONFIG['resetPassword_token_expiry'];
            break;
        case 'activate_account':
            $tokenColumn = Config::USER_TABLE_CONFIG['activateUser_token_column'];
            $expiryColumn = Config::USER_TABLE_CONFIG['activateUser_token_expiry'];
            break;
        default:
			return ['check' => false, 'message' => 'Invalid action: $action'];
    }
    
    $sql = "SELECT {$this->tableConfig['primary_key']} 
            FROM {$this->tableConfig['users_table']} 
            WHERE $tokenColumn = :token 
            AND ($expiryColumn IS NULL OR $expiryColumn > NOW())";
    
    $stmt = $this->db->prepare($sql);
    $stmt->bindValue(':token', $token);
    $stmt->execute();
    
    return $stmt->fetch(PDO::FETCH_COLUMN);
}


public function activateUser($userId) {
    $verifiedColumn = Config::USER_TABLE_CONFIG['verified'];

    if ($this->fieldExists($verifiedColumn)) {
        $stmt = $this->db->prepare("UPDATE {$this->tableConfig['users_table']} SET " . $verifiedColumn . " = 1, {$this->tableConfig['activateUser_token_column']} = NULL,{$this->tableConfig['activateUser_token_expiry']} = NULL WHERE {$this->tableConfig['primary_key']} = :userId");
        $stmt->bindParam(':userId', $userId);
		$stmt->execute();
		return ['check' => true, 'message' => 'Your account is successfully activated.'];
    } else {
		return ['check' => false, 'message' => 'Field $verifiedColumn not found.'];
    }
}
}
