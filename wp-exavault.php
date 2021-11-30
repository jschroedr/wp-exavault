<?php
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @since             1.0.0
 * @package           WP_Exavault
 *
 * @wordpress-plugin
 * Plugin Name:       WP Exavault
 * Description:       Basic Wordpress Exavault integration with file compression.
 * Version:           1.0.0
 * Author:            Jake Schroeder
 * Author URI:        
 */

function wp_exavault_autoload(string $name) {
    if (stripos($name, 'wp_exavault') !== false) {
        $folder = stripos($name, 'tests') !== false ? 'tests' : 'src';
        $path = __DIR__ . DIRECTORY_SEPARATOR . $folder . DIRECTORY_SEPARATOR . $name . '.php';
        if (file_exists($path) === true) {
            require_once $path;
        } else {
            $path = str_replace('\\', '/', $path);
            require_once $path;
        }
    }
}
spl_autoload_register('wp_exavault_autoload');


use wp_exavault_v2\Credential;
use wp_exavault_v2\RestClient;
use wp_exavault_conf\Menu;
use wp_exavault_conf\FieldGroup;
use wp_exavault_conf\Encryption;


function wp_exavault_v2_handle_encrypted_field($value, $post_id, $field, $original) 
{
    if (empty($value) === false && is_string($value) === true) {
        return Credential::encrypt($value);
    }
}


function wp_exavault_op_init() {

    $encryption = new Encryption(Credential::Version);
    $iv = $encryption->getIV();
    if (empty($iv) === true) {
        $encryption->setIV();
    }
    $key = $encryption->getKey();
    if (empty($key) === true) {
        $encryption->setKey();
    }

    
    if(function_exists('acf_add_options_sub_page') === true) {
        Menu::setup();
    }
    
    if (function_exists('acf_add_local_field_group') === true) 
    {
        FieldGroup::setup();
    }
    
    // V2 filters
    // api key
    add_filter('acf/update_value/key=field_60e65576ad559', 'wp_exavault_v2_handle_encrypted_field', 10, 4);
    // access token
    add_filter('acf/update_value/key=field_60e65587ad55a', 'wp_exavault_v2_handle_encrypted_field', 10, 4);

}
add_action('acf/init', 'wp_exavault_op_init');


function wp_exavault_register_v2_compress_resource() {
    $client = new RestClient();
    $client->register();
}
add_action('rest_api_init', 'wp_exavault_register_v2_compress_resource');
