<?php

// Define the options page HTML markup
function hide_menu_items_options_page()
{
    echo '<div class="wrap">';
    echo '<h1>Hide Menu Items Settings</h1>';
    echo '<form method="post" action="options.php">';
    settings_fields('hide_menu_items_options_group');   // This is the option group name
    do_settings_sections('hide-menu-items');            // This is the page slug
    submit_button();
    echo '</form>';
    echo '</div>';
}

// Define the settings sections and fields
function hide_menu_items_settings()
{

    register_setting(
        'hide_menu_items_options_group',            // Option group
        'hide_menu_items_options_menu_name'        // Option name
    );

    register_setting(
        'hide_menu_items_options_group',            // Option group
        'hide_menu_items_options_menu_item'        // Option name
    );

    add_settings_section(
        'hide_menu_items_settings_section',         // Section ID
        'Menu Name and Item',                       // Section title
        'hide_menu_items_settings_info',            // Section callback (we don't want anything)
        'hide-menu-items'                           // Page slug
    );

    add_settings_field(
        'hide_menu_items_menu_name',                // Field ID
        'Menu Name',                                // Field title 
        'hide_menu_items_menu_name_input',          // Field callback
        'hide-menu-items',                          // Page slug
        'hide_menu_items_settings_section'          // Section ID
    );

    add_settings_field(
        'hide_menu_items_menu_item',                // Field ID
        'Menu Item',                                // Field title 
        'hide_menu_items_menu_item_input',          // Field callback
        'hide-menu-items',                          // Page slug
        'hide_menu_items_settings_section'          // Section ID
    );

    register_setting(
        'hide_menu_items_options_group',            // Option group
        'hide_menu_items_options_user_role'        // Option name
    );

    add_settings_section(
        'hide_menu_items_settings_section_user_role',           // Section ID
        'User Role',                                            // Section title
        'hide_menu_items_settings_user_role_info',              // Section callback
        'hide-menu-items'                                       // Page slug
    );

    add_settings_field(
        'hide_menu_items_user_role',                            // Field ID
        'User Role',                                            // Field title
        'hide_menu_items_user_role_input',                      // Callback function to display field input
        'hide-menu-items',                                      // Page slug
        'hide_menu_items_settings_section_user_role'            // Section ID
    );
}


// Define the section callback functions

function hide_menu_items_settings_info()
{
    echo 'Choose the menu name and menu item to hide based on user role.';
}

function hide_menu_items_settings_user_role_info()
{
    echo 'Choose the user role to hide the menu item.';
}

// Define the field callbacks

function hide_menu_items_menu_name_input()
{
    $selected_menu_name = esc_attr(get_option('hide_menu_items_options_menu_name'));
    $menus = wp_get_nav_menus();
?>
    <select name="hide_menu_items_options_menu_name">
        <?php foreach ($menus as $menu) : ?>
            <option value="<?php echo $menu->name; ?>" <?php selected($selected_menu_name, $menu->name); ?>><?php echo $menu->name; ?></option>
        <?php endforeach; ?>
    </select>
<?php
}

function hide_menu_items_menu_item_input()
{
    $selected_menu_name = esc_attr(get_option('hide_menu_items_options_menu_name'));
    $menu_item_array = wp_get_nav_menu_items($selected_menu_name);
    $selected_items = get_option('hide_menu_items_options_menu_item');
    if (!is_array($selected_items)) {
        $selected_items = array();
    }

?>

    <?php foreach ($menu_item_array as $menu_item) : ?>
        <?php $checked = in_array($menu_item->title, $selected_items) ? 'checked' : ''; ?>
        <input type="checkbox" name="hide_menu_items_options_menu_item[]" id="<?php echo $menu_item->ID; ?>" value="<?php echo $menu_item->title; ?>" <?php echo $checked; ?>>
        <label for="<?php echo $menu_item->ID; ?>"><?php echo $menu_item->title; ?></label>
    <?php endforeach; ?>

    <?php foreach ($selected_items as $menu_item) : ?>
        <?php $checked = in_array($menu_item, $selected_items) ? 'checked' : ''; ?>
        <input type="checkbox" name="hide_menu_items_options_menu_item[]" id="<?php echo $menu_item; ?>" value="<?php echo $menu_item; ?>" <?php echo $checked; ?>>
        <label for="<?php echo $menu_item; ?>"><?php echo $menu_item; ?></label>
    <?php endforeach; ?>

<?php
}

function hide_menu_items_user_role_input()
{
    $user_role = esc_attr(get_option('hide_menu_items_options_user_role'));
    echo '<select name="hide_menu_items_options_user_role">';
    echo '<option value="">Select a role</option>';
    $roles = wp_roles()->get_names();
    foreach ($roles as $value => $name) {
        $selected = ($user_role === $value) ? 'selected' : '';
        echo '<option value="' . esc_attr($value) . '" ' . $selected . '>' . esc_html($name) . '</option>';
    }
    echo '</select>';
}



// Add the menu page
function hide_menu_items_add_menu_page()
{
    add_menu_page(
        'Hide Menu Items Settings',
        'Hide Menu Items',
        'manage_options',
        'hide-menu-items',
        'hide_menu_items_options_page'
    );
}
add_action('admin_menu', 'hide_menu_items_add_menu_page');

// Add the settings sections and fields
add_action('admin_init', 'hide_menu_items_settings');
