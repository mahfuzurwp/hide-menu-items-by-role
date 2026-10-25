<?php
// Prevent direct access.
if (!defined('ABSPATH')) {
    exit;
}

function hmi_hide_menu_items_based_on_role($items, $args)
{
    // Get the menu ID from the $args parameter
    $menu_id = $args->menu->term_id;

    // Get the current user's roles
    $user_roles = is_user_logged_in() ? wp_get_current_user()->roles : ['guest'];

    // Process each menu item
    foreach ($items as $key => $item) {
        // Get meta values for this menu item
        $item_id = $item->ID;
        
        // 1. Check login state first - if it resolves to hide, exclude item immediately
        $login_state = get_post_meta($item_id, '_hmibr_login_state', true) ?: 'any';
        if ($login_state === 'logged_in' && !is_user_logged_in()) {
            unset($items[$key]);
            continue;
        }
        if ($login_state === 'logged_out' && is_user_logged_in()) {
            unset($items[$key]);
            continue;
        }

        // Only guests reach this point on a logged-out item, and guests have no role
        // to match, so role rules are skipped (they are disabled in the editor too).
        if ($login_state === 'logged_out') {
            continue;
        }
        
        // 2. If no roles are saved, keep the item (nothing to check)
        $roles = get_post_meta($item_id, '_menu_item_user_roles', true);
        if (empty($roles)) {
            continue;
        }
        
        // 3. Read mode and apply hide/show logic
        $mode = get_post_meta($item_id, '_hmibr_mode', true) ?: 'hide';
        
        // 'all' shows the item to everyone, so role rules are skipped
        if ($mode === 'all') {
            continue;
        }
        
        $user_roles_array = is_array($user_roles) ? $user_roles : [];
        $roles_array = is_array($roles) ? $roles : [];
        $has_overlap = (bool) array_intersect($user_roles_array, $roles_array);
        
        if ($mode === 'hide' && $has_overlap) {
            unset($items[$key]); // exclude item - user has a role that should be hidden
        }
        if ($mode === 'show' && !empty($roles_array) && !$has_overlap) {
            unset($items[$key]); // exclude item - user doesn't have any of the allowed roles
        }
        // Note: when mode is 'show' and no roles are selected, we keep the item (show to everyone)
    }

    return $items;
}
add_filter('wp_nav_menu_objects', 'hmi_hide_menu_items_based_on_role', 10, 2);
