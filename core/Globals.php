<?php

namespace AppSumo__PLG__Licensing;

// exit if file is called directly
if (!defined('ABSPATH')) {
    exit;
}

// if class already defined, bail out
if (class_exists('AppSumo__PLG__Licensing')) {
    return;
}

class Globals
{

    public static function get_appsumo__plg__parent_product_id()
    {
        // woo commerce product id. not  variation, but main product id.
        // return 18691; // accounts.wpmet.com
        return 1788;
    }

    public static function get_variation_id($tier_name)
    {
        // list out the woocommerce variation id's for appsumo.
        $variations = array(
            // test
            'appsumo__plg__tier1' => 708,
            'appsumo__plg__tier2' => 1755,
            'appsumo__plg__tier3' => 2380,
        );
        return isset($variations[$tier_name]) ? $variations[$tier_name] : 0;
    }

    public static function get_options($key, $default = '')
    {
        if (!$key) {
            return '';
        }

        return get_option($key, $default);
    }

    public static function get_appsumo__plg__redirect_link()
    {
        return home_url('/manage-sites/');
    }

    public static function get_jwt_auth_secret_key()
    {
        // defined in wp-config.php
        // generate from https://api.wordpress.org/secret-key/1.1/salt/
        if(defined('JWT_AUTH_SECRET_KEY')) {
            return JWT_AUTH_SECRET_KEY;
        }
        
        // fallback
        return 'jwt_auth_secret_key-ore-wa-oppai-daisuki-nandayo';
    }

    public static function get_product_slug()
    {
        return 'wpmet';
    }

    public static function get_product_name()
    {
        return 'Wpmet';
    }

    public static function get_request_key()
    {
        return 'wpmet_appsumo';
    }

    public static function get_request_value()
    {
        return '1';
    }
    public static function generate_username($email)
    {
        return sanitize_user($email);
    }
    public static function get_confirmation_email_body($email, $username, $password, $firstname)
    {
        return sprintf(
"<html><body style='padding:20px 15px 100px 15px; background: transparent;'>
<h2>Thanks for trusting Wpmet. We're really happy to have you on board.</h2>

<p>You can check your order details as well as the download links and license keys for the wpmet From here <a href='https://accounts.wpmet.com/'  target='_blank'>https://accounts.wpmet.com/</a></p>

<h3>For reference, here's your login information:</h3>
<p>
Login Page: <a href='https://accounts.wpmet.com/' target='_blank'>https://accounts.wpmet.com/</a> </br>
Login Email: %s </br>
Login Password: %s </br>
</p>
</body></html>
",$email, $password);
    }

}
