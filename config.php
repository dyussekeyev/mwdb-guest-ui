<?php
$config = [
    'api_url' => getenv('API_URL'),
    'api_key' => getenv('API_KEY'),
    'recaptcha_site_key' => getenv('RECAPTCHA_SITE_KEY'),
    'recaptcha_secret_key' => getenv('RECAPTCHA_SECRET_KEY'),
    'captcha_type' => getenv('CAPTCHA_TYPE')
];

// Validate required configuration
if (empty($config['api_url']) || empty($config['api_key'])) {
    error_log('Configuration error: API_URL and API_KEY must be set');
    die('Configuration error. Please check the server logs.');
}

// Validate CAPTCHA configuration
if (!in_array($config['captcha_type'], ['recaptcha', 'custom'])) {
    error_log('Configuration error: CAPTCHA_TYPE must be either "recaptcha" or "custom"');
    die('Configuration error. Please check the server logs.');
}

if ($config['captcha_type'] === 'recaptcha' && 
    (empty($config['recaptcha_site_key']) || empty($config['recaptcha_secret_key']))) {
    error_log('Configuration error: RECAPTCHA_SITE_KEY and RECAPTCHA_SECRET_KEY must be set when using reCAPTCHA');
    die('Configuration error. Please check the server logs.');
}

return $config;
?>
