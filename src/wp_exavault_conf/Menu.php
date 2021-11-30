<?php 

namespace wp_exavault_conf {

    class Menu {

        public static function setup() : void
        {
            acf_add_options_sub_page(
                [
                    'page_title' => __('WP Exavault'),
                    'menu_title' => __('WP Exavault'),
                    'parent_slug' => __('options-general.php')
                ]
            );
        }

    }

}