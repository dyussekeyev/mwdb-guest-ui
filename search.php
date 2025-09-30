<?php
session_start();
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

        if (!isset($_GET['hash_value'])) {
            echo "<p>No hash value provided. Please try again.</p>";
            echo '<a href="index.php" style="font-size:20px;">Go back</a>';
            exit;
        }

        if (!isset($_GET['search_csrf_token']) || $_GET['search_csrf_token'] !== $_SESSION['csrf_token']) {
            echo "<p>Invalid CSRF token. Please try again.</p>";
            echo '<a href="index.php" style="font-size:20px;">Go back</a>';
            exit;
        }

        $hash_value = isset($_GET['hash_value']) ? trim(strip_tags($_GET['hash_value'])) : '';
        if (empty($hash_value)) {
            echo "<p>Invalid hash value provided. Please try again.</p>";
            echo '<a href="index.php" style="font-size:20px;">Go back</a>';
            exit;
        }

        if ($config['captcha_type'] === 'recaptcha' && isset($_GET['recaptcha_token'])) {
            $recaptcha_token = isset($_GET['recaptcha_token']) ? trim(strip_tags($_GET['recaptcha_token'])) : '';
            $secret_key = $config['recaptcha_secret_key'];
            
            // Verify reCAPTCHA
            $recaptcha_url = 'https://www.google.com/recaptcha/api/siteverify';
            $recaptcha_data = [
                'secret' => $secret_key,
                'response' => $recaptcha_token
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
                echo '<a href="index.php" style="font-size:20px;">Go back</a>';
                exit;
            }
            $recaptcha_result = json_decode($recaptcha_verification, true);
            
            if (!$recaptcha_result['success']) {
                echo "<p>reCAPTCHA verification failed. Please try again.</p>";
                echo '<a href="index.php" style="font-size:20px;">Go back</a>';
                exit;
            }
        } elseif ($config['captcha_type'] === 'custom' && isset($_GET['search_captcha_input'])) {
            $captcha_input = isset($_GET['search_captcha_input']) ? trim(strip_tags($_GET['search_captcha_input'])) : '';
            
            if ($captcha_input !== $_SESSION['captcha_text_search']) {
                echo "<p>Incorrect captcha. Please try again.</p>";
                echo '<a href="index.php" style="font-size:20px;">Go back</a>';
                exit;
            }
        } else {
            echo "<p>No captcha input provided. Please try again.</p>";
            echo '<a href="index.php" style="font-size:20px;">Go back</a>';
            exit;
        }
        
        // Load API key from configuration
        $api_url = $config['api_url'];
        $api_key = $config['api_key'];

        // Perform the search request
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "$api_url/file/$hash_value");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "accept: application/json",
            "Authorization: Bearer $api_key"
        ]);

        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            error_log('CURL error: ' . curl_error($ch));
            echo "<p>Error fetching file. Please try again later.</p>";
            echo '<a href="index.php" style="font-size:20px;">Go back</a>';
            curl_close($ch);
            exit;
        }
        
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($http_code !== 200) {
            echo "<p>Error fetching file. HTTP code: $http_code</p>";
            echo '<a href="index.php" style="font-size:20px;">Go back</a>';
            exit;
        }

        $file = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log('JSON decode error: ' . json_last_error_msg());
            echo "<p>Error processing response. Please try again later.</p>";
            echo '<a href="index.php" style="font-size:20px;">Go back</a>';
            exit;
        }

        if (isset($file['message']) && $file['message'] == 'Object not found') {
            echo "<p>File not found.</p>";
            echo '<a href="index.php" style="font-size:20px;">Go back</a>';
        } else {
            echo "<table border='1'>";
            echo "<thead>";
            echo "<tr><th>Key</th><th>Value</th></tr>";
            echo "</thead>";
            echo "<tbody>";
            echo "<tr><td>File name</td><td>" . htmlspecialchars($file['file_name'] ?? '') . "</td></tr>";
            if (!empty($file['alt_names'])) {
                foreach ($file['alt_names'] as $alt_name) {
                    echo "<tr><td>Alt name</td><td>" . htmlspecialchars($alt_name) . "</td></tr>";
                }
            }
            echo "<tr><td>MD5</td><td>" . htmlspecialchars($file['md5'] ?? '') . "</td></tr>";
            echo "<tr><td>SHA1</td><td>" . htmlspecialchars($file['sha1'] ?? '') . "</td></tr>";
            echo "<tr><td>SHA256</td><td>" . htmlspecialchars($file['sha256'] ?? '') . "</td></tr>";
            echo "<tr><td>SHA512</td><td>" . htmlspecialchars($file['sha512'] ?? '') . "</td></tr>";
            echo "<tr><td>CRC32</td><td>" . htmlspecialchars($file['crc32'] ?? '') . "</td></tr>";
            echo "<tr><td>ssdeep</td><td>" . htmlspecialchars($file['ssdeep'] ?? '') . "</td></tr>";
            echo "<tr><td>File type</td><td>" . htmlspecialchars($file['file_type'] ?? '') . "</td></tr>";
            echo "<tr><td>File size</td><td>" . htmlspecialchars($file['file_size'] ?? '') . "</td></tr>";
            echo "</tbody>";
            echo "</table>";
        }

        // Perform the comments request
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "$api_url/file/$hash_value/comment");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "accept: application/json",
            "Authorization: Bearer $api_key"
        ]);

        $comments_response = curl_exec($ch);
        if (curl_errno($ch)) {
            error_log('CURL error: ' . curl_error($ch));
            echo "<p>Error fetching comments. Please try again later.</p>";
            echo '<a href="index.php" style="font-size:20px;">Go back</a>';
            curl_close($ch);
            exit;
        }

        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($http_code !== 200) {
            echo "<p>Error fetching comments. HTTP code: $http_code</p>";
            echo '<a href="index.php" style="font-size:20px;">Go back</a>';
            exit;
        }

        $comments = json_decode($comments_response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log('JSON decode error: ' . json_last_error_msg());
            echo "<p>Error processing comments response. Please try again later.</p>";
            echo '<a href="index.php" style="font-size:20px;">Go back</a>';
            exit;
        }

        if (!empty($comments)) {
            echo "<table border='1' style='width:100%; margin-top:20px;'>";
            echo "<thead>";
            echo "<tr><th style='width:25%;'>Author and Date</th><th style='width:75%;'>Comment</th></tr>";
            echo "</thead>";
            echo "<tbody>";
            foreach ($comments as $comment) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($comment['author']) . "<br>" . htmlspecialchars($comment['timestamp']) . "</td>";
                echo "<td>" . nl2br(htmlspecialchars($comment['comment'])) . "</td>";
                echo "</tr>";
            }
            echo "</tbody>";
            echo "</table>";
        } else {
            echo "<p>No comments found.</p>";
        }
        ?>
        <a href="index.php" style="font-size:20px;">Go back</a>
    </div>
</body>
</html>
