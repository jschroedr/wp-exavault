<?php

/**
 * PHP version 7.4
 * 
 * @category Integration
 * @package  WP_Exavault
 * @author   Jake Schroeder <jake_schroeder@outlook.com>
 * @license  GNU v3
 * @link     https://github.com/jschroedr/wp-exavault/
 * @since    1.0.0
 */

namespace wp_exavault_v2 {

    /**
     * Base API Client Class
     * 
     * PHP version 7.4
     * 
     * @category Integration
     * @package  WP_Exavault
     * @author   Jake Schroeder <jake_schroeder@outlook.com>
     * @license  GNU v3
     * @link     https://github.com/jschroedr/wp-exavault/
     * @since    1.0.0
     */
    class ApiClient
    {

        protected $url;
        protected $apiKey;
        protected $accessToken;
        protected $timeout;

        /**
         * Initialize the ApiClient instance.
         * 
         * @param string $url         the base url of the API
         * @param string $apiKey      the api key
         * @param string $accessToken the access token
         * @param int    $timeout     the http timeout to allow
         * 
         * @return ApiClient
         */
        public function __construct(
            string $url,
            string $apiKey,
            string $accessToken,
            int $timeout
        ) {
            $this->url = $url;
            $this->apiKey = $apiKey;
            $this->accessToken = $accessToken;
            $this->timeout = $timeout;
        }

        /**
         * Getter for the standard HTTP authentication headers
         * 
         * @return array authentication headers
         */
        protected function getHeaders(): array
        {
            return [
                "ev-api-key: {$this->apiKey}",
                "ev-access-token: {$this->accessToken}",
                'Content-Type: application/json'
            ];
        }

        /**
         * Helper function to construct the request url
         * 
         * @param $uri the uri of the resource object
         * 
         * @return string the full URL to request against
         */
        protected function constructUrl(string $uri): string
        {
            return "$this->url/$uri";
        }

        /**
         * Perform an HTTP Post request and get the result as an array
         * 
         * @param string $uri     the uri of the object resource
         * @param array  $payload the array to POST
         * 
         * @return array the response decoded as an array
         */
        public function post(string $uri, array $payload): array
        {

            $url = $this->constructUrl($uri);
            $ch = curl_init($url);

            $payload = json_encode($payload);
            $options = [
                CURLOPT_POSTFIELDS => $payload,
                CURLOPT_HTTPHEADER => $this->getHeaders(),
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => $this->timeout
            ];
            curl_setopt_array($ch, $options);

            $result = curl_exec($ch);
            $statusCode = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
            $time = curl_getinfo($ch, CURLINFO_TOTAL_TIME);
            curl_close($ch);

            return [
                'url' => $url,
                'result' => $result,
                'status_code' => $statusCode,
                'time' => $time,
                'payload' => json_decode($payload, true)
            ];
        }
    }
}
