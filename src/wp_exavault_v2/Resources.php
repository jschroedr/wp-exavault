<?php

namespace wp_exavault_v2 {

    class Resources extends ApiClient {

        public function compress(array $resources, string $parentResource, string $archiveName) : array 
        {
            $payload = [
                'resources' => $resources
            ];
            if (empty($parentResource) === false) {
                $payload['parentResource'] = $parentResource;
            }
            if (empty($archiveName) === false) {
                $payload['archiveName'] = $archiveName;
            }
            $uri = 'resources/compress';
            return $this->post($uri, $payload);
        }

    }

}