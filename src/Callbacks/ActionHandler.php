<?php

namespace Appsumo_PLG_Licensing\Callbacks;

use Appsumo_PLG_Licensing\Env;
use Appsumo_PLG_Licensing\LicenseModel;
use Appsumo_PLG_Licensing\Util;

class ActionHandler
{

    public function __construct()
    {
        $this->process__code();
    }

    public function process__code()
    {
        if (empty($_GET['code'])) return;

        $data = array(
            'client_id' => '1234567890',
            'client_secret' => '1234567890abcdef1234567890abcdef',
            'code' => 'fedcba0987654321fedcba0987654321',
            'redirect_uri' => 'https://your-url.com/',
            'grant_type' => 'authorization_code',
        );
        $headers = array('Content-Type' => 'application/json');
        
        $args = array(
            'body'    => wp_json_encode($data),
            'headers' => $headers,
            'method'  => 'POST',
        );
        
        $response = wp_remote_post(Env::get('appsumo_token_api'), $args);
        
        if (is_wp_error($response)) {
            $error_message = $response->get_error_message();
            Util::add_message("Something went wrong: $error_message", 'error');
        } else {
            $response_body = wp_remote_retrieve_body($response);
            // Process the response as needed

            // {
            //     "access_token": "82b35f3d810f4cf49dd7a52d4b22a594",
            //     "token_type": "bearer",
            //     "expires_in": 3600,
            //     "refresh_token": "0bac2d80d75d46658b0b31d3778039bb",
            //     "id_token": "eyJhbGciOiJSUzI1NiIsImtpZCI6...",
            //     "error": ""
            // }
        }
    }
}
