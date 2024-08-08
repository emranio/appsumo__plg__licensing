<?php

namespace Appsumo_PLG_Licensing\Callbacks;

use Appsumo_PLG_Licensing\Env;
use Appsumo_PLG_Licensing\LicenseModel;

class Init
{
    public function __construct()
    {
        add_filter('theme_page_templates', [$this, 'register_custom_template']);
        add_filter('template_include', [$this, 'load_custom_template']);
        add_action('init', [$this, 'create_custom_page']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_custom_scripts']);
        add_action('template_redirect', [$this, 'action_handler']);
    }

    public function action_handler()
    {
        if (is_page_template('templates/__plg__signup.php')) {
            new ActionHandler();
        }
    }

    // enqueue styles and scripts
    function enqueue_custom_scripts()
    {
        if (is_page_template('templates/__plg__signup.php')) {
            wp_enqueue_style('__plg__custom-template-style', Env::get('assets') . 'css/style.css');
            wp_enqueue_script('__plg__custom-template-script', Env::get('assets') . 'js/script.js', array('jquery'), '1.0', true);
        }
    }


    public function register_custom_template($templates)
    {
        $templates['templates/__plg__signup.php'] = 'Signup Template';
        return $templates;
    }

    public function load_custom_template($template)
    {
        if (is_page_template('templates/__plg__signup.php')) {
            $template = Env::get('templates') . 'signup.php';
        }
        return $template;
    }

    public function create_custom_page()
    {
        $page_slug = 'appsumo__plg__signup';
        $page_title = Env::get('product_name') . ' Signup';
        $page_content = 'This is a custom template page for appsumo new user registration.';
        $page_template = 'templates/__plg__signup.php';

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
