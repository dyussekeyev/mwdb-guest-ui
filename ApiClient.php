<?php
/**
 * API Client for MWDB interactions
 * Handles all CURL requests to the MWDB API
 */
class ApiClient {
    private $api_url;
    private $api_key;
    private $timeout;
    private $connect_timeout;

    public function __construct($config) {
        $this->api_url = $config['api_url'];
        $this->api_key = $config['api_key'];
        $this->timeout = 30;
        $this->connect_timeout = 10;
    }

    /**
     * Execute a CURL request with common configuration
     * 
     * @param resource $ch CURL handle
     * @param int $timeout Request timeout in seconds
     * @return array Response data with 'success', 'data', 'http_code', and 'error' keys
     */
    private function executeCurlRequest($ch, int $timeout): array {
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->connect_timeout);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

        $response = curl_exec($ch);
        $curl_error = curl_errno($ch);
        $curl_error_msg = curl_error($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return $this->processResponse($response, $curl_error, $curl_error_msg, $http_code);
    }

    /**
     * Process CURL response
     * 
     * @param string|false $response Response body
     * @param int $curl_error CURL error number
     * @param string $curl_error_msg CURL error message
     * @param int $http_code HTTP status code
     * @return array Response data with 'success', 'data', 'http_code', and 'error' keys
     */
    private function processResponse($response, int $curl_error, string $curl_error_msg, int $http_code): array {
        if ($curl_error) {
            error_log('CURL error: ' . $curl_error_msg);
            return [
                'success' => false,
                'error' => 'CURL error: ' . $curl_error_msg,
                'http_code' => 0
            ];
        }

        if ($http_code < 200 || $http_code >= 300) {
            return [
                'success' => false,
                'error' => "HTTP error code: $http_code",
                'http_code' => $http_code
            ];
        }

        $data = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log('JSON decode error: ' . json_last_error_msg());
            return [
                'success' => false,
                'error' => 'JSON decode error',
                'http_code' => $http_code
            ];
        }

        return [
            'success' => true,
            'data' => $data,
            'http_code' => $http_code
        ];
    }

    /**
     * Performs a GET request to the API
     * 
     * @param string $endpoint API endpoint
     * @return array Response data with 'success', 'data', 'http_code', and 'error' keys
     */
    public function get(string $endpoint): array {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "{$this->api_url}/{$endpoint}");
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "accept: application/json",
            "Authorization: Bearer {$this->api_key}"
        ]);

        return $this->executeCurlRequest($ch, $this->timeout);
    }

    /**
     * Uploads a file to the API
     * 
     * @param string $file_path Path to file
     * @param string $file_name Original filename
     * @param string $file_type MIME type
     * @return array Response data with 'success', 'data', 'http_code', and 'error' keys
     */
    public function uploadFile(string $file_path, string $file_name, string $file_type): array {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "{$this->api_url}/file");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "accept: application/json",
            "Authorization: Bearer {$this->api_key}",
            "Content-Type: multipart/form-data"
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, [
            'file' => new CURLFile($file_path, $file_type, $file_name)
        ]);

        return $this->executeCurlRequest($ch, 60); // Longer timeout for uploads
    }

    /**
     * Get file information by hash
     * 
     * @param string $hash File hash
     * @return array Response data
     */
    public function getFile(string $hash): array {
        return $this->get("file/$hash");
    }

    /**
     * Get recent files
     * 
     * @param int $count Number of files to retrieve
     * @return array Response data
     */
    public function getRecentFiles(int $count = 10): array {
        return $this->get("file?count=$count");
    }

    /**
     * Get comments for a file
     * 
     * @param string $hash File hash
     * @return array Response data
     */
    public function getFileComments(string $hash): array {
        return $this->get("file/$hash/comment");
    }
}
?>
