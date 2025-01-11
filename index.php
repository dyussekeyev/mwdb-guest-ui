<?php
session_start();
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>File Search</title>
    <link rel="stylesheet" href="styles.css">
    <?php
    $config = require 'config.php';
    if ($config['captcha_type'] === 'recaptcha') {
        echo '<script src="https://www.google.com/recaptcha/api.js" async defer></script>';
    }
    ?>
</head>
<body>
    <div class="container">
        <h1>File Search</h1>
        
        <!-- Search Form -->
        <form action="search.php" method="get">
            <label for="hash_value">Enter Hash:</label>
            <input type="text" id="hash_value" name="hash_value" size="150" maxlength="150" required><br><br>
            
            <?php if ($config['captcha_type'] === 'recaptcha'): ?>
                <!-- Google reCAPTCHA -->
                <div class="g-recaptcha" data-sitekey="<?php echo htmlspecialchars($config['recaptcha_site_key']); ?>"></div>
            <?php else: ?>
                <!-- Custom CAPTCHA -->
                <img src="generate_captcha.php?<?php echo uniqid(); ?>" alt="CAPTCHA Image"><br><br>
                <label for="captcha_input">Enter Captcha:</label>
                <input type="text" id="captcha_input" name="captcha_input" required><br><br>
            <?php endif; ?>
            
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
            <button type="submit">Search</button>
        </form>

        <h1>File Upload</h1>
    
        <!-- Upload Form -->
        <form action="upload.php" method="post" enctype="multipart/form-data">
            <label for="file">Choose file:</label>
            <input type="file" id="file" name="file" required><br><br>
            
            <?php if ($config['captcha_type'] === 'recaptcha'): ?>
                <!-- Google reCAPTCHA -->
                <div class="g-recaptcha" data-sitekey="<?php echo htmlspecialchars($config['recaptcha_site_key']); ?>"></div>
            <?php else: ?>
                <!-- Custom CAPTCHA -->
                <img src="generate_captcha.php?<?php echo uniqid(); ?>" alt="CAPTCHA Image"><br><br>
                <label for="captcha_input">Enter Captcha:</label>
                <input type="text" id="captcha_input" name="captcha_input" required><br><br>
            <?php endif; ?>
            
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
            <button type="submit">Upload</button>
        </form>
        
        <h2>Recent files</h2>
        
        <!-- Recent Files Table -->
        <table border="1">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Type</th>
                    <th>Tags</th>
                    <th>Upload date</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Fetch recent files using CURL
                $api_url = $config['api_url'];
                $api_key = $config['api_key'];
    
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, "$api_url/file?count=10");
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    "accept: application/json",
                    "Authorization: Bearer $api_key"
                ]);
    
                $response = curl_exec($ch);
                if (curl_errno($ch)) {
                    error_log('CURL error: ' . curl_error($ch));
                    echo "<tr><td colspan='4'>Error fetching recent files. Please check the logs for details.</td></tr>";
                } else {
                    $files = json_decode($response, true);
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        error_log('JSON decode error: ' . json_last_error_msg());
                        echo "<tr><td colspan='4'>Error decoding response. Please check the logs for details.</td></tr>";
                    } else {
                        if (!empty($files['files'])) {
                            foreach ($files['files'] as $file) {
                                echo "<tr>";
                                echo "<td>";
                                echo "File Name: " . htmlspecialchars($file['file_name'] ?? '') . "<br>";
                                echo "MD5: " . htmlspecialchars($file['md5'] ?? '') . "<br>";
                                echo "SHA1: " . htmlspecialchars($file['sha1'] ?? '') . "<br>";
                                echo "SHA256: " . htmlspecialchars($file['sha256'] ?? '');
                                echo "</td>";
                                echo "<td>";
                                echo "File Type: " . htmlspecialchars($file['file_type'] ?? '') . "<br>";
                                echo "File Size: " . htmlspecialchars($file['file_size'] ?? '');
                                echo "</td>";
                                echo "<td>";
                                if (!empty($file['tags'])) {
                                    foreach ($file['tags'] as $tag) {
                                        echo htmlspecialchars($tag['tag'] ?? '') . "<br>";
                                    }
                                }
                                echo "</td>";
                                echo "<td>" . htmlspecialchars($file['upload_time'] ?? '') . "</td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='4'>No recent files found.</td></tr>";
                        }
                    }
                }
                curl_close($ch);
                ?>
            </tbody>
        </table>
    </div>
</body>
</html>
