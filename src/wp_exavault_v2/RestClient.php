<?php 


namespace wp_exavault_v2 {

    use WP_Error;
    use WP_REST_Request;
    use WP_REST_Response;

    class RestClient {

        public function register() : void 
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

        public function compressResource(WP_REST_Request $request) 
        {
            $credential = Credential::get();

            if (empty($credential) === true) 
            {
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

            if (empty($filePaths) === true)
            {
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