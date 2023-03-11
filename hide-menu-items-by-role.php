<?php

/**
 * Plugin Name: Hide Menu Items by Role
 * Plugin URI: https://wordpress.org/plugins/hide-menu-items-by-role
 * Description: Hide specific navigation menu items based on user role
 * Version: 1.0.0
 * Requires at least: 5.0
 * Requires PHP: 7.0
 * Author: Pixelyd
 * Author URI: https://www.pixelyd.com/
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: hmibyrole
 */

// Define constants
define('HMI_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('HMI_PLUGIN_URL', plugin_dir_url(__FILE__));

// Include required files
require_once HMI_PLUGIN_DIR . 'includes/functions.php';
require_once plugin_dir_path(__FILE__) . 'includes/admin/custom-fields.php';

// Initialize the plugin
add_action('plugins_loaded', 'hmi_init');
function hmi_init()
{
    // Initialize the plugin code here
}
