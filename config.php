<?php
return [
    'api_url' => getenv('API_URL'),
    'api_key' => getenv('API_KEY'),
    'recaptcha_site_key' => getenv('RECAPTCHA_SITE_KEY'),
    'recaptcha_secret_key' => getenv('RECAPTCHA_SECRET_KEY'),
    'captcha_type' => getenv('CAPTCHA_TYPE')
];
?>
