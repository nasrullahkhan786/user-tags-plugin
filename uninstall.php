<?php
// Exit if accessed directly
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Delete user tags taxonomy terms
$terms = get_terms([
    'taxonomy'   => 'user_tags',
    'hide_empty' => false
]);

if (!is_wp_error($terms) && !empty($terms)) {
    foreach ($terms as $term) {
        wp_delete_term($term->term_id, 'user_tags');
    }
}

// Remove any plugin-specific options from wp_options table
delete_option('user_tags_plugin_settings'); 

// Clear any saved user meta related to user tags (if applicable)
global $wpdb;
$wpdb->delete($wpdb->usermeta, ['meta_key' => 'user_tags']); 
