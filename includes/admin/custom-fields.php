<?php
// Add a custom field for "user role" to the WordPress menu
function hmi_custom_menu_item_fields($id, $item, $depth, $args)
{
    $roles = wp_roles()->get_names();
?>
    <div class="field-custom description-wide">
        <label for="edit-menu-item-user-roles-<?php echo $id; ?>">
            <?php _e('Select the user roles to hide the item'); ?><br />
        </label>
        <?php foreach ($roles as $key => $role) : ?>
            <input type="checkbox" id="edit-menu-item-user-roles-<?php echo esc_attr($id) . '-' . esc_attr($key); ?>" class="widefat code edit-menu-item-custom" name="menu-item-user-roles[<?php echo esc_attr($id); ?>][]" value="<?php echo esc_attr($key); ?>" <?php if (in_array($key, (array) get_post_meta($id, '_menu_item_user_roles', true))) echo 'checked'; ?>>
            <label for="edit-menu-item-user-roles-<?php echo esc_attr($id) . '-' . esc_attr($key); ?>"><?php echo esc_html($role); ?></label><br>
        <?php endforeach; ?>
    </div>
<?php
}
add_action('wp_nav_menu_item_custom_fields', 'hmi_custom_menu_item_fields', 10, 4);

// Save the custom field for "user role"
function hmi_save_custom_menu_item_fields($menu_id, $menu_item_db_id, $menu_item_data)
{
    // Save the menu item's user roles
    if (isset($_POST['menu-item-user-roles'][$menu_item_db_id])) {
        $user_roles = $_POST['menu-item-user-roles'][$menu_item_db_id];
    } else {
        $user_roles = array();
    }
    update_post_meta($menu_item_db_id, '_menu_item_user_roles', $user_roles);

    // Save the menu item's menu ID and menu name
    update_post_meta($menu_item_db_id, '_menu_item_menu_id', $menu_id);
    update_post_meta($menu_item_db_id, '_menu_item_menu_name', $menu_item_data['menu-item-title']);
}

add_action('wp_update_nav_menu_item', 'hmi_save_custom_menu_item_fields', 10, 3);
