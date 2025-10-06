<?php
session_start();
require_once 'security_headers.php';
require_once 'ApiClient.php';
require_once 'CaptchaValidator.php';
require_once 'InputValidator.php';
require_once 'HtmlHelper.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Result</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h1>Upload Result</h1>

        <?php
        $config = require 'config.php';

        // Validate file upload
        $fileValidation = InputValidator::validateFileUpload();
        if (!$fileValidation['valid']) {
            HtmlHelper::renderError($fileValidation['error']);
            exit;
        }

        // Validate CSRF token
        if (!isset($_POST['upload_csrf_token']) || !InputValidator::validateCsrfToken($_POST['upload_csrf_token'])) {
            HtmlHelper::renderError('Invalid CSRF token. Please try again.');
            exit;
        }

        // Validate CAPTCHA
        $captchaValidator = new CaptchaValidator($config);
        $captchaResult = $captchaValidator->validate($_POST, 'captcha_text_upload');
        
        if (!$captchaResult['valid']) {
            HtmlHelper::renderError($captchaResult['error'] . '. Please try again.');
            exit;
        }

        // Upload file
        $file_tmp_path = $_FILES['file']['tmp_name'];
        $file_name = basename($_FILES['file']['name']);
        $file_type = $_FILES['file']['type'];

        $apiClient = new ApiClient($config);
        $uploadResult = $apiClient->uploadFile($file_tmp_path, $file_name, $file_type);

        if (!$uploadResult['success']) {
            HtmlHelper::renderError('Error uploading file. ' . $uploadResult['error']);
            exit;
        }

        HtmlHelper::renderFileTable($uploadResult['data']);
        ?>
        <a href="index.php" style="font-size:20px;">Go back</a>
    </div>
</body>
</html>
