<?php
/**
 * Plugin Name: Hide Menu Items by Role
 * Plugin URI: https://wordpress.org/plugins/hide-menu-items-by-role
 * Description: Hide specific navigation menu items based on user role
 * Version: 2.0.0
 * Requires at least: 5.0
 * Requires PHP: 7.0
 * Author: Mahfuzur Rahman
 * Author URI: https://www.linkedin.com/in/mahfuzd
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: hmibyrole
 */

// Define constants
define('HMI_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('HMI_PLUGIN_URL', plugin_dir_url(__FILE__));

// Enqueue admin assets for nav menu editor
function hmi_enqueue_admin_assets() {
    $screen = get_current_screen();
    
    // Only enqueue on nav-menus.php page
    if (is_object($screen) && $screen->id === 'nav-menus') {
        $css_file = HMI_PLUGIN_DIR . 'assets/css/hmibr-admin.css';
        $js_file  = HMI_PLUGIN_DIR . 'assets/js/hmibr-admin.js';

        wp_enqueue_style(
            'hmibr-admin-style',
            HMI_PLUGIN_URL . 'assets/css/hmibr-admin.css',
            array(),
            // Use the file modification time so browser caches refresh after updates.
            file_exists($css_file) ? (string) filemtime($css_file) : '2.0.0'
        );
        
        wp_enqueue_script(
            'hmibr-admin-script',
            HMI_PLUGIN_URL . 'assets/js/hmibr-admin.js',
            array('jquery'),
            file_exists($js_file) ? (string) filemtime($js_file) : '2.0.0',
            true
        );
    }
}
add_action('admin_enqueue_scripts', 'hmi_enqueue_admin_assets');

// Include required files
require_once HMI_PLUGIN_DIR . 'includes/functions.php';
require_once plugin_dir_path(__FILE__) . 'includes/admin/custom-fields.php';

// Initialize the plugin
add_action('plugins_loaded', 'hmi_init');
function hmi_init()
{
    // Initialize the plugin code here
}
