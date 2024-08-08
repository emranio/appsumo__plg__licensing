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
        if (empty($_GET['code'])){
            Util::add_message("Code not found", 'error');
            return;
        }

        $data = array(
            'client_id' => Env::get('client_id'),
            'client_secret' => Env::get('client_secret'),
            'code' => $_GET['code'],
            'redirect_uri' => Env::get('Oauth_redirect_url'),
            'grant_type' => 'authorization_code',
        );
        $headers = array('Content-Type' => 'application/json');
        
        $args = array(
            'body'    => wp_json_encode($data),
            'headers' => $headers,
            'method'  => 'POST',
        );
        
        $response = wp_remote_post(Env::get('appsumo_openid_api') . 'token/', $args);
        
        if (is_wp_error($response)) {
            $error_message = $response->get_error_message();
            Util::add_message("Something went wrong: $error_message", 'error');

            return;
        }
        $response_body = wp_remote_retrieve_body($response);
        $response_data = json_decode($response_body, true);
        $access_token = $response_data['access_token'] ?? null;
        if (!$access_token) {
            Util::add_message("Something went wrong: Access token not found", 'error');
            return;
        }

        $args = array(
            'method' => 'GET',
        );
        
        $response = wp_remote_get(Env::get('appsumo_openid_api') . 'license_key/?access_token=' . $access_token, $args);
        
        if (is_wp_error($response)) {
            $error_message = $response->get_error_message();
            Util::add_message("Something went wrong: $error_message");
            return;
        } 
        $response_body = wp_remote_retrieve_body($response);
        $response_data = json_decode($response_body, true);
        $license_key = $response_data['license_key'] ?? null;
        if (!$license_key) {
            Util::add_message("Something went wrong: License key not found", 'error');
            return;
        }

        $license = LicenseModel::where('license_key', $license_key)->first();
        
        if (!$license || $license->status !== 'inactive') {
            Util::add_message("License key is invalid or inactive", 'error');
            return;
        }

        // if license is already belongs to a user
        if ($license->user_id) {
            Util::add_message("License key is already used by another user", 'error');
            return;
        }

        // check if the user is not exists create a new user with $_POST firstname, lastname, email, password
        $user = get_user_by('email', $_POST['email'] ?? '');
        if (!$user) {
            // check if the required fields are not empty
            if (empty($_POST['email']) || empty($_POST['password'])) {
                Util::add_message("Email and password are required", 'error');
                return;
            }

            $user_id = wp_create_user($_POST['email'], $_POST['password'], $_POST['email']);
            if (is_wp_error($user_id)) {
                Util::add_message("User creation failed: " . $user_id->get_error_message(), 'error');
                return;
            }
            $user = get_user_by('id', $user_id);
        }else{
            // authenticate the user with $_POST email and password
            $user = wp_authenticate($_POST['email'], $_POST['password']);
            if (is_wp_error($user)) {
                Util::add_message("User already exists with this email but authentication failed: " . $user->get_error_message(), 'error');
                return;
            }
        }

        // login the user and set as current user
        wp_set_current_user($user->ID);
        wp_set_auth_cookie($user->ID);

        // attach the license to the current user
        $license->user_id = get_current_user_id();
        $license->status = 'active';
        $license->save();

    }
}
