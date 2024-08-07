<?php

namespace App\Utils;
use App\Config\Config;

class Validator {
    

    public static function validate($fieldName, $fieldValue, $rules) {
        $isValid = true;
        $errorMessages = [];

        // Check if required rule exists and apply it
        if (isset($rules['required']) && $rules['required'] && empty($fieldValue)) {
            $isValid = false;
            $errorMessages[$fieldName] = $rules['error_messages']['required'] ?? 'This field is required.';
            return ['is_valid' => $isValid, 'error_messages' => $errorMessages];
        }

        // Skip validation for empty optional fields
        if (!isset($rules['required']) || !$rules['required']) {
            if (empty($fieldValue)) {
                return ['is_valid' => true, 'error_messages' => []];
            }
        }

        // Validate type
        if (isset($rules['type'])) {
            switch ($rules['type']) {
                case 'String':
                    if (!is_string($fieldValue)) {
                        $isValid = false;
                        $errorMessages[$fieldName] = $rules['error_messages']['type'] ?? 'Invalid type. Expected String.';
                    }
                    break;
                case 'Integer':
                    if (!filter_var($fieldValue, FILTER_VALIDATE_INT)) {
                        $isValid = false;
                        $errorMessages[$fieldName] = $rules['error_messages']['type'] ?? 'Invalid type. Expected Integer.';
                    }
                    break;
                case 'Float':
                    if (!filter_var($fieldValue, FILTER_VALIDATE_FLOAT)) {
                        $isValid = false;
                        $errorMessages[$fieldName] = $rules['error_messages']['type'] ?? 'Invalid type. Expected Float.';
                    }
                    break;
                case 'Boolean':
                    if (!filter_var($fieldValue, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE)) {
                        $isValid = false;
                        $errorMessages[$fieldName] = $rules['error_messages']['type'] ?? 'Invalid type. Expected Boolean.';
                    }
                    break;
                case 'Array':
                    if (!is_array($fieldValue)) {
                        $isValid = false;
                        $errorMessages[$fieldName] = $rules['error_messages']['type'] ?? 'Invalid type. Expected Array.';
                    }
                    break;
                case 'Object':
                    if (!is_object($fieldValue)) {
                        $isValid = false;
                        $errorMessages[$fieldName] = $rules['error_messages']['type'] ?? 'Invalid type. Expected Object.';
                    }
                    break;
                case 'Email':
                    if (!filter_var($fieldValue, FILTER_VALIDATE_EMAIL, $rules['filter_options'] ?? [])) {
                        $isValid = false;
                        $errorMessages[$fieldName] = $rules['error_messages']['type'] ?? 'Invalid email address.';
                    }
                    break;
                case 'URL':
                    if (!filter_var($fieldValue, FILTER_VALIDATE_URL, $rules['filter_options'] ?? [])) {
                        $isValid = false;
                        $errorMessages[$fieldName] = $rules['error_messages']['type'] ?? 'Invalid URL.';
                    }
                    break;
                case 'Domain':
                    if (!filter_var($fieldValue, FILTER_VALIDATE_DOMAIN, $rules['filter_options'] ?? [])) {
                        $isValid = false;
                        $errorMessages[$fieldName] = $rules['error_messages']['type'] ?? 'Invalid domain.';
                    }
                    break;
                case 'IP':
                    if (!filter_var($fieldValue, FILTER_VALIDATE_IP, $rules['filter_options'] ?? [])) {
                        $isValid = false;
                        $errorMessages[$fieldName] = $rules['error_messages']['type'] ?? 'Invalid IP address.';
                    }
                    break;
                case 'MAC':
                    if (!filter_var($fieldValue, FILTER_VALIDATE_MAC, $rules['filter_options'] ?? [])) {
                        $isValid = false;
                        $errorMessages[$fieldName] = $rules['error_messages']['type'] ?? 'Invalid MAC address.';
                    }
                    break;
                case 'Date':
                    if (!strtotime($fieldValue)) {
                        $isValid = false;
                        $errorMessages[$fieldName] = $rules['error_messages']['type'] ?? 'Invalid date format.';
                    }
                    break;
                case 'Time':
                    $timeFormat = $rules['time_format'] ?? 'H:i';
                    $d = \DateTime::createFromFormat($timeFormat, $fieldValue);
                    if (!$d || $d->format($timeFormat) !== $fieldValue) {
                        $isValid = false;
                        $errorMessages[$fieldName] = $rules['error_messages']['type'] ?? 'Invalid time format.';
                    }
                    break;
                case 'file':
                    if (isset($_FILES[$fieldName])) {
                        $file = $_FILES[$fieldName];
                        $allowedExtensions = $rules['allowed_extensions'] ?? [];
                        $maxSize = $rules['max_size'] ?? PHP_INT_MAX;

                        $fileExtension = pathinfo($file['name'], PATHINFO_EXTENSION);
                        if (!in_array($fileExtension, $allowedExtensions)) {
                            $isValid = false;
                            $errorMessages[$fieldName] = $rules['error_messages']['allowed_extensions'] ?? 'Invalid file extension.';
                        }
                        if ($file['size'] > $maxSize) {
                            $isValid = false;
                            $errorMessages[$fieldName] = $rules['error_messages']['max_size'] ?? 'File size exceeds limit.';
                        }
                    } else {
                        $isValid = false;
                        $errorMessages[$fieldName] = $rules['error_messages']['required'] ?? 'File is required.';
                    }
                    break;
                default:
                    $isValid = false;
                    $errorMessages[$fieldName] = 'Invalid field type.';
            }
        } else {
            $isValid = false;
            $errorMessages[$fieldName] = 'Field type not specified.';
        }

        // Validate min_length
        if (isset($rules['min_length']) && strlen($fieldValue) < $rules['min_length']) {
            $isValid = false;
            $errorMessages[$fieldName] = $rules['error_messages']['min_length'] ?? 'Minimum length not met.';
        }

        // Validate max_length
        if (isset($rules['max_length']) && strlen($fieldValue) > $rules['max_length']) {
            $isValid = false;
            $errorMessages[$fieldName] = $rules['error_messages']['max_length'] ?? 'Maximum length exceeded.';
        }

        // Validate min_value
        if (isset($rules['min_value']) && $fieldValue < $rules['min_value']) {
            $isValid = false;
            $errorMessages[$fieldName] = $rules['error_messages']['min_value'] ?? 'Value is less than minimum allowed.';
        }

        // Validate max_value
        if (isset($rules['max_value']) && $fieldValue > $rules['max_value']) {
            $isValid = false;
            $errorMessages[$fieldName] = $rules['error_messages']['max_value'] ?? 'Value is more than maximum allowed.';
        }

        // Validate regex
        if (isset($rules['regex']) && !preg_match($rules['regex'], $fieldValue)) {
            $isValid = false;
            $errorMessages[$fieldName] = $rules['error_messages']['regex'] ?? 'Invalid format.';
        }

        // Validate inclusion in set
        if (isset($rules['inclusion']) && !in_array($fieldValue, $rules['inclusion'])) {
            $isValid = false;
            $errorMessages[$fieldName] = $rules['error_messages']['inclusion'] ?? 'Value not in allowed set.';
        }

        // Validate exclusion from set
        if (isset($rules['exclusion']) && in_array($fieldValue, $rules['exclusion'])) {
            $isValid = false;
            $errorMessages[$fieldName] = $rules['error_messages']['exclusion'] ?? 'Value in disallowed set.';
        }


        return ['is_valid' => $isValid, 'error_messages' => $errorMessages];
    }

    public static function validateFields($fields) {
        $config = Config::FIELD_VALIDATION;
        $errorMessages = [];
        $isValid = true;

        foreach ($config as $fieldName => $rules) {
            $fieldValue = isset($fields[$fieldName]) ? $fields[$fieldName] : null;
            $validationResult = self::validate($fieldName, $fieldValue, $rules);
            if (!$validationResult['is_valid']) {
                $isValid = false;
                $errorMessages = array_merge($errorMessages, $validationResult['error_messages']);
            }
        }

        return ['is_valid' => $isValid, 'error_messages' => $errorMessages];
    }
}
