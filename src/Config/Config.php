<?php
	namespace App\Config;

class Config {
    const DB_CONFIG = [
        'db_host' => 'localhost',
        'db_username' => 'root',
        'db_password' => '',
        'db_name' => 'test_db'
    ];

    const USER_TABLE_CONFIG = [
        'users_table' => 'users',
        'primary_key' => 'id',
        'verified' => 'is_active',
		'activateUser_token_column' => 'activate_token',
        'activateUser_token_expiry' => 'activate_token_expiry',
        'resetPassword_token_column' => 'reset_token',
        'resetPassword_token_expiry' => 'reset_token_expiry',
        'remember_me_token_column' => 'remember_me',
        'remember_token_expiry' => 'remember_token_expiry'
    ];
	const EMAIL_CONFIG = [
        'from' => 'no-reply@youwebsite.com',
        'from_name' => 'Example',
        'subject_verification' => 'Email Verification',
        'message_verification' => "<h2>Hello,</h2> <h3>Please click the following link to activate your account:</h3> <p style=\"text-align:center\"><a href=\"https://youwebsite.com/activate.php?token={{token}}\" style=\"padding: 10px 20px; font-size: 16px; color: #ffffff; background-color: #54b96b; text-decoration: none; border-radius: 5px; text-align: center;\">Activate account</a>",
        'subject_password_reset' => 'Password Reset',
		'message_password_reset' => "<h2>Hello,</h2> <h3>You recently requested to reset your password for your account. Click the button bellow to reset it.</h3> <p style=\"text-align:center\"><a href=\"https://youwebsite.com/forgot-password.php?token={{token}}\" style=\"padding: 10px 20px; font-size: 16px; color: #ffffff; background-color: #54b96b; text-decoration: none; border-radius: 5px; text-align: center;\">Reset password</a>",
        'smtp_host' => 'smtp.youwebsite.com',
        'smtp_port' => 465,
        'smtp_username' => 'no-reply@youwebsite.com',
        'smtp_password' => '',
        'smtp_secure' => 'tls'
    ];

    const SECURITY_CONFIG = [
        'password_hash_algo' => PASSWORD_BCRYPT,
		'secret_salt' => 'SomeSecretSalt',
		'activateUser_token_expiry_time' => 1 * 24 * 60 * 60, // 1 day in seconds
		'resetPassword_token_expiry_time' => 3 * 60 * 60, // 3 hours in seconds
        'rememberMe_token_expiry_time' =>  30 * 24 * 60 * 60, // 30 days
		'activateUser_token_length' => 32, 
		'resetPassword_token_length' => 16, 
		'rememberMe_token_length' => 64,
		'csrf_input_name' => 'csrf_token',
		'csrf_session_key' => 'myapp_csrf_tokens',
        'max_login_attempts' => 5,
		'remember_me_cookie_name' => 'remember_me',
		'session_name' => 'user_session',
		'session_maxlifetime' => 900, //15 min 
		'session_cookie_httponly' => true,
        'session_cookie_secure' => true,
        'session_use_strict_mode' => true,
        'session_use_only_cookies' => true,
        'session_samesite' => 'Strict', //'Strict' or 'Lax' or 'None'
    ];
	
	const FIELD_VALIDATION = [
        'user' => [
            'column' => 'username',
            'type' => 'String',
            'required' => true,
            'max_length' => 50,
            'min_length' => 3,
            'regex' => '/^[a-zA-Z0-9_]+$/',
            'error_messages' => [
                'required' => 'Username is required.',
                'max_length' => 'Username cannot exceed 50 characters.',
                'min_length' => 'Username must be at least 3 characters.',
                'regex' => 'Username can only contain letters, numbers, and underscores.'
            ],
            'login_field' => true
        ],
        'mail' => [
            'column' => 'email',
            'type' => 'Email',
            'required' => false,
            'max_length' => 100,
            'error_messages' => [
                'required' => 'Email is required.',
                'max_length' => 'Email cannot exceed 100 characters.',
                'type' => 'Email format is invalid.'
            ],
            'login_field' => true
        ],
        'password' => [
            'column' => 'password',
            'type' => 'String',
            'required' => false,
            'min_length' => 8,
            'error_messages' => [
                'required' => 'Password is required.',
                'min_length' => 'Password must be at least 8 characters.'
            ],
			'password_field' => true
        ],
        'first-name' => [
            'column' => 'first_name',
            'type' => 'String',
            'required' => false,
            'min_length' => 4,
            'error_messages' => [
				'required' => 'Name is required.',
                'min_length' => 'Name cannot minimum than 4 characters.',
            ],
        ],
		'last-name' => [
            'column' => 'last_name',
            'type' => 'String',
            'required' => false,
            'error_messages' => [
                'required' => 'Phone is required.',
                'type' => 'Invalid date format.'
            ],
        ],
		'phone' => [
            'column' => 'phone',
            'type' => 'String',
            'required' => false,
            'error_messages' => [
                'required' => 'Phone is required.',
                'type' => 'Invalid date format.'
            ],
        ],
        // Additional fields and their validation rules can be added here
    ];
}
