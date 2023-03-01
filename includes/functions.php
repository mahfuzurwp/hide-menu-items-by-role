<?php
function hmi_hide_menu_items_based_on_role($items, $args)
{
    // Get the menu ID from the $args parameter
    $menu_id = $args->menu->term_id;

    // Get the user role from the plugin settings
    $user_role = get_option('hide_menu_items_options_user_role');

    // Get the selected menu items for this menu ID
    $selected_items = array();
    $menu_items = wp_get_nav_menu_items($menu_id);
    foreach ($menu_items as $menu_item) {
        $menu_item_id = $menu_item->ID;
        $menu_item_user_roles = get_post_meta($menu_item_id, '_menu_item_user_roles', true);
        if (!empty($menu_item_user_roles)) {
            $selected_items[$menu_item->title] = $menu_item_user_roles;
        }
    }

    // If the user is an $user_role and the current menu has selected items, hide them
    if (in_array($user_role, wp_get_current_user()->roles) && !empty($selected_items)) {
        foreach ($items as $key => $item) {
            if (isset($selected_items[$item->title]) && in_array($user_role, $selected_items[$item->title])) {
                unset($items[$key]);
            }
        }
    }

    return $items;
}
add_filter('wp_nav_menu_objects', 'hmi_hide_menu_items_based_on_role', 10, 2);
