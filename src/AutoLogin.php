<?php 
namespace Appsumo_PLG_Licensing;

if (!defined('ABSPATH')) exit();

/*
|--------------------------------------------------------------------------
| Class AutoLogin
|--------------------------------------------------------------------------
|
| This class handles the automatic login functionality for the Appsumo PLG 
| Licensing plugin. It provides a method to automatically log in a user 
| (default is 'admin') if no user is currently logged in.
|
*/
class AutoLogin
{
    /**
     * Constructor to initialize the AutoLogin class.
     *
     * This constructor can be used to call the auto_login method automatically
     * when an instance of the class is created. Currently, the auto_login method
     * call is commented out.
     */
    public function __construct()
    {
        // Uncomment the following line to enable auto login when the class is instantiated
        // $this->auto_login();
    }

    /**
     * Automatically log in a user if no user is currently logged in.
     *
     * This method checks if a user is logged in. If not, it retrieves the user
     * with the login name 'admin', sets the current user to this user, sets the
     * authentication cookie, and triggers the 'wp_login' action.
     *
     * @return void
     */
    public function auto_login()
    {
        // Check if no user is currently logged in
        if (!is_user_logged_in()) {
            // Retrieve the user with the login name 'admin'
            $user = get_user_by('login', 'admin');
            // Set the current user to the retrieved user
            wp_set_current_user($user->ID, $user->user_login);
            // Set the authentication cookie for the retrieved user
            wp_set_auth_cookie($user->ID);
            // Trigger the 'wp_login' action for the retrieved user
            do_action('wp_login', $user->user_login);
        }
    }
}