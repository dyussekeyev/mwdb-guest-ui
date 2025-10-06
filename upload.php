<?php
session_start();
require_once 'security_headers.php';
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

        if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            echo "<p>No file uploaded or there was an upload error.</p>";
            echo '<a href="index.php" style="font-size:20px;">Go back</a>';
            exit;
        }

        if (
            $_FILES['file']['error'] === UPLOAD_ERR_INI_SIZE ||
            $_FILES['file']['error'] === UPLOAD_ERR_FORM_SIZE ||
			$_FILES['file']['size'] > 10485760
        ) {
            echo "<p>File is too large. Maximum allowed size is 10 MB.</p>";
            echo '<a href="index.php" style="font-size:20px;">Go back</a>';
            exit;
        }

        if (!isset($_POST['upload_csrf_token']) || $_POST['upload_csrf_token'] !== $_SESSION['csrf_token']) {
            echo "<p>Invalid CSRF token. Please try again.</p>";
            echo '<a href="index.php" style="font-size:20px;">Go back</a>';
            exit;
        }

        if ($config['captcha_type'] === 'recaptcha' && isset($_POST['recaptcha_token'])) {
            $recaptcha_token = isset($_POST['recaptcha_token']) ? trim(strip_tags($_POST['recaptcha_token'])) : '';
            $secret_key = $config['recaptcha_secret_key'];
            
            // Verify reCAPTCHA using CURL for better timeout control
            $recaptcha_url = 'https://www.google.com/recaptcha/api/siteverify';
            $recaptcha_data = [
                'secret' => $secret_key,
                'response' => $recaptcha_token
            ];
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $recaptcha_url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($recaptcha_data));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
            
            $recaptcha_verification = curl_exec($ch);
            if (curl_errno($ch)) {
                error_log('reCAPTCHA verification request failed: ' . curl_error($ch));
                curl_close($ch);
                echo "<p>reCAPTCHA verification request failed. Please try again.</p>";
                echo '<a href="index.php" style="font-size:20px;">Go back</a>';
                exit;
            }
            curl_close($ch);
            
            $recaptcha_result = json_decode($recaptcha_verification, true);
            
            if (!$recaptcha_result || !isset($recaptcha_result['success']) || !$recaptcha_result['success']) {
                error_log('reCAPTCHA verification failed: ' . ($recaptcha_verification ?: 'Invalid response'));
                echo "<p>reCAPTCHA verification failed. Please try again.</p>";
                echo '<a href="index.php" style="font-size:20px;">Go back</a>';
                exit;
            }
        } elseif ($config['captcha_type'] === 'custom' && isset($_POST['upload_captcha_input'])) {
            $captcha_input = isset($_POST['upload_captcha_input']) ? trim(strip_tags($_POST['upload_captcha_input'])) : '';
            
            if ($captcha_input !== $_SESSION['captcha_text_upload']) {
                echo "<p>Incorrect captcha. Please try again.</p>";
                echo '<a href="index.php" style="font-size:20px;">Go back</a>';
                exit;
            }
            // Clear captcha after successful validation
            unset($_SESSION['captcha_text_upload']);
        } else {
            echo "<p>No captcha input provided. Please try again.</p>";
            echo '<a href="index.php" style="font-size:20px;">Go back</a>';
            exit;
        }

        $file_tmp_path = $_FILES['file']['tmp_name'];
        $file_name = basename($_FILES['file']['name']);

        $api_url = $config['api_url'];
        $api_key = $config['api_key'];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "$api_url/file");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
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
            echo '<a href="index.php" style="font-size:20px;">Go back</a>';
            curl_close($ch);
            exit;
        }
        
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($http_code < 200 || $http_code >= 300) {
            echo "<p>Error uploading file. HTTP code: $http_code</p>";
            echo '<a href="index.php" style="font-size:20px;">Go back</a>';
            exit;
        }

        $response_data = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log('JSON decode error: ' . json_last_error_msg());
            echo "<p>Error processing response. Please try again later.</p>";
            echo '<a href="index.php" style="font-size:20px;">Go back</a>';
            exit;
        }

        if (isset($response_data)) {
            echo "<table border='1'>";
            echo "<thead>";
            echo "<tr><th>Key</th><th>Value</th></tr>";
            echo "</thead>";
            echo "<tbody>";
            echo "<tr><td>File name</td><td>" . htmlspecialchars($response_data['file_name'] ?? '') . "</td></tr>";
            echo "<tr><td>MD5</td><td>" . htmlspecialchars($response_data['md5'] ?? '') . "</td></tr>";
            echo "<tr><td>SHA1</td><td>" . htmlspecialchars($response_data['sha1'] ?? '') . "</td></tr>";
            echo "<tr><td>SHA256</td><td>" . htmlspecialchars($response_data['sha256'] ?? '') . "</td></tr>";
            echo "<tr><td>SHA512</td><td>" . htmlspecialchars($response_data['sha512'] ?? '') . "</td></tr>";
            echo "<tr><td>CRC32</td><td>" . htmlspecialchars($response_data['crc32'] ?? '') . "</td></tr>";
            echo "<tr><td>ssdeep</td><td>" . htmlspecialchars($response_data['ssdeep'] ?? '') . "</td></tr>";
            echo "<tr><td>File type</td><td>" . htmlspecialchars($response_data['file_type'] ?? '') . "</td></tr>";
            echo "<tr><td>File size</td><td>" . htmlspecialchars($response_data['file_size'] ?? '') . "</td></tr>";
            echo "</tbody>";
            echo "</table>";
        }
        ?>
        <a href="index.php" style="font-size:20px;">Go back</a>
    </div>
</body>
</html>
