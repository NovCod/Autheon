<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Controllers\AuthController;

session_start();
$authController = new AuthController();

$requestMethod = $_SERVER['REQUEST_METHOD'];
$requestData = $requestMethod === 'POST' ? $_POST : $_GET;

if ($requestMethod === 'POST') {
    
    $formName = $requestData['form_name'] ?? '';
    switch ($formName) {
        case 'loginForm':
            print_r($authController->login($requestData));
            break;
        case 'registerForm':
            print_r( $authController->register($requestData));
            break;
        case 'reset':
            print_r( $authController->sendPasswordReset($requestData));
            break;
        case 'resetPassword':
            print_r( $authController->resetPassword($requestData));
            break;
        default:
            print_r( $authController->logout());
            break;
    }
} else {
    print_r( $authController->logout());
}
?>
