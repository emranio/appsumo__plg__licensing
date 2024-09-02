<?php

// Import the Util class from the Appsumo_PLG_Licensing namespace
use Appsumo_PLG_Licensing\Util;

if (!defined('ABSPATH')) exit();

/*
|--------------------------------------------------------------------------
| Configuration Array
|--------------------------------------------------------------------------
|
| This array contains configuration settings for the Appsumo PLG Licensing plugin.
| These settings include version, URLs, paths, product details, and API credentials.
|
*/

return [
    // The version of the plugin
    'version' => '1.0.0',

    // The URL of the plugin directory
    'url' => plugin_dir_url(__FILE__),

    // The path of the plugin directory
    'path' => plugin_dir_path(__FILE__),

    // The URL of the assets directory within the plugin
    'assets' => plugin_dir_url(__FILE__) . 'assets/',

    // The path of the templates directory within the plugin
    'templates' => plugin_dir_path(__FILE__) . 'templates/',

    // The name of the product
    'product_name' => 'Plg Pro',

    // The URL of the dashboard
    'dashboard_url' => 'https://#',

    // The API endpoint for Appsumo OpenID
    'appsumo_openid_api' => 'https://appsumo.com/openid/',

    // The OAuth redirect URL, dynamically generated using a utility function
    'Oauth_redirect_url' => Util::get_page_url_by_slug('appsumo_plg_signup'),

    // The client ID for OAuth authentication
    'client_id' => '024136685551',

    // The client secret for OAuth authentication (empty by default)
    'client_secret' => '',

    // The private key for OAuth authentication (empty by default)
    'private_key' => '',

    // The product ID
    'product_id' => 253666,

    // An array mapping tier levels to variation IDs
    'tier_variations' => [
        '1' => 10, // Tier 1 maps to variation ID 10
        '2' => 11, // Tier 2 maps to variation ID 11
        '3' => 12, // Tier 3 maps to variation ID 12
    ],
];