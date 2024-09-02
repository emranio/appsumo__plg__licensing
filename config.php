<?php

use Appsumo_PLG_Licensing\Util;

return [
    'version' => '1.0.0',
    'url' => plugin_dir_url(__FILE__),
    'path' => plugin_dir_path(__FILE__),
    'assets' => plugin_dir_url(__FILE__) . 'assets/',
    'templates' => plugin_dir_path(__FILE__) . 'templates/',
    'product_name' => 'Plg Pro',
    'dashboard_url' => 'https://#',
    'appsumo_openid_api' => 'https://appsumo.com/openid/',
    'Oauth_redirect_url' => Util::get_page_url_by_slug('appsumo_plg_signup'),
    'client_id' => '024136685551',
    'client_secret' => '',
    'private_key' => '',
    'product_id' => 253666,
    'tier_variations' => [
        '1' => 10,
        '2' => 11,
        '3' => 12,
    ],
];
