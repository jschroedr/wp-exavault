<?php

namespace wp_exavault_conf {

    use Exception;
    use openssl_encrypt;
    use openssl_decrypt;
    use openssl_cipher_iv_length;
    use openssl_random_pseudo_bytes;

    class Encryption {

        protected const Method = 'aes-256-cbc';

        public $version;

        public function __construct(string $version)
        {
            $this->version = $version;
        }

        private function getIVField() : string 
        {
            return $this->version . '_iv';
        }

        private function getKeyField() : string 
        {
            return $this->version . '_key';
        }

        public function setIV() : string 
        {
            $len = openssl_cipher_iv_length(self::Method);
            $iv = '';
            while(true) {
                $iv = $iv . uniqid();
                if (strlen($iv) > $len) {
                    $iv = substr($iv, 0, $len);
                    break;
                }
            }
            $field = $this->getIVField();
            $result = Utility::setField(
                $field,
                $iv
            );
            if ($result === false) {
                throw new Exception("Unable to update field $field with value");
            }
            return $iv;
        }

        public function getIV() : string
        {
            $iv = Utility::getField($this->getIVField());
            if (empty($iv) === true) {
                return $this->setIV();
            }
            return $iv;
        }

        public function setKey() : string
        {
            $key = openssl_pkey_new();
            openssl_pkey_export($key, $out, '');
            $out = str_replace('-----BEGIN ENCRYPTED PRIVATE KEY-----', '', $out);
            $out = str_replace('-----END ENCRYPTED PRIVATE KEY-----', '', $out);
            $out = trim($out);
            Utility::setField($this->getKeyField(), $out);
            return $out;
        }

        public function getKey() : string
        {
            $key = Utility::getField($this->getKeyField());
            if (empty($key) === true) {
                return $this->setKey();
            }
            return $key;
        }

        public function encrypt(string $value) : string 
        {
            $key = $this->getKey();
            $iv = $this->getIV();
            return openssl_encrypt($value, self::Method, $key, 0, $iv);
        }

        public function decrypt(string $value) : string
        {
            $key = $this->getKey();
            $iv = $this->getIV();
            return openssl_decrypt($value, self::Method, $key, 0, $iv);
        }

    }

}