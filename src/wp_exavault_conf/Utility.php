<?php
/**
 * PHP version 7.4
 * 
 * @category Configuration
 * @package  WP_Exavault
 * @author   Jake Schroeder <jake_schroeder@outlook.com>
 * @license  GNU v3
 * @link     https://github.com/jschroedr/wp-exavault/
 * @since    1.1.0
 */
namespace wp_exavault_conf {

    /**
     * Configuration (ACF) Utility Class
     * 
     * PHP version 7.4
     * 
     * @category Configuration
     * @package  WP_Exavault
     * @author   Jake Schroeder <jake_schroeder@outlook.com>
     * @license  GNU v3
     * @link     https://github.com/jschroedr/wp-exavault/
     * @since    1.0.0
     */
    class Utility
    {

        const PREFIX = 'wp_exavault_';

        const POST_ID = 'options';

        /**
         * Get the options page field
         * 
         * @param string $field the ACF field to get
         * 
         * @return mixed, false on failure
         */
        public static function getField(string $field)
        {
            $acfFunction = 'get_field';
            if (function_exists($acfFunction)) {
                return $acfFunction(self::PREFIX . $field, self::POST_ID);
            }
            return false;
        }

        /**
         * Set the options page field
         * 
         * @param string $field the ACF field to set
         * @param mixed  $value the ACF value to set
         * 
         * @return bool the operation outcome (true = success)
         */
        public static function setField(string $field, $value) : bool
        {
            $acfFunction = 'update_field';
            if (function_exists($acfFunction)) {
                return $acfFunction(self::PREFIX . $field, $value, self::POST_ID);
            }
            return false;
        }

    }

}