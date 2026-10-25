<?php
// Add a custom field for "user role" to the WordPress menu
function hmi_custom_menu_item_fields($id, $item, $depth, $args)
{
    // Get saved values for new fields
    $login_state = get_post_meta($id, '_hmibr_login_state', true) ?: 'any';
    $mode = get_post_meta($id, '_hmibr_mode', true);
    $roles = wp_roles()->get_names();
    $saved_roles = (array) get_post_meta($id, '_menu_item_user_roles', true);

    // "Show to all roles" is the default for new items. Items that already have
    // roles saved (from before the mode option existed) keep the "hide" default.
    if (!$mode) {
        $mode = empty($saved_roles) ? 'all' : 'hide';
    }
?>
    <!-- Audience Panel -->
    <details class="hmibr-panel" open>
        <summary class="hmibr-panel__summary">
            <span class="hmibr-panel__icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" focusable="false"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
            </span>
            <span class="hmibr-panel__title"><?php _e('Audience', 'hmibyrole'); ?></span>
            <span class="hmibr-panel__chevron" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" focusable="false"><polyline points="18 15 12 9 6 15"></polyline></svg>
            </span>
        </summary>

        <div class="hmibr-panel__body">
            <div class="hmibr-field-row">
                <!-- Login State Field -->
                <p class="field-hmibr-login-state description description-wide">
                    <label class="hmibr-field-label" for="edit-menu-item-login-state-<?php echo esc_attr($id); ?>">
                        <?php _e('User Status', 'hmibyrole'); ?>
                    </label>
                    <select id="edit-menu-item-login-state-<?php echo esc_attr($id); ?>" name="hmibr_login_state[<?php echo $id; ?>]">
                        <option value="any"       <?php selected($login_state, 'any'); ?>><?php _e('Everyone', 'hmibyrole'); ?></option>
                        <option value="logged_out" <?php selected($login_state, 'logged_out'); ?>><?php _e('Logged-out users only', 'hmibyrole'); ?></option>
                        <option value="logged_in"  <?php selected($login_state, 'logged_in'); ?>><?php _e('Logged-in users only', 'hmibyrole'); ?></option>
                    </select>
                </p>

                <!-- Mode Field (Hide/Show) -->
                <p class="field-hmibr-mode description description-wide">
                    <label class="hmibr-field-label" for="edit-menu-item-mode-<?php echo esc_attr($id); ?>">
                        <?php _e('Role Visibility', 'hmibyrole'); ?>
                    </label>
                    <select id="edit-menu-item-mode-<?php echo esc_attr($id); ?>" name="hmibr_mode[<?php echo $id; ?>]">
                        <option value="all"  <?php selected($mode, 'all'); ?>><?php _e('Show to all roles', 'hmibyrole'); ?></option>
                        <option value="hide" <?php selected($mode, 'hide'); ?>><?php _e('Hide for selected roles', 'hmibyrole'); ?></option>
                        <option value="show" <?php selected($mode, 'show'); ?>><?php _e('Show only to selected roles', 'hmibyrole'); ?></option>
                    </select>
                </p>
            </div>

            <!-- Existing User Roles Field -->
            <div class="field-custom description-wide hmibr-roles" id="edit-menu-item-user-roles-<?php echo esc_attr($id); ?>">
                <span class="hmibr-roles-title"><?php _e('Select the user roles', 'hmibyrole'); ?></span>
                <div class="hmibr-role-list">
                    <?php foreach ($roles as $key => $role) : ?>
                        <label class="hmibr-role">
                            <input type="checkbox" id="edit-menu-item-user-roles-<?php echo esc_attr($id) . '-' . esc_attr($key); ?>" class="widefat code edit-menu-item-custom" name="menu-item-user-roles[<?php echo esc_attr($id); ?>][]" value="<?php echo esc_attr($key); ?>" <?php checked(in_array($key, $saved_roles)); ?>>
                            <span class="hmibr-role__name"><?php echo esc_html($role); ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </details>
<?php
}
add_action('wp_nav_menu_item_custom_fields', 'hmi_custom_menu_item_fields', 10, 4);

// Save the custom field for "user role"
function hmi_save_custom_menu_item_fields($menu_id, $menu_item_db_id, $menu_item_data)
{
    // Sanitize the menu item's user roles
    if (isset($_POST['menu-item-user-roles'][$menu_item_db_id])) {
        $user_roles = $_POST['menu-item-user-roles'][$menu_item_db_id];
        $user_roles = array_map('sanitize_text_field', $user_roles);
    } else {
        $user_roles = array();
    }

    // Validate the menu item's user roles
    $editable_roles = get_editable_roles();
    foreach ($user_roles as $role) {
        if (!array_key_exists($role, $editable_roles)) {
            $messages[] = __('Error: Invalid user role selected.', ' hmibyrole');
            break;
        }
    }

    // If there are error messages, add them to the settings error message stack
    if (!empty($messages)) {
        add_settings_error('menu_editor', 'menu_editor_error', implode('<br>', $messages), 'error');
        return;
    }

    //save the user roles
    update_post_meta($menu_item_db_id, '_menu_item_user_roles', $user_roles);

    // Save the menu item's menu ID and menu name
    update_post_meta($menu_item_db_id, '_menu_item_menu_id', $menu_id);
    update_post_meta($menu_item_db_id, '_menu_item_menu_name', $menu_item_data['menu-item-title']);
    
    // Save hmibr_login_state field
    $allowed_login_states = ['any', 'logged_in', 'logged_out'];
    $login_state = isset($_POST['hmibr_login_state'][$menu_item_db_id])
        ? sanitize_key($_POST['hmibr_login_state'][$menu_item_db_id])
        : 'any';
    $login_state = in_array($login_state, $allowed_login_states, true) ? $login_state : 'any';
    update_post_meta($menu_item_db_id, '_hmibr_login_state', $login_state);
    
    // Save hmibr_mode field. The stored value is left untouched when the field is
    // not submitted, e.g. when an item is created through the add-menu-item request.
    if (isset($_POST['hmibr_mode'][$menu_item_db_id])) {
        $allowed_modes = ['all', 'hide', 'show'];
        $mode = sanitize_key($_POST['hmibr_mode'][$menu_item_db_id]);
        $mode = in_array($mode, $allowed_modes, true) ? $mode : 'hide';
        update_post_meta($menu_item_db_id, '_hmibr_mode', $mode);
    }
}

add_action('wp_update_nav_menu_item', 'hmi_save_custom_menu_item_fields', 10, 3);
