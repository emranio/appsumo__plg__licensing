<?php 
defined( 'ABSPATH' ) || exit;

/**
 * Plugin Name: EDD Appsumo Landing Page
 * Description: Landing page and related API workloads for appsumo redeem landing page
 * Plugin URI: #
 * Author: Emran
 * Version: 2.0.0
 * Author URI: https://wpmet.com/
 *
 * Text Domain: appsumo-redeem
 * 
 */


final class Appsumo_Redeem{

	/**
	 * Plugin Version
	 *
	 * @since 1.0.0
	 * @var string The plugin version.
	 */
	static function version(){
		return '2.0.1';
	}

	/**
	 * Plugin url
	 *
	 * @since 1.0.0
	 * @var string plugins's root url.
	 */
	static function plugin_url(){
		return trailingslashit(plugin_dir_url( __FILE__ ));
	}

	/**
	 * Plugin dir
	 *
	 * @since 1.0.0
	 * @var string plugins's root directory.
	 */
	static function plugin_dir(){
		return trailingslashit(plugin_dir_path( __FILE__ ));
    }
	

	/**
	 * Plugin asset url.
	 *
	 * @since 1.0.0
	 * @var string plugins's asset url.
	 */
	static function asset_url(){
		return self::plugin_url() . 'assets/';
	}
	
	

	/**
	 * Campaign details for each products.
	 *
	 * @since 2.0.0
	 * @var array
	 */
	static function campaign_data($pid, $default = null){
		$data = (include self::plugin_dir() . 'campaign-data.php');
		// if(!is_numeric($pid)){ // may be prefix of a discount id, lets convert it.
		// 	$pid = (string) array_search($pid, array_column($data, 'redeem_code_prefix'));
		// }
		return (isset($data[$pid]) ? (object)($data[$pid]) : $default);
	}
	

    /**
     * Autoloader.
     *
     * ElementsKit autoloader loads all the classes needed to run the plugin.
     *
     * @since 1.0.0
     * @access private
     */
    private static function registrar_autoloader() {
        require_once self::plugin_dir() . '/autoloader.php';
        \Appsumo_Redeem\Autoloader::run();
    }

	/**
	 * Load Textdomain
	 *
	 * Load plugin localization files.
	 * Fired by `init` action hook.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function i18n() {
		load_plugin_textdomain( 'appsumo-redeem', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}

	static function page_slug($key = 'appsumo') {
		$list = [
			'appsumo' => 'appsumo',
			'test-landing-page' => 'my-page',
		];

		return $list[$key];
	}

    /**
	 * Initialize the plugin
	 *
	 * Checks for basic plugin requirements, if one check fail don't continue,
	 * if all check have passed include the plugin class.
	 *
	 * Fired by `plugins_loaded` action hook.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function init() {
        // check if edd is installed
        if( !class_exists( 'Easy_Digital_Downloads' ) ){
            // edd is not installed
            return;
        }

        // redeem form api
        new Appsumo_Redeem\Api\Redeem();

		add_action( 'init', [$this, 'rewrites_init'] );
		add_filter( 'query_vars', [$this, 'query_vars'] );
		add_filter( 'page_template', [$this, 'template_file'] );
	}
	
	public function rewrites_init(){
		
		add_rewrite_rule(
			\Appsumo_Redeem::page_slug('appsumo').'/([A-Za-z0-9]+)/?$',
			'index.php?pagename='.\Appsumo_Redeem::page_slug('appsumo').'&asr_download_id=$matches[1]',
			'top' );

		add_rewrite_rule(
			\Appsumo_Redeem::page_slug('appsumo').'/([A-Za-z0-9]+)/([A-Za-z0-9]+)?$',
			'index.php?pagename='.\Appsumo_Redeem::page_slug('appsumo').'&asr_download_id=$matches[1]&redeem_code=$matches[2]',
			'top' );

	}
	
	public function query_vars( $query_vars ){
		$query_vars[] = 'asr_download_id';
		$query_vars[] = 'redeem_code';
		return $query_vars;
	}

	function template_file( $page_template ){
		if ( is_page( \Appsumo_Redeem::page_slug('appsumo') ) ) {
			$page_template = dirname( __FILE__ ) . '/templates/'.\Appsumo_Redeem::page_slug('appsumo').'.php';
		}
		return $page_template;
	}

    /**
     * Instance.
     *
     * Ensures only one instance of the plugin class is loaded or can be loaded.
     *
     * @since 1.0.0
     * @access public
     * @static
     *
     * @return Plugin An instance of the class.
     */
    public static function instance() {
        if ( is_null( self::$instance ) ) {
            // Call the method for ElementsKit lite autoloader.
            self::registrar_autoloader();
            // Fire when ElementsKit instance.
            self::$instance = new self();

            // Load translation
            add_action( 'init', array( self::$instance, 'i18n' ) );
            // Init Plugin
            add_action( 'plugins_loaded', array( self::$instance, 'init' ), 1010 );
        }

        return self::$instance;
    }

    private static $instance;
}


// run the instance
Appsumo_Redeem::instance();