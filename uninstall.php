<?php
// Exit if accessed directly
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Include WordPress functions if necessary
require_once ABSPATH . 'wp-load.php';
require_once ABSPATH . 'wp-includes/taxonomy.php';

// Ensure the taxonomy exists before proceeding
if (taxonomy_exists('user_tags')) {
    // Delete all terms under 'user_tags' taxonomy
    $terms = get_terms([
        'taxonomy'   => 'user_tags',
        'hide_empty' => false
    ]);

    if (!is_wp_error($terms) && !empty($terms)) {
        foreach ($terms as $term) {
            wp_delete_term($term->term_id, 'user_tags');
        }
    }
}

// Clean up user meta related to user tags
global $wpdb;
$wpdb->query("DELETE FROM {$wpdb->usermeta} WHERE meta_key = 'user_tags'");

// Remove taxonomy itself
$wpdb->query("DELETE FROM {$wpdb->term_taxonomy} WHERE taxonomy = 'user_tags'");
$wpdb->query("DELETE t FROM {$wpdb->terms} t LEFT JOIN {$wpdb->term_taxonomy} tt ON t.term_id = tt.term_id WHERE tt.taxonomy IS NULL");