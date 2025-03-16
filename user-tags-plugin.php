<?php
/**
 * Plugin Name: User Tags Plugin
 * Plugin URI:  https://github.com/yourrepo/user-tags-plugin
 * Description: A WordPress plugin to categorize users with a custom taxonomy.
 * Version:     1.0.0
 * Author:      NK
 * Author URI:  https://yourwebsite.com
 * License:     GPL v2 or later
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

// Define Constants
define('UTP_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('UTP_PLUGIN_URL', plugin_dir_url(__FILE__));

// Include Required Files
require_once UTP_PLUGIN_DIR . 'includes/class-user-tags.php';
require_once UTP_PLUGIN_DIR . 'includes/class-user-tags-filter.php';
require_once UTP_PLUGIN_DIR . 'includes/class-user-tags-ajax.php';
require_once UTP_PLUGIN_DIR . 'includes/class-user-tags-metabox.php';


// Initialize Plugin
function utp_init() {
    new User_Tags();
    new User_Tags_Metabox();
    new User_Tags_Filter();
    new User_Tags_AJAX();
    
}
add_action('plugins_loaded', 'utp_init');
