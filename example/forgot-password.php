<?php
require_once __DIR__ . '/../vendor/autoload.php';
use App\Utils\Utils;
use App\Utils\CSRFProtection;
use App\Controllers\AuthController;

$utils = new Utils();
$utils->initializeSession();
$csrf = new CSRFProtection();

// Retrieve the token from the query parameters
$token = $_GET['token'] ?? null;
$isTokenValid = false;

// Check if the token is provided
if ($token !== null) {
    $authController = new AuthController();
    $isTokenValid = $authController->verifyResetToken($token);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
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
                <h2><?php echo $token && $isTokenValid ? 'Reset Password' : 'Forgot Password'; ?></h2>
                <?php if ($token && $isTokenValid): ?>
                    <form action="./rooter.php" method="post">
                        <?php echo $csrf->createHiddenField(); ?>
                        <input type="hidden" name="form_name" value="resetPassword">
						<input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                        <div class="form-group">
                            <label for="password">New Password</label>
                            <input type="password" id="password" name="password" required>
                        </div>
                        <div class="form-group">
                            <label for="password_confirm">Confirm Password</label>
                            <input type="password" id="password_confirm" name="password_confirm" required>
                        </div>
                        <button type="submit">Change Password</button>
                    </form>
                <?php else: ?>
                    <form action="./rooter.php" method="post">
						<?php echo $csrf->createHiddenField(); ?>
                        <input type="hidden" name="form_name" value="reset">
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" required>
                        </div>
                        <button type="submit">Reset Password</button>
                    </form>
                <?php endif; ?>
                <div class="footer">
                    <p>Remembered your password? <a href="login.php">Login</a></p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
