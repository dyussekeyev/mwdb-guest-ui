<?php
/**
 * CAPTCHA Validator
 * Handles both reCAPTCHA and custom CAPTCHA verification
 */
class CaptchaValidator {
    private string $captcha_type;
    private string $recaptcha_secret_key;

    public function __construct(array $config) {
        $this->captcha_type = $config['captcha_type'];
        $this->recaptcha_secret_key = $config['recaptcha_secret_key'] ?? '';
    }

    /**
     * Validate CAPTCHA based on the configured type
     * 
     * @param array $input Input data (GET or POST)
     * @param string $session_key Session key for custom CAPTCHA
     * @return array Result with 'valid' and 'error' keys
     */
    public function validate(array $input, string $session_key): array {
        return $this->captcha_type === 'recaptcha' 
            ? $this->validateRecaptcha($input) 
            : $this->validateCustomCaptcha($input, $session_key);
    }

    /**
     * Validate Google reCAPTCHA
     * 
     * @param array $input Input data containing recaptcha_token
     * @return array Result with 'valid' and 'error' keys
     */
    private function validateRecaptcha(array $input): array {
        if (!isset($input['recaptcha_token'])) {
            return [
                'valid' => false,
                'error' => 'No reCAPTCHA token provided'
            ];
        }

        $recaptcha_token = trim(strip_tags($input['recaptcha_token']));
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://www.google.com/recaptcha/api/siteverify');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'secret' => $this->recaptcha_secret_key,
            'response' => $recaptcha_token
        ]));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        
        $recaptcha_verification = curl_exec($ch);
        if (curl_errno($ch)) {
            error_log('reCAPTCHA verification request failed: ' . curl_error($ch));
            curl_close($ch);
            return [
                'valid' => false,
                'error' => 'reCAPTCHA verification request failed'
            ];
        }
        curl_close($ch);
        
        $recaptcha_result = json_decode($recaptcha_verification, true);
        
        if (!$recaptcha_result || !isset($recaptcha_result['success']) || !$recaptcha_result['success']) {
            error_log('reCAPTCHA verification failed: ' . ($recaptcha_verification ?: 'Invalid response'));
            return [
                'valid' => false,
                'error' => 'reCAPTCHA verification failed'
            ];
        }

        return ['valid' => true];
    }

    /**
     * Validate custom CAPTCHA
     * 
     * @param array $input Input data containing captcha input
     * @param string $session_key Session key where captcha text is stored
     * @return array Result with 'valid' and 'error' keys
     */
    private function validateCustomCaptcha(array $input, string $session_key): array {
        $input_key = $session_key . '_input';
        
        if (!isset($input[$input_key])) {
            return [
                'valid' => false,
                'error' => 'No captcha input provided'
            ];
        }

        $captcha_input = trim(strip_tags($input[$input_key]));
        
        if (!isset($_SESSION[$session_key])) {
            return [
                'valid' => false,
                'error' => 'CAPTCHA session expired'
            ];
        }

        if ($captcha_input !== $_SESSION[$session_key]) {
            return [
                'valid' => false,
                'error' => 'Incorrect captcha'
            ];
        }

        // Clear captcha after successful validation
        unset($_SESSION[$session_key]);

        return ['valid' => true];
    }
}
?>
