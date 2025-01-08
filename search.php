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
    <h1>Search Result</h1>
    
    <?php
    $config = require 'config.php';

    if (isset($_GET['hash_value'])) {
        $hash_value = htmlspecialchars($_GET['hash_value']);

        if ($config['captcha_type'] === 'recaptcha' && isset($_GET['g-recaptcha-response'])) {
            $recaptcha_response = $_GET['g-recaptcha-response'];
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
            $recaptcha_result = json_decode($recaptcha_verification, true);
            
            if (!$recaptcha_result['success']) {
                echo "<p>reCAPTCHA verification failed. Please try again.</p>";
                exit;
            }
        } elseif ($config['captcha_type'] === 'custom' && isset($_GET['captcha_input'])) {
            $captcha_input = htmlspecialchars($_GET['captcha_input']);
            
            if ($captcha_input !== $_SESSION['captcha_text']) {
                echo "<p>Incorrect captcha. Please try again.</p>";
                exit;
            }
        } else {
            echo "<p>No captcha input provided. Please try again.</p>";
            exit;
        }
        
        // Load API key from configuration
        $api_key = $config['api_key'];

        // Perform the search request
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://mwdb.cert.pl/api/file/$hash_value");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "accept: application/json",
            "Authorization: Bearer $api_key"
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        $file = json_decode($response, true);

        if (isset($file['message']) && $file['message'] == 'Object not found') {
            echo "<p>File not found.</p>";
        } else {
            echo "<table border='1'>";
            echo "<thead>";
            echo "<tr><th>Key</th><th>Value</th></tr>";
            echo "</thead>";
            echo "<tbody>";
            echo "<tr><td>File name</td><td>" . htmlspecialchars($file['file_name']) . "</td></tr>";
            if (!empty($file['alt_names'])) {
                foreach ($file['alt_names'] as $alt_name) {
                    echo "<tr><td>Alt name</td><td>" . htmlspecialchars($alt_name) . "</td></tr>";
                }
            }
            echo "<tr><td>MD5</td><td>" . htmlspecialchars($file['md5']) . "</td></tr>";
            echo "<tr><td>SHA1</td><td>" . htmlspecialchars($file['sha1']) . "</td></tr>";
            echo "<tr><td>SHA256</td><td>" . htmlspecialchars($file['sha256']) . "</td></tr>";
            echo "<tr><td>SHA512</td><td>" . htmlspecialchars($file['sha512']) . "</td></tr>";
            echo "<tr><td>CRC32</td><td>" . htmlspecialchars($file['crc32']) . "</td></tr>";
            echo "<tr><td>ssdeep</td><td>" . htmlspecialchars($file['ssdeep']) . "</td></tr>";
            echo "<tr><td>File type</td><td>" . htmlspecialchars($file['file_type']) . "</td></tr>";
            echo "<tr><td>File size</td><td>" . htmlspecialchars($file['file_size']) . "</td></tr>";
            echo "</tbody>";
            echo "</table>";
        }
    } else {
        echo "<p>No hash value provided. Please try again.</p>";
    }
    ?>
    
    <a href="index.php">Go back</a>
</body>
</html>