<?php
session_start();
$config = require 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['upload_csrf_token']) || $_POST['upload_csrf_token'] !== $_SESSION['csrf_token']) {
        echo "<p>Invalid CSRF token. Please try again.</p>";
        exit;
    }

    if ($config['captcha_type'] === 'recaptcha' && isset($_POST['g-recaptcha-response'])) {
        $recaptcha_response = filter_input(INPUT_POST, 'g-recaptcha-response', FILTER_SANITIZE_STRING);
        $secret_key = $config['recaptcha_secret_key'];
        
        // Verify reCAPTCHA
        $recaptcha_url = 'https://www.google.com/recaptcha/api/siteverify';
        $recaptcha_data = [
            'secret' => $secret_key,
            'response' => $recaptcha_response
        ];
        $options = [
            'http' => [
                'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                'method'  => 'POST',
                'content' => http_build_query($recaptcha_data)
            ]
        ];
        $context  = stream_context_create($options);
        $recaptcha_verification = file_get_contents($recaptcha_url, false, $context);
        if ($recaptcha_verification === FALSE) {
            error_log('reCAPTCHA verification request failed.');
            echo "<p>reCAPTCHA verification request failed. Please try again.</p>";
            exit;
        }
        $recaptcha_result = json_decode($recaptcha_verification, true);
        
        if (!$recaptcha_result['success']) {
            echo "<p>reCAPTCHA verification failed. Please try again.</p>";
            exit;
        }
    } elseif ($config['captcha_type'] === 'custom' && isset($_POST['upload_captcha_input'])) {
        $captcha_input = filter_input(INPUT_POST, 'upload_captcha_input', FILTER_SANITIZE_STRING);
        
        if ($captcha_input !== $_SESSION['captcha_text']) {
            echo "<p>Incorrect captcha. Please try again.</p>";
            exit;
        }
    } else {
        echo "<p>No captcha input provided. Please try again.</p>";
        exit;
    }

    if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
        $file_tmp_path = $_FILES['file']['tmp_name'];
        $file_name = basename($_FILES['file']['name']);

        $api_url = $config['api_url'];
        $api_key = $config['api_key'];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "$api_url/file");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "accept: application/json",
            "Authorization: Bearer $api_key",
            "Content-Type: multipart/form-data"
        ]);
        $post_fields = [
            'file' => new CURLFile($file_tmp_path, $_FILES['file']['type'], $file_name)
        ];
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);

        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            error_log('CURL error: ' . curl_error($ch));
            echo "<p>Error uploading file. Please try again later.</p>";
            curl_close($ch);
            exit;
        }
        curl_close($ch);

        $response_data = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log('JSON decode error: ' . json_last_error_msg());
            echo "<p>Error processing response. Please try again later.</p>";
            exit;
        }

        // Handle different response codes
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        switch ($http_code) {
            case 200:
                echo "<p>File uploaded successfully!</p>";
                echo "<pre>" . print_r($response_data, true) . "</pre>";
                break;
            case 403:
                echo "<p>No permissions to perform additional operations.</p>";
                break;
            case 404:
                echo "<p>One of attribute keys doesn't exist or user doesn't have permission to set it.</p>";
                break;
            case 409:
                echo "<p>Object exists yet but has different type.</p>";
                break;
            case 503:
                echo "<p>Request canceled due to database statement timeout.</p>";
                break;
            default:
                echo "<p>Unexpected response code: $http_code</p>";
                echo "<pre>" . print_r($response_data, true) . "</pre>";
                break;
        }
    } else {
        echo "<p>No file uploaded or there was an upload error.</p>";
    }
} else {
    echo "<p>Invalid request method.</p>";
}
?>
