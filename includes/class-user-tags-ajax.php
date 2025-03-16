<?php
if (!defined('ABSPATH')) exit;

/**
 * Class User_Tags_AJAX
 *
 * This class handles AJAX search functionality for user tags 
 * and enqueues necessary scripts for admin pages.
 */
class User_Tags_AJAX {

    /**
     * Constructor - Hooks into WordPress actions for AJAX handling and script enqueuing.
     */
    public function __construct() {
        add_action('wp_ajax_search_user_tags', [$this, 'search_user_tags']); // AJAX search handler
        add_action('admin_enqueue_scripts', [$this, 'enqueue_scripts']); // Load scripts in admin panel
    }

    /**
     * Enqueues Select2 and custom scripts on user-related admin pages.
     *
     * @param string $hook The current admin page hook.
     */
    public function enqueue_scripts($hook) {
        
        if (in_array($hook, ['users.php', 'user-edit.php', 'profile.php'], true)) {
        
            wp_enqueue_style('select2-css', 'https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css');
            wp_enqueue_script('select2', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js', ['jquery'], null, true);
            wp_enqueue_script(
                'utp-admin-js',
                UTP_PLUGIN_URL . 'assets/js/custom-admin.js',
                ['jquery', 'select2'],
                null,
                true
            );
            wp_localize_script('utp-admin-js', 'utp_ajax', [
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce'    => wp_create_nonce('search_user_tags_nonce'),
            ]);
        }
    }

    /**
     * Handles AJAX search for user tags.
     */
    public function search_user_tags() {

        check_ajax_referer('search_user_tags_nonce', 'nonce');
        $search = isset($_GET['q']) ? sanitize_text_field($_GET['q']) : '';

        $terms = get_terms([
            'taxonomy'   => 'user_tags',
            'hide_empty' => false,
            'search'     => $search
        ]);

        if (is_wp_error($terms) || empty($terms)) {
            wp_send_json([]);
        }
        $results = array_map(function ($term) {
            return ['id' => $term->term_id, 'text' => esc_html($term->name)];
        }, $terms);

        wp_send_json($results);
    }
}