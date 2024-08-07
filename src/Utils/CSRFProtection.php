<?php
namespace App\Utils;

class CSRFProtection {
    private $sessionKey;
    private $tokenFieldName;
    private $tokenExpiry;
    private $tokenSize;
    private $tokens;

    public function __construct($sessionKey = 'csrf_session_key', $tokenFieldName = 'csrf_input_name', $tokenExpiry = 3600, $tokenSize = 16) {
        $this->sessionKey = $sessionKey;
        $this->tokenFieldName = $tokenFieldName;
        $this->tokenExpiry = $tokenExpiry;
        $this->tokenSize = $tokenSize;
        $this->tokens = [];
        $this->loadTokens();
    }

    private function generateToken() {
        return bin2hex(random_bytes($this->tokenSize));
    }

    public function createHiddenField() {
        $token = $this->generateToken();
        $formName = $this->getFormName();
        $this->tokens[$token] = [
            'formName' => $formName,
            'expiresAt' => time() + $this->tokenExpiry
        ];
        $this->saveTokens();
        return sprintf(
            '<input type="hidden" name="%s" value="%s">',
            htmlspecialchars($this->tokenFieldName),
            htmlspecialchars($token)
        );
    }

    public function validateToken() {
        $token = $_POST[$this->tokenFieldName] ?? $_GET[$this->tokenFieldName] ?? null;

        if ($token === null || !isset($this->tokens[$token])) {
            return ['check' => false, 'message' => 'Your session has expired. Please try again.'];
        }

        $tokenData = $this->tokens[$token];

        if ($tokenData['expiresAt'] > time()) {
            // Token is valid
            unset($this->tokens[$token]); // Invalidate token after use
            $this->saveTokens();
            return ['check' => true, 'message' => 'CSRF token verified successfully'];
        }

        return ['check' => false, 'message' => 'There was an issue with your request. Please try again.'];
    }

    private function getFormName() {
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? $_SERVER['PHP_SELF'];
        $formName = basename($scriptName, '.php'); // Extract the base name without extension
        if (empty($formName)) {
            $formName = 'default_form'; // Fallback name if extraction fails
        }
        return htmlspecialchars($formName);
    }

    private function loadTokens() {
        if (isset($_SESSION[$this->sessionKey])) {
            $this->tokens = unserialize($_SESSION[$this->sessionKey]);
            $this->clearExpiredTokens();
            $this->clearDuplicateFormNames(); // Ensure no duplicated form names
        }
    }

    private function saveTokens() {
        $_SESSION[$this->sessionKey] = serialize($this->tokens);
        $this->clearDuplicateFormNames(); // Ensure no duplicated form names after saving
    }

    private function clearExpiredTokens() {
        foreach ($this->tokens as $key => $storedToken) {
            if ($storedToken['expiresAt'] <= time()) {
                unset($this->tokens[$key]);
            }
        }
        $this->saveTokens();
    }

    private function clearDuplicateFormNames() {
        $formNames = [];
        foreach ($this->tokens as $key => $storedToken) {
            $formName = $storedToken['formName'];
            if (isset($formNames[$formName])) {
                // Remove the old token
                unset($this->tokens[$formNames[$formName]]);
            }
            $formNames[$formName] = $key; // Keep the new token
        }
    }
}
