<?php 
namespace Appsumo_PLG_Licensing;

if (!defined('ABSPATH')) exit();

/*
|--------------------------------------------------------------------------
| Class Util
|--------------------------------------------------------------------------
|
| This class contains utility functions that are used throughout the 
| Appsumo PLG Licensing plugin. These functions include retrieving 
| variation IDs by tier, getting page URLs by slug, adding and retrieving 
| template data, and managing messages.
|
*/
class Util {

    /**
     * Retrieve the variation ID based on the tier level.
     *
     * @param int $tier The tier level.
     * @return int|null The variation ID corresponding to the tier, or null if not found.
     */
    public static function variation_id_by_tier($tier) {
        // Get the tier variations from the environment configuration
        $variations = Env::get('tier_variations');
        // Return the variation ID for the given tier, or null if not found
        return $variations[$tier] ?? null;
    }

    /**
     * Get the URL of a page by its slug.
     *
     * @param string $slug The slug of the page.
     * @return string|null The URL of the page, or null if the page is not found.
     */
    public static function get_page_url_by_slug($slug) {
        // Retrieve the page object by its slug
        $page = get_page_by_path($slug);
        // If the page exists, return its permalink URL
        if ($page) {
            return get_permalink($page->ID);
        }
        // Return null if the page is not found
        return null;
    }

    /**
     * Add data to the template context.
     *
     * @param string $key The key under which the data will be stored.
     * @param mixed $data The data to be stored.
     * @return void
     */
    public static function add_template_data($key, $data)
    {
        // Access the global template data array
        global $_plg_signup_template_data;
        // Add the data to the template context under the specified key
        $_plg_signup_template_data[$key] = $data;
    }

    /**
     * Add a message to the global message array.
     *
     * @param string $message The message text.
     * @param string $type The type of the message (e.g., 'success', 'error'). Default is 'success'.
     * @return void
     */
    public static function add_message($message, $type = 'success')
    {
        // Access the global message array
        global $_plg_signup_message;
        // Add the message to the global message array with its type
        $_plg_signup_message[] = [
            'message' => $message,
            'type' => $type,
        ];
    }

    /**
     * Retrieve all messages from the global message array.
     *
     * @return array The array of messages.
     */
    public static function get_messages()
    {
        // Access the global message array
        global $_plg_signup_message;
        // Return the array of messages
        return $_plg_signup_message;
    }

    /**
     * Retrieve template data by key.
     *
     * @param string $key The key of the data to retrieve.
     * @return mixed|null The data associated with the key, or null if not found.
     */
    public static function get_template_data($key)
    {
        // Access the global template data array
        global $_plg_signup_template_data;
        // Return the data associated with the key, or null if not found
        return $_plg_signup_template_data[$key] ?? null;
    }
}