<?php

namespace Appsumo_PLG_Licensing\Callbacks;

if (!defined('ABSPATH')) exit();

use Appsumo_PLG_Licensing\EDD;
use Appsumo_PLG_Licensing\Env;
use Appsumo_PLG_Licensing\LicenseModel;
use Appsumo_PLG_Licensing\Util;

/*
|--------------------------------------------------------------------------
| Class ActionHandler
|--------------------------------------------------------------------------
|
| This class handles the actions related to the Appsumo PLG Licensing plugin.
| It processes the access code, retrieves the access token, and manages the
| user authentication and license assignment.
|
*/
class ActionHandler
{
    // The access token retrieved from the cookie or API
    private $access_token = null;

    // The access code retrieved from the cookie or API
    private $access_code = null;

    /**
     * Constructor to initialize the ActionHandler class.
     *
     * This constructor retrieves the access token and access code from cookies
     * and processes the access code.
     */
    public function __construct()
    {
        $this->access_token = $_COOKIE['_plg_token'] ?? null;
        $this->access_code = $_COOKIE['_plg_code'] ?? null;
        
        // \write_log('token_test', $this->access_token, $this->access_code);
        
        $this->process__code();
    }

    /**
     * Process the access code to retrieve the license key and manage user authentication.
     *
     * This method retrieves the license key using the access token, checks the license status,
     * and manages user authentication and license assignment.
     *
     * @return void
     */
    public function process__code()
    {
        if ($this->access_token == null) {
            $this->get_access_token();
        }

        $args = array(
            'method' => 'GET',
        );

        $response = wp_remote_get(Env::get('appsumo_openid_api') . 'license_key/?access_token=' . $this->access_token, $args);

        if (is_wp_error($response)) {
            $error_message = $response->get_error_message();
            Util::add_message("Something went wrong: $error_message");
            return;
        }
        $response_body = wp_remote_retrieve_body($response);
        $response_data = json_decode($response_body, true);

        \write_log('response_data', $response_data);

        $license_key = $response_data['license_key'] ?? null;
        if (!$license_key) {
            Util::add_message("Something went wrong: License key not found", 'error');
            return;
        }

        $license = LicenseModel::where('license_key', $license_key)->first();

        if (!$license || !\in_array($license->license_status, ['active', 'deactivated'])) {
            Util::add_message("License key is invalid or inactive", 'error');
            return;
        }

        // if license is already belongs to a user
        if ($license->user_id) {
            // login to that user

            wp_set_current_user($license->user_id);
            wp_set_auth_cookie($license->user_id);

            // redirect to the dashboard
            wp_redirect(Env::get('dashboard_url'));
            return;
        }

        // check if the user is not exists create a new user with $_POST firstname, lastname, email, password
        $user = get_user_by('email', $_POST['email'] ?? '');
        if (!$user) {
            // check if the required fields are not empty
            if (empty($_POST['email']) || empty($_POST['password'])) {
                // Util::add_message("Email and password are required", 'error');
                return;
            }

            $user_id = wp_create_user($_POST['email'], $_POST['password'], $_POST['email']);
            if (is_wp_error($user_id)) {
                Util::add_message("User creation failed: " . $user_id->get_error_message(), 'error');
                return;
            }
            $user = get_user_by('id', $user_id);

            // Update user's first name and last name
            update_user_meta($user_id, 'first_name', $_POST['first_name'] ?? '');
            update_user_meta($user_id, 'last_name', $_POST['last_name'] ?? '');
            
            // Optionally, update the display name
            wp_update_user([
                'ID'           => $user_id,
                'display_name' => $_POST['first_name'] ?? '' . ' ' . $_POST['last_name'] ?? ''
            ]);
            
        } else {
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

        // purchase the product
        $payment_id = (new EDD($license, $user))->purchase();

        // attach the license to the current user
        LicenseModel::where('id', $license->id)->update([
            'user_id' => get_current_user_id(),
            'payment_id' => $payment_id,
        ]);

        // redirect to the dashboard
        wp_redirect(Env::get('dashboard_url'));
    }

    /**
     * Retrieve the access token using the authorization code.
     *
     * This method sends a POST request to the Appsumo OpenID API to retrieve the access token
     * using the authorization code. The access token is then stored in a cookie.
     *
     * @return void
     */
    public function get_access_token()
    {

        if (empty($_GET['code'])) {
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

        // set the response in a cookie
        setcookie('_plg_token', $access_token, time() + 600, "/"); // 600 seconds = 10 minutes
        $this->access_token = $access_token;

        setcookie('_plg_code', $_GET['code'], time() + 600, "/"); // 600 seconds = 10 minutes
        $this->access_code = $_GET['code'];
    }
}