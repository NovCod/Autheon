<?php
	namespace App\Utils;
	
	use App\Config\Config;
	use App\Controllers\AuthController;

	
class Utils {
    private $sessionConfig;

    public function __construct() {
        $this->sessionConfig = Config::SECURITY_CONFIG;
    }
	public function initializeSession() {
		if (isset($_SESSION['last_activity'])) {
		$this->checkSessionTimeout();
		}
        if (session_status() !== PHP_SESSION_ACTIVE) {
		
            // Configuration for session cookie
            $params = session_get_cookie_params();

            // Set session cookie parameters
            session_set_cookie_params([
                'lifetime' => $this->sessionConfig['session_maxlifetime'],
                'path' => $params['path'],
                'domain' => $params['domain'],
                'secure' => $this->sessionConfig['session_cookie_secure'],
                'httponly' => $this->sessionConfig['session_cookie_httponly'],
                'samesite' => $this->sessionConfig['session_samesite']
            ]);

            // Set session settings
            ini_set('session.gc_maxlifetime', $this->sessionConfig['session_maxlifetime']);
			ini_set('session.cookie_lifetime', $this->sessionConfig['session_maxlifetime']);
            ini_set('session.use_strict_mode', $this->sessionConfig['session_use_strict_mode']);
            ini_set('session.use_only_cookies', $this->sessionConfig['session_use_only_cookies']);

            // Start the session
            session_start();
        }
		if (isset($_COOKIE[Config::SECURITY_CONFIG['remember_me_cookie_name']])) {

			$authController = new AuthController();
			$user_id = $authController->rememberMeController($_COOKIE[Config::SECURITY_CONFIG['remember_me_cookie_name']]);
			if ($user_id) {
				// Log the user in
				// $_SESSION['user_id'] = $user_id;
			}
		}
		
    }
	
	public function checkSessionTimeout() {
	
		
		$currentTime = time();
		$elapsedTime = $currentTime - $_SESSION['last_activity'];

		$sessionTimeout = $this->sessionConfig['session_maxlifetime'];
		if ($elapsedTime > $sessionTimeout) {
			// Perform logout or session destroy
			$authController = new AuthController();
			$authController->logout();
			$this->initializeSession();		
		}else{
		$_SESSION['last_activity'] = time();
		}
	
	}

	
}

