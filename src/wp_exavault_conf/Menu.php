<?php 
/**
 * PHP version 7.4
 * 
 * @category Configuration
 * @package  WP_Exavault
 * @author   Jake Schroeder <jake_schroeder@outlook.com>
 * @license  GNU v3
 * @link     https://github.com/jschroedr/wp-exavault/
 * @since    1.0.0
 */
namespace wp_exavault_conf {

    /**
     * WP Options Menu Class
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
    class Menu
    {

        /**
         * Setup the WP Options Menu using the ACF Pro
         * helper function.
         * 
         * @return void
         */
        public static function setup() : void
        {
            $acfFunction = 'acf_add_options_sub_page';
            if (function_exists($acfFunction)) {
                $acfFunction(
                    [
                        'page_title' => __('WP Exavault'),
                        'menu_title' => __('WP Exavault'),
                        'parent_slug' => __('options-general.php')
                    ]
                );    
            }
        }
    }
}