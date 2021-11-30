<?php

namespace wp_exavault_conf {

    class Utility {

        const Prefix = 'wp_exavault_';

        const PostId = 'options';

        public static function getField(string $field)
        {
            return get_field(self::Prefix . $field, self::PostId);
        }

        public static function setField(string $field, $value) : bool
        {
            return update_field(self::Prefix . $field, $value, self::PostId);
        }

    }

}