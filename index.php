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
    <title>Malware Database</title>
    <link rel="stylesheet" href="styles.css">
    <?php
    $config = require 'config.php';
    if ($config['captcha_type'] === 'recaptcha') {
        echo '<script src="https://www.google.com/recaptcha/api.js?render=' . htmlspecialchars($config['recaptcha_site_key']) . '"></script>';
    }
    ?>
    <script src="scripts.js"></script>
</head>
<body onload="showTab('file-search')">
    <div class="container">
        <h1>Malware Database</h1>
        
        <div class="tab-buttons">
            <div id="file-search-button" onclick="showTab('file-search')">File Search</div>
            <div id="file-upload-button" onclick="showTab('file-upload')">File Upload</div>
        </div>
        
        <!-- Search Form -->
        <div id="file-search" class="tab">
            <form id="search-form" action="search.php" method="get">
                <label for="hash_value">Enter Hash:</label>
                <input type="text" id="hash_value" name="hash_value" size="150" maxlength="150" required><br><br>
                
                <?php if ($config['captcha_type'] === 'recaptcha'): ?>
                    <input type="hidden" id="recaptcha_token" name="recaptcha_token">
                <?php else: ?>
                    <!-- Custom CAPTCHA -->
                    <img src="generate_captcha.php?type=search&<?php echo uniqid(); ?>" alt="CAPTCHA Image"><br><br>
                    <label for="search_captcha_input">Enter Captcha:</label>
                    <input type="text" id="search_captcha_input" name="search_captcha_input" required><br><br>
                <?php endif; ?>
                
                <input type="hidden" name="search_csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                <button type="submit" style="height:50px; width:150px" onclick="executeRecaptcha(event, 'search')">Search</button>
            </form>
        </div>
        
        <!-- Upload Form -->
        <div id="file-upload" class="tab">
            <form id="upload-form" action="upload.php" method="post" enctype="multipart/form-data">
                <label for="file">Choose file:</label>
                <input type="file" id="file" name="file" required><br><br>
                
                <?php if ($config['captcha_type'] === 'recaptcha'): ?>
                    <input type="hidden" id="recaptcha_token" name="recaptcha_token">
                <?php else: ?>
                    <!-- Custom CAPTCHA -->
                    <img src="generate_captcha.php?type=upload&<?php echo uniqid(); ?>" alt="CAPTCHA Image"><br><br>
                    <label for="upload_captcha_input">Enter Captcha:</label>
                    <input type="text" id="upload_captcha_input" name="upload_captcha_input" required><br><br>
                <?php endif; ?>
                
                <input type="hidden" name="upload_csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                <button type="submit" style="height:50px; width:150px" onclick="executeRecaptcha(event, 'upload')">Upload</button>
            </form>
        </div>
        
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
                $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

                if (curl_errno($ch)) {
                    error_log('CURL error: ' . curl_error($ch));
                    echo "<tr><td colspan='4'>Error fetching recent files. Please check the logs for details.</td></tr>";
                } elseif ($http_code !== 200) {
                    echo "<tr><td colspan='4'>Error fetching recent files. HTTP code: $http_code</td></tr>";
                } else {
                    $files = json_decode($response, true);
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        error_log('JSON decode error: ' . json_last_error_msg());
                        echo "<tr><td colspan='4'>Error decoding response. Please check the logs for details.</td></tr>";
                    } else {
                        if (!empty($files['files'])) {
                            foreach ($files['files'] as $file) {
                                echo "<tr>";
                                echo "<td class='table-column-name'>";
                                $file_name = htmlspecialchars($file['file_name'] ?? '');
                                if (strlen($file_name) > 30) {
                                    $file_name = substr($file_name, 0, 60) . '[...]';
                                }
                                echo "File Name: " . $file_name . "<br>";
                                echo "MD5: " . htmlspecialchars($file['md5'] ?? '') . "<br>";
                                echo "SHA1: " . htmlspecialchars($file['sha1'] ?? '') . "<br>";
                                echo "SHA256: " . htmlspecialchars($file['sha256'] ?? '');
                                echo "</td>";
                                echo "<td>";
                                echo "File Type: " . htmlspecialchars($file['file_type'] ?? '') . "<br>";
                                echo "File Size: " . htmlspecialchars($file['file_size'] ?? '');
                                echo "</td>";
                                echo "<td class='table-column-tags'>";
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
                ?>
            </tbody>
        </table>
    </div>
    <script>
        function executeRecaptcha(event, action) {
            event.preventDefault();
            grecaptcha.ready(function() {
                grecaptcha.execute('<?php echo htmlspecialchars($config['recaptcha_site_key']); ?>', {action: action}).then(function(token) {
                    document.getElementById('recaptcha_token').value = token;
                    if (action === 'search') {
                        document.getElementById('search-form').submit();
                    } else if (action === 'upload') {
                        document.getElementById('upload-form').submit();
                    }
                });
            });
        }
    </script>
</body>
</html>
