<div align="center">
	
   # PHP-Autheon 
   
</div>

<p align="center">A versatile and secure PHP authentication/registration system with effortless integration and dependency-free.

</p>


<div align="center">
	
![GitHub](https://img.shields.io/github/license/NovoCod/PHP-Autheon)
![GitHub pre-release](https://img.shields.io/github/release/NovoCod/PHP-Autheon/all)
![GitHub repo size](https://img.shields.io/github/repo-size/NovoCod/PHP-Autheon)
![GitHub code size in bytes](https://img.shields.io/github/languages/code-size/NovoCod/PHP-Autheon)
</div>
<br> <br>

## Table of Contents

- [Why Choose PHP-Autheon?](#why-choose-php-autheon)
- [Prerequisites](#prerequisites)
- [Installation](#installation)
  - [Direct Download](#direct-download)
  - [Using Composer](#using-composer)
- [Configuration](#configuration)
  - [Database Configuration](#database-configuration)
  - [User Table Configuration](#user-table-configuration)
  - [Email Configuration](#email-configuration)
  - [Security Configuration](#security-configuration)
- [Form Field Mapping](#form-field-mapping)
- [Field Validation](#field-validation)
- [Integration](#integration)
- [Folder Structure](#folder-structure)
- [PSR-4 and MVC Implementation](#psr-4-and-mvc-implementation)
- [Troubleshooting](#troubleshooting)
- [Note](#note)
- [Demo and Example](#demo-and-example)
- [Contributing](#contributing)
- [Support Us](#support-us)
  - [Making a Donation](#making-a-donation)
  - [Give Us a Star!](#give-us-a-star)
- [License](#license)
- [Additional Documentation and Support](#additional-documentation-and-support)


## Why Choose PHP-Autheon?

- **Ease of Use**: PHP-Autheon requires minimal setup through a single `config.php` file, in just about 10 minutes, you can integrate a complete authentication features into your existing or new PHP project.
- **Flexible and Customizable**: Customize your registration system to fit specific needs with adaptable field validation (such as strings, integers, floats, booleans, emails, file uploads, and custom error messages), database mapping, email configurations, and more. This flexibility allows you to tailor your system behavior to various use cases without extensive coding.
- **Zero Dependencies**: No additional libraries or frameworks are needed, simplifying the integration process.
- **Secure**: Built with modern security practices, including password hashing, CSRF protection, secure session management, and SQL binding to prevent SQL injection attacks.

## Prerequisites

- PHP 8.0 and above
- MySQL

## Installation

### Direct Download

1. Go to the [GitHub repository](https://github.com/NovoCod/PHP-Autheon) and click on the **Code** button.
2. Select **Download ZIP**.
3. Once downloaded, unzip the folder.
4. Move the unzipped folder to your `htdocs` directory if using XAMPP or to the `www` directory if using WAMP.

### Using Composer

1. Download and install Composer from [getcomposer.org](https://getcomposer.org/).
2. Open your terminal or command prompt.
3. Navigate to your desired directory within the `htdocs` directory for XAMPP or the `www` directory for WAMP.
4. Run the following command:

```bash
   
composer require novocod/php-autheon

```
   
This will download and install PHP-Autheon in your specified directory.

## Configuration

PHP-Autheon is configured through the `config.php` file. Here’s a breakdown of each configuration option:

### Database Configuration

| Key              | Description                                | Example                           |
|------------------|--------------------------------------------|-----------------------------------|
| `db_host`        | Database host address                      | `localhost`                       |
| `db_username`    | Database username                          | `root`                            |
| `db_password`    | Database password                          | `password`                        |
| `db_name`        | Database name                              | `my_database`                     |

### User Table Configuration

| Key                          | Description                                      | Example                 |
|------------------------------|--------------------------------------------------|-------------------------|
| `users_table`                | Name of the user table in the database           | users                |
| `primary_key`                | Name of the primary key column                   | id                    |
| `verified`                   | Column indicating if the user is verified [Optional]        | is_active             |
| `activateUser_token_column`  | Column for activation token [Optional]                     | activate_token        |
| `activateUser_token_expiry`  | Column for activation token expiry [Optional]                    | activate_token_expiry        |
| `resetPassword_token_column` | Column for password reset token [Optional]                 | reset_token           |
| `resetPassword_token_expiry`  | Column for password reset token expiry [Optional]                   | reset_token_expiry        |
| `remember_me_token_column`   | Column for remember me token [Optional]                    | remember_me           |
| `remember_token_expiry`  | Column for remember me token expiry [Optional]                    | remember_token_expiry        |

### Email Configuration

| Key                          | Description                                      | Example                                |
|------------------------------|--------------------------------------------------|----------------------------------------|
| `from`                       | Sender email address                            | no-reply@example.com                 |
| `from_name`                  | Sender name                                     | Example                              |
| `subject_verification`       | Subject of the verification email               | Email Verification                 |
| `message_verification`       | HTML message for email verification             | `<a href="https://example.com/activate?token={{token}}">Activate account</a>` |
| `smtp_host`                  | SMTP server host                                | smtp.example.com                     |
| `smtp_port`                  | SMTP server port                                | 465                                  |
| `smtp_username`              | SMTP username                                   | no-reply@example.com                 |
| `smtp_password`              | SMTP password                                   | emailpassword                 |

**Note:** If the `smtp_password` in the configuration is left empty (`''`), PHP-Autheon will not send activation emails or handle the forgot password process. Ensure that all email configuration settings are correctly set to enable these functionalities.

### Security Configuration

| Key                          | Description                                  | Example           |
|------------------------------|----------------------------------------------|-------------------|
| `password_hash_algo`        | Algorithm for hashing passwords              | PASSWORD_BCRYPT |
| `secret_salt`               | Secret salt for additional security          | SomeSecretSalt |
| `activateUser_token_expiry_time` | Token expiry time for user activation     | 86400 (1 day)   |
| `session_cookie_secure`     | Secure session cookies                      | true            |


### Form Field Mapping

PHP-Autheon allows dynamic mapping of form fields to database columns. This not only ensures correct data storage but also simplifies integration and enhances security by hiding real column names, providing an additional security layer against SQL injection attacks.
**Example : **
```
const FIELD_VALIDATION =
[
	'first_name' =>
	[
		'column' => 'c7vU_fname',
	],
	'last_name' =>
	[
		'column' => 'c7vU_lname',
	],
]
```
Here, the **first_name** *form field* is mapped to the **c7vU_fname** *column in the database*, and the **last_name** *form field* is mapped to the **c7vU_lname** *column*. 
### Field Validation 
Field validation in PHP-Autheon is designed to ensure robust data integrity and security. It provides comprehensive server-side validation to enforce rules and constraints on user inputs, helping to prevent invalid or malicious data from compromising your application. The system supports various validation options, including data types, required fields, and constraints such as maximum and minimum lengths or values.

| **Option**           | **Description**                                                                                               | **Examples**                                   |
|----------------------|---------------------------------------------------------------------------------------------------------------|-----------------------------------------------|
| **type**             | Specifies the data type of the field.                                                                         | `String`, `Integer`, `Float`, `Boolean`, `Array`, `Object`, `Email`, `URL`, `Domain`, `IP`, `MAC`, `Date`, `Time`, `File` |
| **required**         | Indicates whether the field is mandatory. If `true`, the field must be filled out; if `false`, it is optional.| `true`, `false`                               |
| **max_length**       | Sets the maximum number of characters allowed in the field.                            | `50` (e.g., username length)                  |
| **min_length**       | Sets the minimum number of characters required for the field.                          | `3` (e.g., username length)                   |
| **regex**            | A regular expression to validate the format of the input.                                                    | `/^[a-zA-Z0-9_]+$/` (alphanumeric only)      |
| **login_field**      | Specifies if the field is used for login purposes,. If `true`, this field is considered for user login. For example if you need to use the email and the username and the phone number to login.       | `true`, `false`                               |
| **password_field**   | Specifies if the field is used for password input. If `true`, this field is treated as a password.           | `true`, `false`                               |
| **min_value**        | Specifies the minimum allowable value for numeric fields.                                                     | `0`, `18` (for age)                           |
| **max_value**        | Specifies the maximum allowable value for numeric fields.                                                     | `4`, `10` (for number of children)                     |
| **inclusion**        | Specifies a set of values that the field must be included in.                                                  | `['option1', 'option2']` (for dropdown)       |
| **exclusion**        | Specifies a set of values that the field must not be included in.                                              | `['banned1', 'banned2']`                      |
| **File**             | For file upload fields, specifies additional file validation rules.                                            | `allowed_extensions`, `max_size`             |
| **allowed_extensions** | Specifies the allowed file extensions for file uploads.                                                       | `['jpg', 'png', 'pdf']`                      |
| **max_size**         | Specifies the maximum file size allowed for file uploads.                                                     | `2MB`, `5MB`                                 |
| **error_messages**   | Custom messages displayed when validation fails. You can define specific error messages for each validation rule, such as required, max_value and inclusion, or format constraints.   | `'max_length' => 'Email cannot exceed 100 characters.'`    |


## Integration

Integrating PHP-Autheon into your PHP project is so easy. You just have to utilize the provided functions by passing `$requestData`, which encompasses all the values submitted from your forms with $_POST and PHP-Autheon will automatically manage all the rest : validation of  data, including interactions with the database. This streamlines the implementation of authentication and user management, allowing you to focus on other aspects of your application while PHP-Autheon handles the complexities of user-related operations.

1. **Include the PHP-Autheon library and instantiate the AuthController**:

```
use App\Controllers\AuthController;
$authController = new AuthController();
```

2. **Call the appropriate function based on the form submission**:



| **Function**                | **Description**                        |
|-----------------------------|----------------------------------------|
| `login($requestData)`       | Handles user login.                    |
| `register($requestData)`    | Handles user registration.             |
| `sendPasswordReset($requestData)` | Sends a password reset email.     |
| `resetPassword($requestData)` | Resets the user's password.           |
| `verifyResetToken($token)` | Verifies the reset token for password recovery. |
| `activateUser($token)` | Activates a user account using the activation token. |
| `logout()`                  | Logs out the user.                     |

## Folder Structure

```
src
├── Config
│   └── Config.php
├── Controllers
│   └── AuthController.php
├── Models
│   └── User.php
└── Utils
    ├── CSRFProtection.php
    ├── Mailer.php
    ├── Utils.php
    └── Validator.php
```


## PSR-4 and MVC Implementation

PHP-Autheon uses PSR-4 for autoloading classes and follows the MVC (Model-View-Controller) design pattern, providing a clean and maintainable code structure.

## Troubleshooting

-   Ensure that database columns are correctly set up with primary keys, unique constraints, NULL/NOT NULL configurations, and default values.
-   Verify field mappings and validation configurations.
-   For secondary fields (fields that you don’t want to configure), ensure that form field names match the exact column names for successful data storage.

## Note

**Please note that this is an initial release and not yet a stable version**. We encourage contributions and invite security experts to test and review the system. As of now, the following functionalities are operational but they are not fully tested:


- **User Registration**: Easy registration process for new users.
- **User Login**: Secure authentication for registered users.
- **Email Activation**: Account activation through email verification.
- **Remember Me**: Persistent login option for returning users.
- **User Logout**: Proper session termination for logged-in users.
- **Password Recovery**: Secure password reset via email.
- **Form Field Mapping**: Dynamic mapping of form fields to database columns.
- **Form Validation**: Robust validation for form inputs to ensure data integrity.
- **Session Management**: Secure handling and management of user sessions.
- **CSRF Protection**: Implementation of CSRF tokens to prevent cross-site request forgery.

## Demo and Example

To see PHP-Autheon in action and explore its features, follow these steps:

1. **Download the Repository**
   - Clone or download the last repository from the [PHP-Autheon GitHub page](https://github.com/NovoCod/PHP-Autheon/tags).

2. **Set Up the Database**
   - Navigate to the `example/DB` folder within the downloaded repository.
   - Import the provided `.sql` file into your MySQL database using a tool like phpMyAdmin or the MySQL command line.
   
     ```bash
     mysql -u yourusername -p yourdatabase < example/DB/test_db.sql
     ```

3. **Configure PHP-Autheon**
   - Update the `config.php` file in the `src/Config` directory to match your database settings and other configurations.

4. **Run the Demo**
   - Navigate to the `example` folder.
   - Open the example files in a web server environment (e.g., using XAMPP, WAMP, or a similar setup).

5. **Explore the Screenshots**
- The following screenshots illustrate the key features of PHP-Autheon:

   ![Login Screen](https://novocodes.com/github/autheon/screenshots/login.png)
   _Login Screen Example_
   
   **Login with Example Credentials**
   - Use the following credentials examples to log in:
     - **Username:** `admin123`
     - **Password:** `admin123`

   ![Registration Screen](https://novocodes.com/github/autheon/screenshots/register.png)
   _Registration Screen Example_

   ![Password Reset](https://novocodes.com/github/autheon/screenshots/forgot.png)
   _Password Reset Screen Example_
   
   ![Password Reset New Password](https://novocodes.com/github/autheon/screenshots/forgot_reset.png)
   _Password Reset New Password Screen Example_
   
![Dashboard](https://novocodes.com/github/autheon/screenshots/dashboard.png)
   _User Dashboard Example_

**Note:** The example does not include routing logic to keep the code simple and focused on demonstrating PHP-Autheon's core functionality. For a complete implementation, you will need to integrate routing according to your project's structure.

For further details or if you encounter any issues, please refer to the documentation or contact support.

Enjoy exploring PHP-Autheon!

## Contributing

Contributions are welcome! Please follow these guidelines:

-   Fork the repository and create a feature branch.
-   Make your changes and submit a pull request.
-   Ensure your code adheres to PSR standards and is thoroughly tested.

## Support Us

If you find PHP-Autheon useful and would like to support its development, consider :

### Making a donation

Your contributions help us maintain and improve the project. Donations can be made via:

- **[PayPal](https://www.paypal.com/paypalme/khalilghenimi)**
- **[Patreon](https://www.patreon.com/novocodes)**

### Give Us a Star!

If you find PHP-Autheon helpful, please give us a ⭐ on GitHub! Your support helps us grow and improve.
<br>

Thank you for your support!

## License

This project is licensed under the MIT License. See the [LICENSE](LICENSE) file for details.

## Additional Documentation and Support

For further documentation or support, please refer to the [GitHub Issues](https://github.com/NovoCod/PHP-Autheon/issues) page or contact us directly.
