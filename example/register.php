<?php
session_start();
require_once __DIR__ . '/../vendor/autoload.php';
use App\Utils\CSRFProtection;

$csrf = new CSRFProtection();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Page</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body class="login-page">
    <div class="container">
        <div class="left-panel">
            <h1>Join Us</h1>
            <p>Create an account to get started</p>
        </div>
        <div class="right-panel">
            <div class="form">
                <h2>Register</h2>
                <form id="registerForm" method="post" action="./rooter.php">
                    <?php echo $csrf->createHiddenField(); ?>
                    <input type="hidden" name="form_name" value="registerForm">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="first-name">First Name</label>
                            <input type="text" id="first-name" name="first-name" required>
                        </div>
                        <div class="form-group">
                            <label for="last-name">Last Name</label>
                            <input type="text" id="last-name" name="last-name" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="mail" required>
                        </div>
                        <div class="form-group">
                            <label for="phone">Phone</label>
                            <input type="tel" id="phone" name="phone" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="username">Username</label>
                            <input type="text" id="username" name="user" required>
                        </div>
                        <div class="form-group">
                            <label for="password">Password</label>
                            <input type="password" id="password" name="password" required>
                        </div>
                    </div>
                    <button type="submit">Register</button>
                </form>
                <div class="footer">
                    <p>Already have an account? <a href="login.php">Login</a></p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
