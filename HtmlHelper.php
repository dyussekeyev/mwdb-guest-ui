<?php
/**
 * HTML Helper
 * Common HTML rendering functions
 */
class HtmlHelper {
    /**
     * Render error message with back link
     * 
     * @param string $message Error message
     */
    public static function renderError($message) {
        echo "<p>$message</p>";
        echo '<a href="index.php" style="font-size:20px;">Go back</a>';
    }

    /**
     * Render a file information table
     * 
     * @param array $file File data
     */
    public static function renderFileTable($file) {
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

    /**
     * Render comments table
     * 
     * @param array $comments Array of comments
     */
    public static function renderCommentsTable($comments) {
        if (empty($comments)) {
            echo "<p>No comments found.</p>";
            return;
        }

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
    }

    /**
     * Render recent files table row
     * 
     * @param array $file File data
     */
    public static function renderRecentFileRow($file) {
        echo "<tr>";
        echo "<td>";
        $file_name = htmlspecialchars($file['file_name'] ?? '');
        if (strlen($file_name) > 60) {
            $file_name = substr($file_name, 0, 60) . '[...]';
        }
        echo "File Name: " . $file_name . "<br>";
        echo "MD5: " . htmlspecialchars($file['md5'] ?? '') . "<br>";
        echo "SHA1: " . htmlspecialchars($file['sha1'] ?? '') . "<br>";
        echo "SHA256: " . htmlspecialchars($file['sha256'] ?? '');
        echo "</td>";
        echo "<td class='table-column-type'>";
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
        $upload_time = htmlspecialchars($file['upload_time'] ?? '');
        $formatted_time = date("H:i:s d.m.Y", strtotime($upload_time));
        echo "<td>" . $formatted_time . "</td>";
        echo "</tr>";
    }
}
?>
