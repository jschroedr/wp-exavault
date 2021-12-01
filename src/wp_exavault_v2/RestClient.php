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

    use WP_Error;
    use WP_REST_Request;
    use WP_REST_Response;

    /**
     * WP Rest API Class
     * 
     * Manages all public WP Rest endpoints for WP Exavault
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
    class RestClient
    {

        /**
         * Setup the available WP Rest endpoints
         * 
         * @return void
         */
        public function register(): void
        {
            register_rest_route(
                'exavault/v2',
                '/resource/compress',
                [
                    'methods' => 'POST',
                    'callback' => [$this, 'compressResource']
                ]
            );
        }

        /**
         * Expose the Resource Compress API action using WP Rest
         * 
         * @param WP_REST_Request $request the inbound request object
         * 
         * @return WP_REST_Response
         */
        public function compressResource(
            WP_REST_Request $request
        ) : WP_REST_Response {

            // get the credentials from the menu
            $credential = Credential::get();

            if (empty($credential) === true) {
                return new WP_Error(
                    'Credential Failure',
                    'Unable to get service credentials',
                    [
                        'status' => 500
                    ]
                );
            }

            // get the file paths from the request
            $filePaths = $request['filePaths'] ?? [];
            $parentResource = $request['parentResource'] ?? '';
            // name of zip archive to create, if left blank current date will be used
            $archiveName =  $request['archiveName'] ?? '';

            if (empty($filePaths) === true) {
                return new WP_Error(
                    "Missing required argument 'filePaths'",
                    [
                        'status' => 400
                    ]
                );
            }
            // skip the login step and head straight to the new upload class
            $resources = new Resources(
                $credential['url'],
                $credential['apiKey'],
                $credential['accessToken'],
                $credential['timeout']
            );
            $data = $resources->compress($filePaths, $parentResource, $archiveName);

            // include the api response in the response back to the client
            $response = new WP_REST_Response($data);
            $response->set_status(200);
            return $response;
        }
    }
}
