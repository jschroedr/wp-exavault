<?php
/**
 * WP Exavault
 * 
 * Integrate your Wordpress website with Exavault API services.
 * 
 * PHP version 7.4
 * 
 * @category Integration
 * @package  WP_Exavault
 * @author   Jake Schroeder <jake_schroeder@outlook.com>
 * @license  GNU v3
 * @link     https://github.com/jschroedr/wp-exavault/
 * @since    1.0.0
 * 
 * @wordpress-plugin
 * Plugin Name:       WP Exavault
 * Description:       Wordpress with Exavault file management services.
 * Version:           1.1.0
 * Author:            Jake Schroeder
 * Author URI:        https://github.com/jschroedr/wp-exavault/
 */

 /**
  * Autoloader for the WP Exavault plugin 
  *
  * @param $name the name of the class to load
  *
  * @return void
  */
function Wp_Exavault_autoload(string $name) : void 
{
    if (stripos($name, 'wp_exavault') !== false) {
        // if the class name has "test" in it
        // assume phpunit context and aim for the tests directory
        $folder = stripos($name, 'tests') !== false ? 'tests' : 'src';
        
        // construct the file path
        $path = __DIR__ . 
            DIRECTORY_SEPARATOR . $folder . 
            DIRECTORY_SEPARATOR . $name . '.php';
        if (file_exists($path) === true) {
            include_once $path;
        } else {
            $path = str_replace('\\', '/', $path);
            include_once $path;
        }
    }
}
spl_autoload_register('Wp_Exavault_autoload');


use wp_exavault_v2\RestClient;
use wp_exavault_conf\Menu;
use wp_exavault_conf\FieldGroup;


/**
 * Initialize the ACF options field.
 * NOTE: this plugin requires ACF Pro in order to work.
 * 
 * @return void
 */
function Wp_Exavault_init() : void
{

    $requiredFunctions = [
        'acf_add_options_sub_page',
        'acf_add_local_field_group'
    ];

    foreach ($requiredFunctions as $function) {
        if (function_exists($function) === false) {
            error_log(
                "Wp_Exavault: Required function $function does not exist. " .
                "Options menu will not be available."
            );
            return;
        }
    }

    // if we made it this far - ACF Pro support is available
    // so we can setup our options page in confidence
    Menu::setup();
    FieldGroup::setup();

}
add_action('acf/init', 'Wp_Exavault_init');

/**
 * Register each of the WP Exavault WP Rest APIs
 * 
 * @return void
 */
function Wp_Exavault_register() : void
{
    $client = new RestClient();
    $client->register();
}
add_action('rest_api_init', 'Wp_Exavault_register');
