<?php
namespace App\Utils;

use App\Config\Config;

class Mailer {
    private $smtpHost;
    private $smtpPort;
    private $smtpUsername;
    private $smtpPassword;
    private $smtpSecure;

    public function __construct() {
        $emailConfig = Config::EMAIL_CONFIG;
        $this->smtpHost = $emailConfig['smtp_host'];
        $this->smtpPort = $emailConfig['smtp_port'];
        $this->smtpUsername = $emailConfig['smtp_username'];
        $this->smtpPassword = $emailConfig['smtp_password'];
        $this->smtpSecure = $emailConfig['smtp_secure'];
    }

    private function sendMail($to, $subject, $message, $headers) {
        if ($this->smtpHost && $this->smtpUsername && $this->smtpPassword) {
            return $this->sendMailWithSMTP($to, $subject, $message, $headers);
        } else {
            return mail($to, $subject, $message, $headers);
        }
    }

    private function sendMailWithSMTP($to, $subject, $message, $headers) {
        $socketContext = stream_context_create([
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            ]
        ]);

        $fp = stream_socket_client("{$this->smtpSecure}://{$this->smtpHost}:{$this->smtpPort}", $errno, $errstr, 30, STREAM_CLIENT_CONNECT, $socketContext);
        if (!$fp) {
            return false;
        }

        $response = $this->getSMTPResponse($fp);
        if (substr($response, 0, 3) != '220') {
            return false;
        }

        $commands = [
            "EHLO {$_SERVER['SERVER_NAME']}",
            "AUTH LOGIN",
            base64_encode($this->smtpUsername),
            base64_encode($this->smtpPassword),
            "MAIL FROM: <" . Config::EMAIL_CONFIG['from'] . ">",
            "RCPT TO: <$to>",
            "DATA",
            "Subject: $subject\r\n$headers\r\n\r\n$message\r\n.",
            "QUIT"
        ];

        foreach ($commands as $command) {
            fwrite($fp, "$command\r\n");
            $response = $this->getSMTPResponse($fp);
            if (substr($response, 0, 3) == '535' || substr($response, 0, 3) == '550') {
                fclose($fp);
                return false;
            }
        }

        fclose($fp);
        return true;
    }

    private function getSMTPResponse($fp) {
        $response = '';
        while ($str = fgets($fp, 515)) {
            $response .= $str;
            if (substr($str, 3, 1) == ' ') {
                break;
            }
        }
        return $response;
    }
    
    private function getFormattedMessage($message, $replacements) {
        foreach ($replacements as $key => $value) {
            $message = str_replace("{{" . $key . "}}", $value, $message);
        }
        return $message;
    }

    public function sendActivationEmail($to, $token) {
        $subject = Config::EMAIL_CONFIG['subject_verification'];
        $message = $this->getFormattedMessage(Config::EMAIL_CONFIG['message_verification'], ['token' => $token]);
        $headers = "From: " . Config::EMAIL_CONFIG['from_name'] . " <" . Config::EMAIL_CONFIG['from'] . ">\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        return $this->sendMail($to, $subject, $message, $headers);
    }

    public function sendPasswordResetEmail($to, $token) {
        $subject = Config::EMAIL_CONFIG['subject_password_reset'];
        $message = $this->getFormattedMessage(Config::EMAIL_CONFIG['message_password_reset'], ['token' => $token]);
        $headers = "From: " . Config::EMAIL_CONFIG['from_name'] . " <" . Config::EMAIL_CONFIG['from'] . ">\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        return $this->sendMail($to, $subject, $message, $headers);
    }
}
