<?php

namespace Appsumo_PLG_Licensing\Callbacks;

if (!defined('ABSPATH')) exit();

use Appsumo_PLG_Licensing\Env;

/*
|--------------------------------------------------------------------------
| Class Init
|--------------------------------------------------------------------------
|
| This class initializes the callbacks for the Appsumo PLG Licensing plugin.
| It registers custom page templates, loads custom templates, creates custom
| pages, enqueues custom scripts, and handles actions for specific templates.
|
*/
class Init
{
    /**
     * Constructor to initialize the Init class.
     *
     * This constructor performs the following actions:
     * - Registers custom page templates.
     * - Loads custom templates.
     * - Creates custom pages.
     * - Enqueues custom scripts.
     * - Handles actions for specific templates.
     */
    public function __construct()
    {
        add_filter('theme_page_templates', [$this, 'register_custom_template']);
        add_filter('template_include', [$this, 'load_custom_template']);
        add_action('init', [$this, 'create_custom_page']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_custom_scripts']);
        add_action('template_redirect', [$this, 'action_handler']);
    }

    /**
     * Handle actions for specific templates.
     *
     * This method checks if the current page template is 'templates/_plg_signup.php'.
     * If it is, it creates a new instance of the ActionHandler class.
     *
     * @return void
     */
    public function action_handler()
    {
        if (is_page_template('templates/_plg_signup.php')) {
            new ActionHandler();
        }
    }

    /**
     * Enqueue custom styles and scripts.
     *
     * This method enqueues the custom styles and scripts for the 'templates/_plg_signup.php' template.
     *
     * @return void
     */
    public function enqueue_custom_scripts()
    {
        if (is_page_template('templates/_plg_signup.php')) {
            wp_enqueue_style('_plg_custom-template-style', Env::get('assets') . 'css/style.css');
            wp_enqueue_script('_plg_custom-template-script', Env::get('assets') . 'js/script.js', array('jquery'), '1.0', true);
        }
    }

    /**
     * Register custom page templates.
     *
     * This method registers the 'templates/_plg_signup.php' template with the name 'Signup Template'.
     *
     * @param array $templates The existing templates.
     * @return array The updated templates.
     */
    public function register_custom_template($templates)
    {
        $templates['templates/_plg_signup.php'] = 'Signup Template';
        return $templates;
    }

    /**
     * Load custom templates.
     *
     * This method loads the custom template for the 'templates/_plg_signup.php' template.
     *
     * @param string $template The existing template.
     * @return string The updated template.
     */
    public function load_custom_template($template)
    {
        if (is_page_template('templates/_plg_signup.php')) {
            $template = Env::get('templates') . 'signup.php';
        }
        return $template;
    }

    /**
     * Create custom pages.
     *
     * This method creates a custom page with the slug 'appsumo_plg_signup' if it does not already exist.
     * The page uses the 'templates/_plg_signup.php' template and has the title and content specified.
     *
     * @return void
     */
    public function create_custom_page()
    {
        // check if current url not wp-admin, then return
        if (is_admin()) {
            return;
        }
        // Define the page details
        $page_slug = 'appsumo_plg_signup';
        $page_title = Env::get('product_name') . ' Signup';
        $page_content = 'This is a custom template page for appsumo new user registration.';
        $page_template = 'templates/_plg_signup.php';

        // Check if the page already exists by slug
        $query = new \WP_Query(array(
            'post_type' => 'page',
            'post_status' => 'publish',
            'name' => $page_slug,
            'posts_per_page' => 1,
        ));

        if (!$query->have_posts()) {
            $new_page = array(
                'post_type' => 'page',
                'post_name' => $page_slug,
                'post_title' => $page_title,
                'post_content' => $page_content,
                'post_status' => 'publish',
                'post_author' => 1,
            );

            $new_page_id = wp_insert_post($new_page);
            if (!empty($page_template)) {
                update_post_meta($new_page_id, '_wp_page_template', $page_template);
            }
        }
    }
}