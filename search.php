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
    <title>Search Result</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h1>Search Result</h1>
        
        <?php
        $config = require 'config.php';

        // Validate hash value exists
        if (!isset($_GET['hash_value'])) {
            HtmlHelper::renderError('No hash value provided. Please try again.');
            exit;
        }

        // Validate CSRF token
        if (!isset($_GET['search_csrf_token']) || !InputValidator::validateCsrfToken($_GET['search_csrf_token'])) {
            HtmlHelper::renderError('Invalid CSRF token. Please try again.');
            exit;
        }

        // Sanitize and validate hash
        $hash_value = trim(strip_tags($_GET['hash_value']));
        if (empty($hash_value) || !InputValidator::validateHash($hash_value)) {
            HtmlHelper::renderError('Invalid hash format. Please provide a valid MD5, SHA1, SHA256, or SHA512 hash.');
            exit;
        }

        // Validate CAPTCHA
        $captchaValidator = new CaptchaValidator($config);
        $captchaResult = $captchaValidator->validate($_GET, 'captcha_text_search');
        
        if (!$captchaResult['valid']) {
            HtmlHelper::renderError($captchaResult['error'] . '. Please try again.');
            exit;
        }
        
        // Fetch file information
        $apiClient = new ApiClient($config);
        $fileResult = $apiClient->getFile($hash_value);

        if (!$fileResult['success']) {
            HtmlHelper::renderError('Error fetching file. ' . $fileResult['error']);
            exit;
        }

        $file = $fileResult['data'];

        if (isset($file['message']) && $file['message'] == 'Object not found') {
            HtmlHelper::renderError('File not found.');
        } else {
            HtmlHelper::renderFileTable($file);
        }

        // Fetch comments
        $commentsResult = $apiClient->getFileComments($hash_value);

        if (!$commentsResult['success']) {
            HtmlHelper::renderError('Error fetching comments. ' . $commentsResult['error']);
            exit;
        }

        HtmlHelper::renderCommentsTable($commentsResult['data']);
        ?>
        <a href="index.php" style="font-size:20px;">Go back</a>
    </div>
</body>
</html>
