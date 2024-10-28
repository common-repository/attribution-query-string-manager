<?php
/*
Plugin Name: Attribution Query String Manager
Plugin URI: http://wordpress.org/plugins/aqsm/
Description: This plugin will help manage query string variables to ensure that desired variables are always included on certain domains. This plugin was developed to assist with affiliate tracking needs for sites/blogs that link to separate purchase flows.
Author: Tor N. Johnson
Version: 0.1.3
Author URI: http://profiles.wordpress.org/kasigi
License: GPL2
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/


	// Define shorthand constants
	if (!defined('AQSM_PLUGIN_NAME')){
	    define('AQSM_PLUGIN_NAME', trim(dirname(plugin_basename(__FILE__)), '/'));
	}

	if (!defined('AQSM_PLUGIN_DIR')){
	    define('AQSM_PLUGIN_DIR', WP_PLUGIN_DIR . '/' . AQSM_PLUGIN_NAME);
	}

	if (!defined('AQSM_PLUGIN_URL')){
	    define('AQSM_PLUGIN_URL', plugins_url() . '/' . AQSM_PLUGIN_NAME);
	}

	// Set version information
	if (!defined('AQSM_VERSION_KEY')){
	    define('AQSM_VERSION_KEY', 'aqsm_version');
	}

	if (!defined('AQSM_VERSION_NUM')){
	    define('AQSM_VERSION_NUM', '0.1.0');
	}
	add_option(AQSM_VERSION_KEY, AQSM_VERSION_NUM);


	// Check to see if updates need to occur
	if (get_option(AQSM_VERSION_KEY) != AQSM_VERSION_NUM) {
		// If there is any future update code needed it will go here

	    // Then update the version value
	    update_option(AQSM_VERSION_KEY, AQSM_VERSION_NUM);
	}

if ( ! defined( 'ABSPATH' ) ) exit;
require_once(AQSM_PLUGIN_DIR."/includes/simple_html_dom.php");
require_once(AQSM_PLUGIN_DIR."/includes/aqsm_engine_init.php");
require_once(AQSM_PLUGIN_DIR."/includes/aqsm_engine.php");
require_once(AQSM_PLUGIN_DIR."/includes/aqsm_content_editor.php");
if(is_admin()){
	include_once(AQSM_PLUGIN_DIR."/includes/aqsm_admin.php");
}

add_action( 'init', 'AQSM_LinkTrackingQSFilterInit');


// Add css for admin panels
add_action('admin_enqueue_scripts', 'AQSM_admin_theme_style');
function AQSM_admin_theme_style() {
    wp_enqueue_style('AQSM-admin-css', AQSM_PLUGIN_URL.'/AQSM-admin.css');
    wp_enqueue_script('AQSM-admin-js', AQSM_PLUGIN_URL.'/AQSM-admin.js');
}

/* end link_tracking_qs_filter */
