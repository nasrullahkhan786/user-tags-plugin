<?php
if (!defined('ABSPATH')) exit;

/**
 * Class User_Tags_Metabox
 * 
 * Handles the User Tags metabox in the user profile page, allowing
 * administrators to assign tags to users.
 */
class User_Tags_Metabox {

    /**
     * Constructor to initialize hooks for displaying and saving user tags.
     */
    public function __construct() {
        add_action('show_user_profile', [$this, 'add_user_tags_metabox']); // Add metabox to user profile page
        add_action('edit_user_profile', [$this, 'add_user_tags_metabox']); // Add metabox to edit user profile page
        add_action('personal_options_update', [$this, 'save_user_tags']); // Save tags on user profile update
        add_action('edit_user_profile_update', [$this, 'save_user_tags']); // Save tags on admin profile update
    }

    /**
     * Display the User Tags metabox in user profile edit screen.
     * 
     * @param WP_User $user The user object.
     */
    public function add_user_tags_metabox($user) {
        $user_tags = get_terms([
            'taxonomy'   => 'user_tags',
            'hide_empty' => false,
        ]);

        $selected_tags = wp_get_object_terms($user->ID, 'user_tags', ['fields' => 'ids']);
        ?>
        <h3><?php _e('User Tags', 'user-tags'); ?></h3>
        <table class="form-table">
            <tr>
                <th><label for="user_tags"><?php _e('Select Tags', 'user-tags'); ?></label></th>
                <td>
                    <select name="user_tags[]" id="user_tags" multiple class="select2">
                        <?php foreach ($user_tags as $tag) : ?>
                            <option value="<?php echo esc_attr($tag->term_id); ?>" <?php echo in_array($tag->term_id, $selected_tags) ? 'selected' : ''; ?>>
                                <?php echo esc_html($tag->name); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
        </table>
        <?php
    }

    /**
     * Save selected user tags when the profile is updated.
     * 
     * @param int $user_id The ID of the user being updated.
     */
    public function save_user_tags($user_id) {
        if (!current_user_can('edit_user', $user_id)) {
            return false;
        }

        if (!isset($_POST['user_tags'])) {
            return;
        }
        $tag_ids = array_map('intval', $_POST['user_tags']);
        $tag_names = [];

        foreach ($tag_ids as $tag_id) {
            $term = get_term($tag_id, 'user_tags');
            if (!is_wp_error($term) && $term) {
                $tag_names[] = $term->name;
            }
        }

        wp_set_object_terms($user_id, $tag_names, 'user_tags', false);
    }
}