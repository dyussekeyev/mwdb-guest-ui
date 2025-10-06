<?php
/**
 * Input Validator
 * Common input validation functions
 */
class InputValidator {
    /**
     * Validate CSRF token
     * 
     * @param string $token Token from request
     * @return bool True if valid
     */
    public static function validateCsrfToken($token) {
        return isset($_SESSION['csrf_token']) && $token === $_SESSION['csrf_token'];
    }

    /**
     * Validate hash format (MD5, SHA1, SHA256, SHA512)
     * 
     * @param string $hash Hash value
     * @return bool True if valid
     */
    public static function validateHash($hash) {
        return preg_match('/^[a-fA-F0-9]{32}$|^[a-fA-F0-9]{40}$|^[a-fA-F0-9]{64}$|^[a-fA-F0-9]{128}$/', $hash);
    }

    /**
     * Validate file upload
     * 
     * @param int $max_size Maximum file size in bytes
     * @return array Result with 'valid' and 'error' keys
     */
    public static function validateFileUpload($max_size = 10485760) {
        if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            return [
                'valid' => false,
                'error' => 'No file uploaded or there was an upload error'
            ];
        }

        if (
            $_FILES['file']['error'] === UPLOAD_ERR_INI_SIZE ||
            $_FILES['file']['error'] === UPLOAD_ERR_FORM_SIZE ||
            $_FILES['file']['size'] > $max_size
        ) {
            return [
                'valid' => false,
                'error' => 'File is too large. Maximum allowed size is 10 MB'
            ];
        }

        return ['valid' => true];
    }
}
?>
