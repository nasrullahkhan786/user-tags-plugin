<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * Class User_Tags
 * This class handles the creation, display, and management of a custom user taxonomy called "User Tags".
 */
class User_Tags {

    public function __construct() {
        add_action('init', [$this, 'register_user_tags_taxonomy']);

        add_action('show_user_profile', [$this, 'user_tags_meta_box']);
        add_action('edit_user_profile', [$this, 'user_tags_meta_box']);

        add_action('personal_options_update', [$this, 'save_user_tags']);
        add_action('edit_user_profile_update', [$this, 'save_user_tags']);

        add_action('admin_menu', [$this, 'add_user_taxonomy_admin_page']);

        add_filter('parent_file', [$this, 'filter_user_taxonomy_admin_page_parent_file']);
        add_filter('submenu_file', [$this, 'filter_user_taxonomy_admin_page_submenu_file']);

        add_filter('manage_edit-user_tags_columns', [$this, 'change_user_tags_count_label']);
        add_filter('manage_user_tags_custom_column', [$this, 'modify_user_tags_column_content'], 10, 3);
    }

    /**
     * Registers the 'user_tags' taxonomy for users.
     */
    public function register_user_tags_taxonomy() {
        register_taxonomy('user_tags', 'user', [
            'labels' => [
                'name'              => __('User Tags', 'utp'),
                'singular_name'     => __('User Tag', 'utp'),
                'menu_name'         => __('User Tags', 'utp'),
                'all_items'         => __('All User Tags', 'utp'),
                'edit_item'         => __('Edit User Tag', 'utp'),
                'view_item'         => __('View User Tag', 'utp'),
                'update_item'       => __('Update User Tag', 'utp'),
                'add_new_item'      => __('Add New User Tag', 'utp'),
                'new_item_name'     => __('New User Tag Name', 'utp'),
                'search_items'      => __('Search User Tags', 'utp'),
                'not_found'         => __('No user tags found.', 'utp'),
            ],
            'public'            => true,
            'show_ui'           => true,
            'show_admin_column' => true,
            'hierarchical'      => false,
            'query_var'         => true,
            'rewrite'           => ['slug' => 'user-tags'],
            'update_count_callback' => [$this, 'user_taxonomy_update_count_callback'],
        ]);
    }

    /**
     * Displays a multi-select dropdown for user tags on the user profile page.
     */
    public function user_tags_meta_box($user) {
        $user_tags = wp_get_object_terms($user->ID, 'user_tags', ['fields' => 'ids']);
        $terms = get_terms(['taxonomy' => 'user_tags', 'hide_empty' => false]);

        echo '<h3>' . __('User Tags', 'utp') . '</h3>';
        echo '<select name="user_tags[]" multiple style="width:100%;">';
        foreach ($terms as $term) {
            $selected = in_array($term->term_id, $user_tags) ? 'selected' : '';
            echo '<option value="' . esc_attr($term->term_id) . '" ' . $selected . '>' . esc_html($term->name) . '</option>';
        }
        echo '</select>';
    }

    /**
     * Saves the selected user tags when the user profile is updated.
     */
    public function save_user_tags($user_id) {
        if (!current_user_can('edit_user', $user_id)) {
            return false;
        }

        $tags = isset($_POST['user_tags']) ? array_map('intval', $_POST['user_tags']) : [];
        wp_set_object_terms($user_id, $tags, 'user_tags', false);
    }

    /**
     * Adds "User Tags" as a submenu under the "Users" menu in the admin panel.
     */
    public function add_user_taxonomy_admin_page() {
        $tax = get_taxonomy('user_tags');

        if (!is_object($tax) || is_wp_error($tax)) 
            return;

        add_users_page(
            esc_attr($tax->labels->menu_name),
            esc_attr($tax->labels->menu_name),
            $tax->cap->manage_terms,
            'edit-tags.php?taxonomy=' . $tax->name
        );
    }

    /**
     * Ensures that the User Tags page appears under the Users menu.
     */
    public function filter_user_taxonomy_admin_page_parent_file($parent_file) {
        global $pagenow;

        if (isset($_GET['taxonomy']) && $_GET['taxonomy'] === 'user_tags' && $pagenow === 'edit-tags.php') {
            return 'users.php';
        }

        return $parent_file;
    }

    /**
     * Highlights the correct submenu item when viewing the User Tags page.
     */
    public function filter_user_taxonomy_admin_page_submenu_file($submenu_file) {
        global $pagenow;

        if (isset($_GET['taxonomy']) && $_GET['taxonomy'] === 'user_tags' && $pagenow === 'edit-tags.php') {
            return 'edit-tags.php?taxonomy=user_tags';
        }

        return $submenu_file;
    }

    /**
     * Custom callback to update the count of user tags.
     */
    public function user_taxonomy_update_count_callback($terms, $taxonomy) {
        global $wpdb;

        foreach ((array) $terms as $term) {
            $count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $wpdb->term_relationships WHERE term_taxonomy_id = %d", $term));

            do_action('edit_term_taxonomy', $term, $taxonomy);
            $wpdb->update($wpdb->term_taxonomy, compact('count'), ['term_taxonomy_id' => $term]);
            do_action('edited_term_taxonomy', $term, $taxonomy);
        }
    }

    /**
     * Removes the default "Count" column and replaces it with "Users" in the User Tags admin table.
     */
    public function change_user_tags_count_label($columns) {
        if (isset($columns['posts'])) {
            unset($columns['posts']);
        }
        $columns['users'] = __('Users', 'utp');
        return $columns;
    }

    /**
     * Modifies the content of the custom "Users" column in the User Tags table.
     */
    public function modify_user_tags_column_content($content, $column_name, $term_id) {
        if ($column_name === 'users') {
            $term = get_term($term_id, 'user_tags');
            $count = (int) $term->count;

            $content = "<p>{$count}</p>";
        }
        return $content;
    }
}