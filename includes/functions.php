<?php
function hide_menu_items_based_on_role($items, $args)
{
    // Get the menu name from the plugin settings
    $menu_name = get_option('hide_menu_items_options_menu_name');
    $user_role = get_option('hide_menu_items_options_user_role');

    // Get the selected items from the plugin settings
    $selected_items = get_option('hide_menu_items_options_menu_item');

    if (!is_array($selected_items)) {
        $selected_items = array();
    }

    // If the user is an $user_role and the current menu is the specified one, hide the selected menu items
    if (in_array($user_role, wp_get_current_user()->roles) && $args->menu->name == $menu_name) {
        foreach ($items as $key => $item) {
            if (in_array($item->title, $selected_items)) {
                unset($items[$key]);
            }
        }
    }

    return $items;
}
add_filter('wp_nav_menu_objects', 'hide_menu_items_based_on_role', 10, 2);
