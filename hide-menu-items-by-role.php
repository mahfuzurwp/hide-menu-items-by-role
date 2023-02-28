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
 * Update URI: https://github.com/coder-mahfuz
 * Text Domain: hmibyrole
 */

// Define constants
define('HMI_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('HMI_PLUGIN_URL', plugin_dir_url(__FILE__));

// Include required files
require_once HMI_PLUGIN_DIR . 'includes/functions.php';
require_once HMI_PLUGIN_DIR . 'includes/admin/hide-menu-items-admin.php';

// Initialize the plugin
add_action('plugins_loaded', 'hmi_init');
function hmi_init()
{
    // Initialize the plugin code here
}


function hide_menu_items_settings_link($links)
{
    $settings_link = '<a href="' . admin_url('options-general.php?page=hide-menu-items') . '">Settings</a>';
    array_unshift($links, $settings_link);
    return $links;
}
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'hide_menu_items_settings_link');
