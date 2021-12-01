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

    use wp_exavault_conf\Utility;
    use wp_exavault_conf\Encryption;

    /**
     * API Credential Management Class
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
    class Credential
    {

        const VERSION = 'v2';

        const URL_FIELD = '_url';
        const KEY_FIELD = '_api_key';
        const TOKEN_FIELD = '_access_token';
        const TIMEOUT_FIELD = '_timeout_value';

        /**
         * Get the credentials in array format
         * 
         * @return array credentials with all necessary options menu data
         */
        public static function get(): array
        {

            // get the url filed
            $url = Utility::getField(self::VERSION . self::URL_FIELD);

            // get the api key, and decrypt it to plain text
            $apiKey = Utility::getField(self::VERSION . self::KEY_FIELD);

            // get the access token, and decrypt it to plain text 
            $accessToken = Utility::getField(self::VERSION . self::TOKEN_FIELD);

            // get the timeout setting
            $timeout = Utility::getField(self::VERSION . self::TIMEOUT_FIELD);

            // package up the credentials as an associative array and return
            return [
                'url' => $url,
                'apiKey' => $apiKey,
                'accessToken' => $accessToken,
                'timeout' => $timeout
            ];
        }
    }
}
