<?php

namespace AppSumo__PLG__Licensing;

class UserForm
{
    public function __construct()
    {
        add_action('appsumo__plg__footer_userform', [$this, 'render_the_form']);

        $this->save_the_form();
    }

    private function save_the_form()
    {
        if (!is_user_logged_in()) {
            return;
        }
        if (empty(isset($_POST['appsumo__plg__save_userform_nonce']))) {
            return;
        }
        if (!wp_verify_nonce($_POST['appsumo__plg__save_userform_nonce'], 'appsumo__plg__save_userform')) {
            return;
        }

        $firstname = sanitize_text_field($_POST['appsumo__plg__firstname']);
        $lastname = sanitize_text_field($_POST['appsumo__plg__lastname']);

        if (empty($firstname) || empty($lastname)) {
            return;
        }

        $user = wp_get_current_user();
        // update wp user's firstname and lastname
        wp_update_user([
            'ID' => $user->ID,
            'first_name' => $firstname,
            'last_name' => $lastname,
        ]);
    }

    public function render_the_form()
    {
        if (!is_user_logged_in()) {
            return;
        }
        $user = wp_get_current_user();

        // checking if it's an appsumo user.
        $is_appsumo__plg__user = get_user_meta($user->ID, 'is_appsumo__plg__user', true);
        if ($is_appsumo__plg__user !== 'yes') {
            return;
        }

        // returns if the form is already shown to the user.
        // remember, we will not show this form again, even if the user did not fill the form.
        $already_logged_in = get_user_meta($user->ID, 'appsumo__plg__already_logged_in', true);
        if ($already_logged_in === 'yes') {
            return;
        }

        $nonce = wp_create_nonce('appsumo__plg__save_userform'); // we will use this nonce to verify the form submission in userform.php.
        include \AppSumo_Licensing::get_plugin_dir() . 'view/userform.php';
        update_user_meta($user->ID, 'appsumo__plg__already_logged_in', 'yes');
    }
}