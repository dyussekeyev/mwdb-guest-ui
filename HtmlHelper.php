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
     * @return void
     */
    public static function renderError(string $message): void {
        echo "<p>$message</p>";
        echo '<a href="index.php" style="font-size:20px;">Go back</a>';
    }

    /**
     * Escape and return value from array
     * 
     * @param array $data Data array
     * @param string $key Key to retrieve
     * @param string $default Default value if key not found
     * @return string Escaped value
     */
    private static function escape(array $data, string $key, string $default = ''): string {
        return htmlspecialchars($data[$key] ?? $default);
    }

    /**
     * Render a table row
     * 
     * @param string $key Row label
     * @param string $value Row value (already escaped)
     * @return string Table row HTML
     */
    private static function tableRow(string $key, string $value): string {
        return "<tr><td>$key</td><td>$value</td></tr>";
    }

    /**
     * Render a file information table
     * 
     * @param array $file File data
     * @return void
     */
    public static function renderFileTable(array $file): void {
        $rows = [];
        
        $rows[] = self::tableRow('File name', self::escape($file, 'file_name'));
        
        if (!empty($file['alt_names'])) {
            foreach ($file['alt_names'] as $alt_name) {
                $rows[] = self::tableRow('Alt name', htmlspecialchars($alt_name));
            }
        }
        
        $hashFields = ['MD5' => 'md5', 'SHA1' => 'sha1', 'SHA256' => 'sha256', 
                       'SHA512' => 'sha512', 'CRC32' => 'crc32', 'ssdeep' => 'ssdeep'];
        
        foreach ($hashFields as $label => $key) {
            $rows[] = self::tableRow($label, self::escape($file, $key));
        }
        
        $rows[] = self::tableRow('File type', self::escape($file, 'file_type'));
        $rows[] = self::tableRow('File size', self::escape($file, 'file_size'));

        echo "<table border='1'>";
        echo "<thead><tr><th>Key</th><th>Value</th></tr></thead>";
        echo "<tbody>" . implode('', $rows) . "</tbody>";
        echo "</table>";
    }

    /**
     * Render comments table
     * 
     * @param array $comments Array of comments
     * @return void
     */
    public static function renderCommentsTable(array $comments): void {
        if (empty($comments)) {
            echo "<p>No comments found.</p>";
            return;
        }

        $rows = [];
        foreach ($comments as $comment) {
            $author = htmlspecialchars($comment['author']);
            $timestamp = htmlspecialchars($comment['timestamp']);
            $commentText = nl2br(htmlspecialchars($comment['comment']));
            $rows[] = "<tr><td>$author<br>$timestamp</td><td>$commentText</td></tr>";
        }

        echo "<table border='1' style='width:100%; margin-top:20px;'>";
        echo "<thead><tr><th style='width:25%;'>Author and Date</th><th style='width:75%;'>Comment</th></tr></thead>";
        echo "<tbody>" . implode('', $rows) . "</tbody>";
        echo "</table>";
    }

    /**
     * Render recent files table row
     * 
     * @param array $file File data
     * @return void
     */
    public static function renderRecentFileRow(array $file): void {
        $file_name = self::escape($file, 'file_name');
        if (strlen($file_name) > 60) {
            $file_name = substr($file_name, 0, 60) . '[...]';
        }

        $nameColumn = sprintf(
            "File Name: %s<br>MD5: %s<br>SHA1: %s<br>SHA256: %s",
            $file_name,
            self::escape($file, 'md5'),
            self::escape($file, 'sha1'),
            self::escape($file, 'sha256')
        );

        $typeColumn = sprintf(
            "File Type: %s<br>File Size: %s",
            self::escape($file, 'file_type'),
            self::escape($file, 'file_size')
        );

        $tags = [];
        if (!empty($file['tags'])) {
            foreach ($file['tags'] as $tag) {
                $tags[] = htmlspecialchars($tag['tag'] ?? '');
            }
        }
        $tagsColumn = implode('<br>', $tags);

        $upload_time = self::escape($file, 'upload_time');
        $formatted_time = $upload_time ? date("H:i:s d.m.Y", strtotime($upload_time)) : '';

        echo "<tr>";
        echo "<td>$nameColumn</td>";
        echo "<td class='table-column-type'>$typeColumn</td>";
        echo "<td class='table-column-tags'>$tagsColumn</td>";
        echo "<td>$formatted_time</td>";
        echo "</tr>";
    }
}
?>
