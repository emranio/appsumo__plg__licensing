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
    'Oauth_redirect_url' => Util::get_page_url_by_slug('appsumo__gutenkit__signup'),
    'client_id' => '024136685551',
    'client_secret' => 'c51c57e4f7afcb7709eef6a8ef84157ce63934a303d8ac7e2254d0c0',
    'tier_variations' => [
        'tier1' => 1,
        'tier2' => 2,
        'tier3' => 3,
    ],
];