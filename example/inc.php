<?php
session_start();
require_once __DIR__ . '/../vendor/autoload.php';

use App\Utils\Utils;
use App\Config\Config;
use App\Controllers\AuthController;
$utils = new Utils();
$utils->initializeSession();

if (!isset($_SESSION['loggedin_'.Config::SECURITY_CONFIG['secret_salt']]) || $_SESSION['loggedin_'.Config::SECURITY_CONFIG['secret_salt']] !== true) {
    header('Location: login.php');
    exit;
}
$user_id = $_SESSION['user_id_'.Config::SECURITY_CONFIG['secret_salt']];

$authController= new AuthController();
$user = $authController->getUser($user_id);

if (isset($user['check']) && !$user['check']){
	$activated = false;
}
function prettyPrint($obj) {
    echo '
        <table style="width: 100%; border-collapse: collapse; font-family: Arial, sans-serif;">
            <thead>
                <tr>
                    <th style="text-align: left; padding: 10px; border-bottom: 2px solid #ddd;">Key</th>
                    <th style="text-align: left; padding: 10px; border-bottom: 2px solid #ddd;">Value</th>
                </tr>
            </thead>
            <tbody>';
    
    foreach ($obj as $key => $value) {
        $safeKey = htmlspecialchars($key);
        $safeValue = is_null($value) ? 'NULL' : htmlspecialchars($value);
        echo '
                <tr>
                    <td style="padding: 10px; border-bottom: 1px solid #ddd; word-break: break-all;">' . $safeKey . '</td>
                    <td style="padding: 10px; border-bottom: 1px solid #ddd; word-break: break-all;">' . $safeValue . '</td>
                </tr>';
    }
    
    echo '
            </tbody>
        </table>
    ';
}
?>