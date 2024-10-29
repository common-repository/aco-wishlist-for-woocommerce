<?php
/*
 * Plugin Name: Wishlist and Save for later for Woocommerce
 * Version: 1.1.17
 * Description: WooCommerce wishlist manager helps to manage wishlist in WooCommerce store
 * Author: Acowebs
 * Author URI: http://acowebs.com
 * Requires at least: 4.0
 * Tested up to: 6.6
 * Text Domain: aco-wishlist-for-woocommerce
 * WC requires at least: 4.0.0
 * WC tested up to: 9.1
 */

define('AWWLM_TOKEN', 'awwlm');
define('AWWLM_VERSION', '1.1.17');
define('AWWLM_FILE', __FILE__);
define('AWWLM_PLUGIN_NAME', 'Wishlist for WooCommerce');
define('AWWLM_TEXT_DOMAIN', 'aco-wishlist-for-woocommerce');
define('AWWLM_STORE_URL', 'https://api.acowebs.com');
define('AWWLM_POST_TYPE', 'awwlm_wishlist');
define('AWWLM_WISHLIST_TYPE', 'awwlm_wishlist_type');
define('AWWLM_WISHLIST_META_KEY', 'awwlm_wishlist_meta_key');
define('AWWLM_PLUGIN_PATH',  plugin_dir_path( __FILE__ ) );

require_once(realpath(plugin_dir_path(__FILE__)) . DIRECTORY_SEPARATOR . 'includes/helpers.php');

if (!function_exists('awwlm_init')) {

    function awwlm_init()
    {
        $plugin_rel_path = basename(dirname(__FILE__)) . '/languages'; /* Relative to WP_PLUGIN_DIR */
        load_plugin_textdomain('aco-wishlist-for-woocommerce', false, $plugin_rel_path);
    }

}

if (!function_exists('awwlm_autoloader')) {

    function awwlm_autoloader($class_name)
    {
        if (0 === strpos($class_name, 'AWWLM')) {
            $classes_dir = realpath(plugin_dir_path(__FILE__)) . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR;
            $class_file = 'class-' . str_replace('_', '-', strtolower($class_name)) . '.php';
            require_once $classes_dir . $class_file;
        }
    }

}

if (!function_exists('AWWLM')) {

    function AWWLM()
    {
        $instance = AWWLM_Backend::instance(__FILE__, AWWLM_VERSION);
        return $instance;
    }

}
add_action('plugins_loaded', 'awwlm_init');
spl_autoload_register('awwlm_autoloader');
if (is_admin()) {
    AWWLM();
}
new AWWLM_Api();

new AWWLM_Front_End(__FILE__, AWWLM_VERSION);

add_action( 'before_woocommerce_init', function() {
	if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
	}
} );
