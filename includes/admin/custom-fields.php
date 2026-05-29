<?php
// Add a custom field for "user role" to the WordPress menu
function hmi_custom_menu_item_fields($id, $item, $depth, $args)
{
    // Get saved values for new fields
    $login_state = get_post_meta($id, '_hmibr_login_state', true) ?: 'any';
    $mode = get_post_meta($id, '_hmibr_mode', true) ?: 'hide';
    $roles = wp_roles()->get_names();
?>
    <!-- Login State Field -->
    <p class="field-hmibr-login-state description description-wide">
        <label for="edit-menu-item-login-state-<?php echo esc_attr($id); ?>">
            <?php _e('Visibility', 'hmibyrole'); ?>
            <select id="edit-menu-item-login-state-<?php echo esc_attr($id); ?>" name="hmibr_login_state[<?php echo $id; ?>]">
                <option value="any"       <?php selected($login_state, 'any'); ?>><?php _e('Everyone', 'hmibyrole'); ?></option>
                <option value="logged_out" <?php selected($login_state, 'logged_out'); ?>><?php _e('Logged-out users only', 'hmibyrole'); ?></option>
                <option value="logged_in"  <?php selected($login_state, 'logged_in'); ?>><?php _e('Logged-in users only', 'hmibyrole'); ?></option>
            </select>
        </label>
    </p>
    
    <!-- Mode Field (Hide/Show) -->
    <p class="field-hmibr-mode description description-wide">
        <label for="edit-menu-item-mode-<?php echo esc_attr($id); ?>">
            <?php _e('Menu Item Visibility Mode', 'hmibyrole'); ?>
            <select id="edit-menu-item-mode-<?php echo esc_attr($id); ?>" name="hmibr_mode[<?php echo $id; ?>]">
                <option value="hide"   <?php selected($mode, 'hide'); ?>><?php _e('Hide from these roles', 'hmibyrole'); ?></option>
                <option value="show"   <?php selected($mode, 'show'); ?>><?php _e('Show only to these roles', 'hmibyrole'); ?></option>
            </select>
        </label>
    </p>
    
    <!-- Existing User Roles Field -->
    <div class="field-custom description-wide">
        <label for="edit-menu-item-user-roles-<?php echo esc_attr($id); ?>">
            <?php _e('Select the user roles', 'hmibyrole'); ?><br />
        </label>
        <?php foreach ($roles as $key => $role) : ?>
            <input type="checkbox" id="edit-menu-item-user-roles-<?php echo esc_attr($id) . '-' . esc_attr($key); ?>" class="widefat code edit-menu-item-custom" name="menu-item-user-roles[<?php echo esc_attr($id); ?>][]" value="<?php echo esc_attr($key); ?>" <?php if (in_array($key, (array) get_post_meta($id, '_menu_item_user_roles', true))) echo 'checked'; ?>>
            <label for="edit-menu-item-user-roles-<?php echo esc_attr($id) . '-' . esc_attr($key); ?>"><?php echo esc_html($role); ?></label><br>
        <?php endforeach; ?>
    </div>
    
    <!-- Script to handle disabling of fields based on login state -->
    <script type="text/javascript">
        jQuery(document).ready(function($) {
            function updateFieldStates() {
                var loginState = $('#edit-menu-item-login-state-<?php echo $id; ?>').val();
                var isLoggedOutOnly = (loginState === 'logged_out');
                
                // Disable/enable mode field
                $('#edit-menu-item-mode-<?php echo $id; ?>').prop('disabled', isLoggedOutOnly);
                if (isLoggedOutOnly) {
                    $('#edit-menu-item-mode-<?php echo $id; ?>').closest('p').addClass('hmibr-disabled');
                } else {
                    $('#edit-menu-item-mode-<?php echo $id; ?>').closest('p').removeClass('hmibr-disabled');
                }
                
                // Disable/enable user roles field
                $('.edit-menu-item-custom[name*="menu-item-user-roles[' + <?php echo $id; ?>]"]').prop('disabled', isLoggedOutOnly);
                $('#edit-menu-item-user-roles-<?php echo $id; ?>').closest('div.field-custom').toggleClass('hmibr-disabled', isLoggedOutOnly);
            }
            
            // Initialize state on page load
            updateFieldStates();
            
            // Update state when login state changes
            $('#edit-menu-item-login-state-<?php echo $id; ?>').change(function() {
                updateFieldStates();
            });
        });
    </script>
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
    
    // Save hmibr_mode field
    $allowed_modes = ['hide', 'show'];
    $mode = isset($_POST['hmibr_mode'][$menu_item_db_id])
        ? sanitize_key($_POST['hmibr_mode'][$menu_item_db_id])
        : 'hide';
    $mode = in_array($mode, $allowed_modes, true) ? $mode : 'hide';
    update_post_meta($menu_item_db_id, '_hmibr_mode', $mode);
}

add_action('wp_update_nav_menu_item', 'hmi_save_custom_menu_item_fields', 10, 3);
