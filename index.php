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
    <h1>File Search</h1>
    
    <!-- Search Form -->
    <form action="search.php" method="get">
        <label for="hash_value">Enter Hash:</label>
        <input type="text" id="hash_value" name="hash_value" size="150" maxlength="150" required><br><br>
        
        <?php if ($config['captcha_type'] === 'recaptcha'): ?>
            <!-- Google reCAPTCHA -->
            <div class="g-recaptcha" data-sitekey="<?php echo $config['recaptcha_site_key']; ?>"></div>
        <?php else: ?>
            <!-- Custom CAPTCHA -->
            <img src="generate_captcha.php?<?php echo uniqid(); ?>" alt="CAPTCHA Image"><br><br>
            <label for="captcha_input">Enter Captcha:</label>
            <input type="text" id="captcha_input" name="captcha_input" required><br><br>
        <?php endif; ?>
        
        <button type="submit">Search</button>
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
            curl_setopt($ch, CURLOPT_URL, $api_url . "/file?count=10");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                "accept: application/json",
                "Authorization: Bearer $api_key"
            ]);

            $response = curl_exec($ch);
            curl_close($ch);

            $files = json_decode($response, true)['files'];

            if (!empty($files)) {
                foreach ($files as $file) {
                    echo "<tr>";
                    echo "<td>";
                    echo "File Name: " . htmlspecialchars($file['file_name']) . "<br>";
                    echo "MD5: " . htmlspecialchars($file['md5']) . "<br>";
                    echo "SHA1: " . htmlspecialchars($file['sha1']) . "<br>";
                    echo "SHA256: " . htmlspecialchars($file['sha256']);
                    echo "</td>";
                    echo "<td>";
                    echo "File Type: " . htmlspecialchars($file['file_type']) . "<br>";
                    echo "File Size: " . htmlspecialchars($file['file_size']);
                    echo "</td>";
                    echo "<td>";
                    foreach ($file['tags'] as $tag) {
                        echo htmlspecialchars($tag['tag']) . "<br>";
                    }
                    echo "</td>";
                    echo "<td>" . htmlspecialchars($file['upload_time']) . "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='4'>No recent files found.</td></tr>";
            }
            ?>
        </tbody>
    </table>
</body>
</html>
