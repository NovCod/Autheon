<?php
	require_once __DIR__ . '/../vendor/autoload.php';
	use App\Controllers\AuthController;
	
	// Initialize AuthController
	$authController = new AuthController();
	
	// Check if the token is provided in the URL
	if (isset($_GET['token'])) {
		$token = $_GET['token'];
        print_r( $authController->activateUser($token));
	}
	else {
		echo "No token provided.";
	}

?>

