<?php
session_start();
require_once 'security_headers.php';
require_once 'ApiClient.php';
require_once 'HtmlHelper.php';
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
                    <input type="hidden" id="recaptcha_token_search" name="recaptcha_token">
                <?php else: ?>
                    <!-- Custom CAPTCHA -->
                    <img src="generate_captcha.php?type=search&<?php echo uniqid(); ?>" alt="CAPTCHA Image"><br><br>
                    <label for="search_captcha_input">Enter Captcha:</label>
                    <input type="text" id="search_captcha_input" name="search_captcha_input" required><br><br>
                <?php endif; ?>
                
                <input type="hidden" name="search_csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                <button type="submit" style="height:50px; width:150px" <?php if ($config['captcha_type'] === 'recaptcha'): ?>onclick="executeRecaptcha(event, 'search')"<?php endif; ?>>Search</button>
            </form>
        </div>
        <!-- Upload Form -->
        <div id="file-upload" class="tab">
            <form id="upload-form" action="upload.php" method="post" enctype="multipart/form-data">
                <label for="file">Choose file:</label>
                <input type="file" id="file" name="file" required><br><br>
                <input type="hidden" name="MAX_FILE_SIZE" value="10485760">
                <?php if ($config['captcha_type'] === 'recaptcha'): ?>
                    <input type="hidden" id="recaptcha_token_upload" name="recaptcha_token">
                <?php else: ?>
                    <!-- Custom CAPTCHA -->
                    <img src="generate_captcha.php?type=upload&<?php echo uniqid(); ?>" alt="CAPTCHA Image"><br><br>
                    <label for="upload_captcha_input">Enter Captcha:</label>
                    <input type="text" id="upload_captcha_input" name="upload_captcha_input" required><br><br>
                <?php endif; ?>
                
                <input type="hidden" name="upload_csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                <button type="submit" style="height:50px; width:150px" <?php if ($config['captcha_type'] === 'recaptcha'): ?>onclick="executeRecaptcha(event, 'upload')"<?php endif; ?>>Upload</button>
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
                // Fetch recent files using API client
                $config = require 'config.php';
                $apiClient = new ApiClient($config);
                $result = $apiClient->getRecentFiles(10);

                if (!$result['success']) {
                    echo "<tr><td colspan='4'>Error fetching recent files. Please check the logs for details.</td></tr>";
                } else {
                    $files = $result['data'];
                    if (!empty($files['files'])) {
                        foreach ($files['files'] as $file) {
                            HtmlHelper::renderRecentFileRow($file);
                        }
                    } else {
                        echo "<tr><td colspan='4'>No recent files found.</td></tr>";
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
                    if (action === 'search') {
                        document.getElementById('recaptcha_token_search').value = token;
                        document.getElementById('search-form').submit();
                    } else if (action === 'upload') {
                        document.getElementById('recaptcha_token_upload').value = token;
                        document.getElementById('upload-form').submit();
                    }
                });
            });
        }
    </script>
</body>
</html>
