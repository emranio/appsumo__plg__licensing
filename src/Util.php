<?php 
namespace Appsumo_PLG_Licensing;

class Util {
    public static function add_template_data($key, $data)
    {
        global $__plg__signup_template_data;
        $__plg__signup_template_data[$key] = $data;
    }

    public static function add_message($message, $type = 'success')
    {
        global $__plg__signup_message;
        $__plg__signup_message[] = [
            'message' => $message,
            'type' => $type,
        ];
    }

    public static function get_messages()
    {
        global $__plg__signup_message;
        return $__plg__signup_message;
    }

    public static function get_template_data($key)
    {
        global $__plg__signup_template_data;
        return $__plg__signup_template_data[$key] ?? null;
    }
}