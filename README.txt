=== Hide Menu Items by Role ===
Contributors: @mahfuzurwp
Tags: menu, navigation, roles, user roles, hide menu items
Requires at least: 5.0
Tested up to: 7.0.0
Stable tag: 2.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

An enhanced WordPress plugin to hide or show menu items based on user roles and login status.

== Description ==

This plugin allows you to control menu item visibility based on user roles and login status. You can:

1. Show menu items to everyone, ignoring role rules (default "show to all" mode)
2. Hide menu items from specific user roles
3. Show menu items only to specific user roles
4. Control visibility based on login status (Everyone, Logged-in only, Logged-out only)

The plugin uses WordPress core filters, so no JavaScript or jQuery is required.

== Installation ==

1. Upload the plugin files to the /wp-content/plugins/ directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Create or edit a menu item, and configure the visibility settings:
   - Select which user roles should affect visibility
   - Choose "Show to all roles" (default), "Hide for selected roles", or "Show only to selected roles"
   - Set user status visibility (Everyone, Logged-in only, Logged-out only)
4. Save your menu.

== Frequently Asked Questions ==

= How do I control who can see a menu item? =

When you create or edit a menu item, new fields will appear where you can:
1. Select which user roles should affect visibility
2. Choose "Show to all roles" (default), "Hide for selected roles", or "Show only to selected roles"
3. Set user status visibility:
   - Everyone (no restriction)
   - Logged-out users only
   - Logged-in users only

= Can I hide menu items for multiple user roles? =

Yes, you can select multiple user roles that should be affected by the visibility rules.

= What do the role visibility modes do? =

- "Show to all roles" (default): Menu item is visible to everyone; role rules are ignored and the role checkboxes are disabled
- "Hide for selected roles": Menu item is visible to everyone EXCEPT the selected roles (original behavior)
- "Show only to selected roles": Menu item is visible ONLY to the selected roles
  
  Note: When using "Show only to selected roles", if no roles are selected, the item will be visible to everyone (to avoid accidentally hiding items during setup).

= How does the user status visibility work? =

The user status setting works independently of role-based rules:
- Everyone: No restriction based on login status
- Logged-out users only: Item is only visible to visitors who are not logged in
- Logged-in users only: Item is only visible to users who are logged in

If the user status rule is set to 'Logged-out users only', the role-based rules are not checked (for performance).

= How do I change the visibility settings for a menu item? =

When you create or edit a menu item, the visibility fields will appear where you can modify:
- The selected user roles
- The role visibility mode (show to all / hide for selected roles / show only to selected roles)
- The user status setting

== Screenshots ==

1. Plugin shown in plugins page.
2. Adding user roles and visibility settings in menus page.

== Changelog ==

= 2.0.0 =
* Added login state visibility control (Everyone/Logged-out only/Logged-in only)
* Added show/hide mode toggle (Hide from these roles / Show only to these roles)
* Added UI enhancements: when login state is 'Logged-out users only', other fields are visually disabled
* Major feature update with enhanced visibility controls

= 1.0.1 =
* Updated for compatibility with WordPress 6.7.1

= 1.0.0 =
* Initial release with basic role-based hiding functionality.

== Upgrade Notice ==

= 2.0.0 =
* Major update with new features: login state controls and show/hide mode toggle
* Please update to take advantage of enhanced visibility controls

= 1.0.1 =
* Compatibility update for WordPress 6.7.1

= 1.0.0 =
* Initial release.