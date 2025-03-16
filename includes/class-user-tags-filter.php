<?php
if (!defined('ABSPATH')) exit;

/**
 * Class User_Tags_Filter
 *
 * This class adds a user tag filter to the WordPress Users screen,
 * allows filtering users by tags, and displays user tags in the admin table.
 */
class User_Tags_Filter {

    /**
     * Constructor - Hooks into WordPress to add filtering functionality.
     */
    public function __construct() {
        add_action('restrict_manage_users', [$this, 'add_user_tags_filter']); // Add dropdown filter
        add_filter('pre_get_users', [$this, 'filter_users_by_tag']); // Modify user query based on filter
        add_filter('manage_users_columns', [$this, 'ut_add_user_tags_column']); // Add 'User Tags' column
        add_action('manage_users_custom_column', [$this, 'ut_show_user_tags_column'], 10, 3); // Populate 'User Tags' column
    }

    /**
     * Adds a dropdown filter to the Users admin page for filtering by user tags.
     *
     * @param string $which Determines placement in the users list table.
     */
    public function add_user_tags_filter($which) {
    
        $select_template = '<select name="user_tag_filter" style="float:none; margin-left:15px;">
                                <option value="">%s</option>%s
                            </select>';

        $option_template = '<option value="%s" %s>%s</option>';

        $selected_tag = $_GET['user_tag_filter'] ?? '';

        $tags = get_terms([
            'taxonomy'   => 'user_tags',
            'hide_empty' => false,
        ]);

        if (is_wp_error($tags) || empty($tags)) {
            $tags = [];
        }
        $options = implode('', array_map(function ($tag) use ($option_template, $selected_tag) {
            return sprintf(
                $option_template,
                esc_attr($tag->term_id),
                selected($tag->term_id, $selected_tag, false),
                esc_html($tag->name)
            );
        }, $tags));

        printf($select_template, __('Filter by user tags...', 'user-tags'), $options);
        submit_button(__('Filter', 'user-tags'), null, $which, false);
    }

    /**
     * Filters users by the selected tag.
     *
     * @param WP_User_Query $query The current user query.
     */
    public function filter_users_by_tag($query) {
        global $pagenow;

        if ($pagenow === 'users.php' && !empty($_GET['user_tag_filter'])) {
            $tag_id = intval($_GET['user_tag_filter']);

            $user_ids = get_objects_in_term($tag_id, 'user_tags');

            if (!empty($user_ids)) {
                $query->query_vars['include'] = $user_ids;
            } else {
                $query->query_vars['include'] = [0];
            }
        }
    }

    /**
     * Adds a 'User Tags' column to the Users table in the admin panel.
     *
     * @param array $columns Existing columns.
     * @return array Modified columns with 'User Tags' added.
     */
    public function ut_add_user_tags_column($columns) {
        $new_columns = [];
        $counter = 0;

        foreach ($columns as $key => $value) {
            $new_columns[$key] = $value;
            $counter++;
            
            if ($counter === 3) {
                $new_columns['user_tags'] = __('User Tags', 'user-tags');
            }
        }

        return $new_columns;
    }

    /**
     * Displays user tags in the 'User Tags' column.
     *
     * @param string $value Current column value.
     * @param string $column_name Column name.
     * @param int $user_id User ID.
     * @return string User tags list or placeholder.
     */
    public function ut_show_user_tags_column($value, $column_name, $user_id) {
        if ($column_name === 'user_tags') {
            $terms = wp_get_object_terms($user_id, 'user_tags');

            return !empty($terms) ? implode(', ', wp_list_pluck($terms, 'name')) : '—';
        }

        return $value;
    }
}