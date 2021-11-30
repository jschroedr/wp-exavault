<?php

namespace wp_exavault_v2 {

    class ApiClient {

        protected $url;
        protected $apiKey;
        protected $accessToken;
        protected $timeout;

        public function __construct(
            string $url,
            string $apiKey, 
            string $accessToken,
            int $timeout
        )
        {
            $this->url = $url;
            $this->apiKey = $apiKey;
            $this->accessToken = $accessToken;
            $this->timeout = $timeout;
        }

        protected function getHeaders() : array
        {
            return [
                "ev-api-key: {$this->apiKey}",
                "ev-access-token: {$this->accessToken}",
                'Content-Type: application/json'
            ];
        }

        protected function constructUrl(string $uri) : string 
        {
            return "$this->url/$uri";
        }

        public function post(string $uri, array $payload) : array 
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