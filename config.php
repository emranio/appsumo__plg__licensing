<?php

use Appsumo_PLG_Licensing\Util;

return [
    'version' => '1.0.0',
    'url' => plugin_dir_url(__FILE__),
    'path' => plugin_dir_path(__FILE__),
    'assets' => plugin_dir_url(__FILE__) . 'assets/',
    'templates' => plugin_dir_path(__FILE__) . 'templates/',
    'product_id' => 0,
    'product_name' => 'My Product',
    'appsumo_openid_api' => 'https://appsumo.com/openid/',
    'Oauth_redirect_url' => Util::get_page_url_by_slug('appsumo__plg__signup'),
    'client_id' => '1234567890',
    'client_secret' => '1234567890abcdef1234567890abcdef',
    'tier_variations' => [
        'tier1' => 1,
        'tier2' => 2,
        'tier3' => 3,
    ],
];