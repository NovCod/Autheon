<?php
require_once __DIR__ . '/../vendor/autoload.php';
use App\Utils\Utils;
use App\Utils\CSRFProtection;

$utils = new Utils();
$utils->initializeSession();
$csrf = new CSRFProtection();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body class="login-page">
    <div class="container">
        <div class="left-panel">
            <h1>Welcome Back</h1>
            <p>Join us for an amazing experience</p>
        </div>
        <div class="right-panel">
            <div class="form">
                <h2>Login</h2>
                <form id="loginForm" method="post" action="./rooter.php">
                    <?php echo $csrf->createHiddenField(); ?>
                    <input type="hidden" name="form_name" value="loginForm">
                    <label for="mail">Email or Username:</label>
                    <input type="text" id="mail" name="mail" required>
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                    <div class="form-options">
                        <div class="remember-me">
                            <input type="checkbox" id="remember_me" name="remember_me">
                            <label for="remember-me">Remember Me</label>
                        </div>
                        <a href="forgot-password.php" class="forgot-password">Forgot Password?</a>
                    </div>
                    <button type="submit">Login</button>
                </form>
                <div class="footer">
                    <p>Don't have an account? <a href="register.php">Sign up</a></p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
