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
     * ACF Field Group Class
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
    class FieldGroup 
    {
        
        /**
         * Setup the ACF Field Group
         * 
         * @return void
         */
        public static function setup() : void
        {
            $acfFunction = 'acf_add_local_field_group';
            if (function_exists($acfFunction)) {
                $acfFunction(self::FIELDS);
            }
        }

        const FIELDS = [
            'key' => 'group_60e6550e8ccfc',
            'title' => 'WP Exavault',
            'fields' => [
                [
                    'key' => 'field_60e65516ad557',
                    'label' => 'V2',
                    'name' => '',
                    'type' => 'tab',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                      'width' => '',
                      'class' => '',
                      'id' => '',
                    ),
                    'placement' => 'left',
                    'endpoint' => 0,   
                ],
                [
                    'key' => 'field_60e655d20209e',
                    'label' => 'URL',
                    'name' => 'wp_exavault_v2_url',
                    'type' => 'text',
                    'instructions' => 'This is the base url to make api '. 
                        'requests to: https://{account_name}.exavault.com',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                    'width' => '',
                    'class' => '',
                    'id' => '',
                    ),
                    'default_value' => '',
                    'placeholder' => '',
                    'prepend' => '',
                    'append' => '',
                    'maxlength' => '',
                ],
                [
                    'key' => 'field_60e6564f0209f',
                    'label' => 'Timeout Value',
                    'name' => 'wp_exavault_v2_timeout_value',
                    'type' => 'number',
                    'instructions' => 'How long should API requests ' .
                        'to the V2 API be allowed to take? ' . 
                        'Tweak based on your web host settings.',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                      'width' => '',
                      'class' => '',
                      'id' => '',
                    ),
                    'default_value' => '',
                    'placeholder' => '',
                    'prepend' => '',
                    'append' => '',
                    'min' => '',
                    'max' => '',
                    'step' => '',
                ],
                [
                    'key' => 'field_60e65576ad559',
                    'label' => 'Api Key',
                    'name' => 'wp_exavault_v2_api_key',
                    'type' => 'password',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                    'width' => '',
                    'class' => '',
                    'id' => '',
                    ),
                    'placeholder' => '',
                    'prepend' => '',
                    'append' => '',
                ],
                [
                    'key' => 'field_60e65587ad55a',
                    'label' => 'Access Token',
                    'name' => 'wp_exavault_v2_access_token',
                    'type' => 'password',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                    'width' => '',
                    'class' => '',
                    'id' => '',
                    ),
                    'placeholder' => '',
                    'prepend' => '',
                    'append' => '',                    
                ],
            ],
            // update location
            'location' => array(
                array(
                  array(
                    'param' => 'options_page',
                    'operator' => '==',
                    'value' => 'acf-options-wp-exavault',
                  ),
                ),
              ),
              'menu_order' => 0,
              'position' => 'normal',
              'style' => 'default',
              'label_placement' => 'top',
              'instruction_placement' => 'label',
              'hide_on_screen' => '',
              'active' => true,
              'description' => '',
        ];
    }
}