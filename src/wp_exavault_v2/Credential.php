<?php

namespace wp_exavault_v2 {

    use wp_exavault_conf\Utility;
    use wp_exavault_conf\Encryption;

    class Credential {

        const Version = 'v2';

        const UrlField = '_url';
        const KeyField = '_api_key';
        const TokenField = '_access_token';
        const TimeoutField = '_timeout_value';

        public static function getDecrypted(string $field) : string
        {
            $encrypted = Utility::getField($field);
            $encryption = new Encryption(self::Version);
            return $encryption->decrypt($encrypted);
        }
        
        public static function encrypt(string $value) : string
        {
            $encryption = new Encryption(self::Version);
            return $encryption->encrypt($value);
        }

        public static function get() : array 
        {
            
            // get the api key, and decrypt it to plain text
            $apiKey = self::getDecrypted(self::Version . self::KeyField);

            // get the access token, and decrypt it to plain text 
            $accessToken = self::getDecrypted(self::Version . self::TokenField);

            // get the timeout setting
            $timeout = 30;

            return [
                'url' => Utility::getField(self::Version . self::UrlField),
                'apiKey' => $apiKey,
                'accessToken' => $accessToken,
                'timeout' => Utility::getField(self::Version . self::TimeoutField)
            ];
        }

    }

}