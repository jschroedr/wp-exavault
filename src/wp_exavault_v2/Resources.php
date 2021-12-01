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
     * Resources API Class
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
    class Resources extends ApiClient
    {

        /**
         * Execute the Compress Resource API Call
         * 
         * @param array  $resources      the files in Exavault to compress
         * @param string $parentResource the name of the parent folder
         * @param string $archiveName    the name of the zip to create
         * 
         * @return array the response
         */
        public function compress(
            array $resources,
            string $parentResource,
            string $archiveName
        ): array {
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
