# Role-Based Menu Visibility

<cite>
**Referenced Files in This Document**
- [hide-menu-items-by-role.php](file://hide-menu-items-by-role.php)
- [functions.php](file://includes/functions.php)
- [custom-fields.php](file://includes/admin/custom-fields.php)
- [README.txt](file://README.txt)
- [hmibr-admin.js](file://assets/js/hmibr-admin.js)
</cite>

## Update Summary
**Changes Made**
- Updated visibility modes section to include the new "Show to all roles" option as default
- Enhanced role validation documentation to reflect use of get_editable_roles()
- Improved logging-out user handling documentation
- Updated filtering engine documentation to explain the new mode handling and default behavior
- Added examples and use cases for the new visibility mode
- Updated UI behavior documentation to reflect role checkbox disabling for "Show to all roles" mode

## Table of Contents
1. [Introduction](#introduction)
2. [Project Structure](#project-structure)
3. [Core Components](#core-components)
4. [Architecture Overview](#architecture-overview)
5. [Detailed Component Analysis](#detailed-component-analysis)
6. [Dependency Analysis](#dependency-analysis)
7. [Performance Considerations](#performance-considerations)
8. [Troubleshooting Guide](#troubleshooting-guide)
9. [Conclusion](#conclusion)

## Introduction
This document explains how the plugin implements role-based menu visibility in WordPress. It covers:
- How to configure visibility rules for roles such as Administrator, Editor, Author, Contributor, and Subscriber
- The implementation of role checking logic in the menu filtering system
- Practical examples for hiding or showing menu items based on roles and login state
- Step-by-step instructions for setting up role-based access control through the admin interface
- The underlying data structure used to store role configurations
- How the filtering engine processes these settings during menu rendering

The plugin provides two complementary controls:
- Login state visibility (Everyone, Logged-in only, Logged-out only)
- Role-based visibility mode with three options: Show to all roles (default), Hide for selected roles, Show only to selected roles

## Project Structure
The plugin is organized into a main entry file, an admin UI module, and a runtime filter that applies visibility rules when menus are rendered.

```mermaid
graph TB
A["hide-menu-items-by-role.php<br/>Plugin bootstrap and asset enqueue"] --> B["includes/admin/custom-fields.php<br/>Admin UI fields and save handlers"]
A --> C["includes/functions.php<br/>Menu filtering engine"]
D["assets/css/hmibr-admin.css<br/>Admin styles"] -.-> B
E["assets/js/hmibr-admin.js<br/>Admin interactivity"] -.-> B
```

**Diagram sources**
- [hide-menu-items-by-role.php:20-46](file://hide-menu-items-by-role.php#L20-L46)
- [custom-fields.php:3-145](file://includes/admin/custom-fields.php#L3-L145)
- [functions.php:1-67](file://includes/functions.php#L1-L67)

**Section sources**
- [hide-menu-items-by-role.php:1-63](file://hide-menu-items-by-role.php#L1-L63)
- [custom-fields.php:1-145](file://includes/admin/custom-fields.php#L1-L145)
- [functions.php:1-67](file://includes/functions.php#L1-L67)

## Core Components
- Plugin bootstrap and assets: Registers admin CSS/JS for the menu editor and includes required files.
- Admin custom fields: Adds "Audience Visibility" panel with fields for login state, visibility mode, and user roles; validates and saves values using WordPress's get_editable_roles() function.
- Menu filtering engine: Hooks into WordPress menu rendering to apply visibility rules based on current user roles and login state.

Key responsibilities:
- Admin UI: Collects and persists configuration per menu item with enhanced role validation.
- Runtime filter: Applies configured rules to include or exclude menu items for the current visitor.

**Section sources**
- [hide-menu-items-by-role.php:20-46](file://hide-menu-items-by-role.php#L20-L46)
- [custom-fields.php:3-145](file://includes/admin/custom-fields.php#L3-L145)
- [functions.php:1-67](file://includes/functions.php#L1-L67)

## Architecture Overview
The plugin integrates with WordPress at two points:
- Admin side: Adds custom fields to the menu editor and saves them as post meta for each menu item.
- Frontend side: Filters menu objects during rendering to hide or show items based on saved rules and the current user's context.

```mermaid
sequenceDiagram
participant Admin as "Admin User"
participant MenuEditor as "WordPress Menu Editor"
participant CustomFields as "Custom Fields Handler"
participant Meta as "Post Meta Storage"
participant Renderer as "Menu Rendering Engine"
participant Filter as "Role Filter"
Admin->>MenuEditor : Open menu item settings
MenuEditor->>CustomFields : Render Audience Visibility panel
Admin->>CustomFields : Set login state, mode, roles
CustomFields->>Meta : Save sanitized values
Note over Meta : Stores _hmibr_login_state, _hmibr_mode, _menu_item_user_roles
Renderer->>Filter : wp_nav_menu_objects(items, args)
Filter->>Meta : Read saved values for each item
Filter->>Filter : Apply login state rule
Filter->>Filter : Apply role-based hide/show/all rule
Filter-->>Renderer : Return filtered items
```

**Diagram sources**
- [custom-fields.php:3-145](file://includes/admin/custom-fields.php#L3-L145)
- [functions.php:1-67](file://includes/functions.php#L1-L67)

## Detailed Component Analysis

### Admin Interface: Audience Visibility Panel
The admin panel adds three controls per menu item:
- Visibility (login state): Everyone, Logged-in users only, Logged-out users only
- Role Visibility Mode: Show to all roles (default), Hide for selected roles, Show only to selected roles
- Select the user roles: Checkbox list of available roles from get_editable_roles()

Behavioral notes:
- When Visibility is set to Logged-out users only, the Mode and Roles fields are disabled to prevent conflicting rules.
- When Role Visibility Mode is set to "Show to all roles", the role checkboxes are disabled since no role filtering is needed.
- New menu items default to "Show to all roles" mode for better user experience.
- The panel renders existing saved values and allows editing with enhanced validation.

```mermaid
flowchart TD
Start(["Open Menu Item Settings"]) --> Load["Load saved values:<br/>_hmibr_login_state, _hmibr_mode, _menu_item_user_roles"]
Load --> CheckMode{"Mode exists?"}
CheckMode --> |No| SetDefault["Set default to 'all' if no roles saved,<br/>else 'hide' for backward compatibility"]
CheckMode --> |Yes| UseExisting["Use existing mode"]
SetDefault --> RenderPanel["Render Audience Visibility panel"]
UseExisting --> RenderPanel
RenderPanel --> UserChoice{"User selects Visibility"}
UserChoice --> |Logged-out only| DisableFields["Disable Mode and Roles fields"]
UserChoice --> |Any or Logged-in| EnableFields["Enable Mode and Roles fields"]
EnableFields --> CheckMode{"Mode = Show to all roles?"}
CheckMode --> |Yes| DisableRoles["Disable role checkboxes"]
CheckMode --> |No| KeepRoles["Keep role checkboxes enabled"]
DisableFields --> Save["Save changes"]
DisableRoles --> Save
KeepRoles --> Save
Save --> End(["Settings Saved"])
```

**Diagram sources**
- [custom-fields.php:3-145](file://includes/admin/custom-fields.php#L3-L145)
- [hmibr-admin.js:11-25](file://assets/js/hmibr-admin.js#L11-L25)

**Section sources**
- [custom-fields.php:3-145](file://includes/admin/custom-fields.php#L3-L145)

### Role Visibility Modes
The plugin now supports three distinct visibility modes with enhanced defaults:

1. **Show to all roles** (default for new items):
   - Menu items are visible to everyone regardless of user roles
   - Role checkboxes are disabled to prevent unnecessary configuration
   - Fast path in filtering engine skips role evaluation entirely
   - Default behavior ensures new menu items work out-of-the-box

2. **Hide for selected roles**:
   - Menu items are visible to everyone EXCEPT the selected roles
   - Original behavior maintained for backward compatibility
   - Excludes items if current user has any of the selected roles

3. **Show only to selected roles**:
   - Menu items are visible ONLY to the selected roles
   - Excludes items if current user does not have any of the selected roles
   - If no roles are selected, item shows to everyone (prevents accidental hiding)

```mermaid
flowchart TD
Mode{"Role Visibility Mode"} --> All["Show to all roles<br/>(Default for new items)"]
Mode --> Hide["Hide for selected roles"]
Mode --> ShowOnly["Show only to selected roles"]
All --> ResultAll["Visible to everyone<br/>Role checkboxes disabled"]
Hide --> ResultHide["Visible to everyone except<br/>selected roles"]
ShowOnly --> ResultShow["Visible only to<br/>selected roles"]
```

**Diagram sources**
- [custom-fields.php:48-52](file://includes/admin/custom-fields.php#L48-L52)
- [functions.php:35-50](file://includes/functions.php#L35-L50)

**Section sources**
- [custom-fields.php:48-52](file://includes/admin/custom-fields.php#L48-L52)
- [functions.php:35-50](file://includes/functions.php#L35-L50)

### Saving Logic and Validation
When saving a menu item:
- Sanitizes selected roles and validates against editable roles using get_editable_roles().
- Saves:
  - `_menu_item_user_roles`: Array of selected roles
  - `_hmibr_login_state`: One of any, logged_in, logged_out
  - `_hmibr_mode`: One of all, hide, show
- Also stores menu ID and title for reference.

```mermaid
flowchart TD
Start(["Save Menu Item"]) --> Sanitize["Sanitize roles and fields"]
Sanitize --> ValidateRoles{"Validate against get_editable_roles()"}
ValidateRoles --> |Invalid| AddError["Add settings error"]
ValidateRoles --> |Valid| Persist["Persist meta:<br/>_menu_item_user_roles,<br/>_hmibr_login_state,<br/>_hmibr_mode"]
AddError --> End(["Done"])
Persist --> End
```

**Diagram sources**
- [custom-fields.php:75-145](file://includes/admin/custom-fields.php#L75-L145)

**Section sources**
- [custom-fields.php:75-145](file://includes/admin/custom-fields.php#L75-L145)

### Filtering Engine: Role Checking Logic
During menu rendering, the filter evaluates each menu item with enhanced mode handling:
- Reads saved meta for the item.
- Applies login state rule first (fast path).
- For logged-out users only, role rules are skipped entirely.
- If no roles are configured, keeps the item visible.
- Applies mode logic with three-way branching:
  - **Show to all roles**: Skip role evaluation entirely, keep item visible
  - **Hide for selected roles**: Exclude if the current user has any of the selected roles
  - **Show only to selected roles**: Exclude if the current user does not have any of the selected roles

```mermaid
flowchart TD
Start(["wp_nav_menu_objects(items, args)"]) --> GetMenuId["Get menu ID from args"]
GetMenuId --> GetUserRoles["Get current user roles or ['guest']"]
GetUserRoles --> LoopItems["For each menu item"]
LoopItems --> ReadMeta["Read meta:<br/>_hmibr_login_state, _menu_item_user_roles, _hmibr_mode"]
ReadMeta --> CheckLogin{"Apply login state rule"}
CheckLogin --> |Violates login state| RemoveItem["Remove item"]
CheckLogin --> |Passes| CheckLoggedOut{"Is logged-out only?"}
CheckLoggedOut --> |Yes| NextItem["Skip role rules, keep item"]
CheckLoggedOut --> |No| HasRoles{"Has roles configured?"}
HasRoles --> |No| NextItem["Keep item and continue"]
HasRoles --> |Yes| ComputeOverlap["Compute overlap between user roles and configured roles"]
ComputeOverlap --> Mode{"Mode = all, hide, or show?"}
Mode --> |all| NextItem["Skip role rules, keep item"]
Mode --> |hide & overlap| RemoveItem
Mode --> |show & no overlap| RemoveItem
Mode --> |otherwise| NextItem
NextItem --> LoopItems
RemoveItem --> LoopItems
LoopItems --> Done(["Return filtered items"])
```

**Diagram sources**
- [functions.php:1-67](file://includes/functions.php#L1-L67)

**Section sources**
- [functions.php:1-67](file://includes/functions.php#L1-L67)

### Data Structures and Persistence
The plugin stores three pieces of metadata per menu item:
- `_menu_item_user_roles`: Array of role slugs (e.g., administrator, editor, author, contributor, subscriber)
- `_hmibr_login_state`: Enum-like string (any, logged_in, logged_out)
- `_hmibr_mode`: Enum-like string (all, hide, show)

These values are read during rendering and compared against the current user's roles to decide inclusion/exclusion.

**Section sources**
- [custom-fields.php:75-145](file://includes/admin/custom-fields.php#L75-L145)
- [functions.php:1-67](file://includes/functions.php#L1-L67)

## Dependency Analysis
- Bootstrap file enqueues admin assets and includes the admin and functions modules.
- Admin module depends on WordPress roles API to render role checkboxes and validate inputs using get_editable_roles().
- Filter module depends on WordPress menu hooks and user functions to evaluate visibility.

```mermaid
graph LR
Bootstrap["hide-menu-items-by-role.php"] --> AdminUI["includes/admin/custom-fields.php"]
Bootstrap --> Filter["includes/functions.php"]
AdminUI --> WPAPI["WordPress Roles API<br/>(get_editable_roles)"]
Filter --> WPMenu["WordPress Menu Hook"]
Filter --> WPUser["WordPress User Functions"]
```

**Diagram sources**
- [hide-menu-items-by-role.php:20-46](file://hide-menu-items-by-role.php#L20-L46)
- [custom-fields.php:3-145](file://includes/admin/custom-fields.php#L3-L145)
- [functions.php:1-67](file://includes/functions.php#L1-L67)

**Section sources**
- [hide-menu-items-by-role.php:20-46](file://hide-menu-items-by-role.php#L20-L46)
- [custom-fields.php:3-145](file://includes/admin/custom-fields.php#L3-L145)
- [functions.php:1-67](file://includes/functions.php#L1-L67)

## Performance Considerations
- Login state check runs before role checks, providing a fast path for simple cases.
- If no roles are configured for an item, the filter skips role evaluation.
- **"Show to all roles" mode provides optimal performance** by completely skipping role evaluation logic.
- Avoid selecting too many roles unnecessarily; keep rules specific to improve clarity and performance.
- Enhanced role validation using get_editable_roles() ensures efficient role checking.

## Troubleshooting Guide
Common issues and resolutions:
- Menu item still visible to unintended roles:
  - Verify the Role Visibility Mode and selected roles.
  - Confirm login state is set appropriately (e.g., Logged-in only vs. Everyone).
  - Check if mode is set to "Show to all roles" which bypasses role rules.
- Field grayed out in admin:
  - When Visibility is set to Logged-out users only, Mode and Roles are intentionally disabled.
  - When Role Visibility Mode is set to "Show to all roles", role checkboxes are disabled.
- No change after saving:
  - Ensure you saved the menu and cleared any caching layers.
- Unexpected behavior for guests:
  - Guests are treated as having no roles; ensure your rules account for this by using appropriate login state settings.
- New menu items not working as expected:
  - New items default to "Show to all roles" mode; adjust the mode if you need role-based restrictions.
- Invalid role errors:
  - Role validation now uses WordPress's get_editable_roles() function; ensure roles are properly registered.

**Section sources**
- [custom-fields.php:11-15](file://includes/admin/custom-fields.php#L11-L15)
- [hmibr-admin.js:14-24](file://assets/js/hmibr-admin.js#L14-L24)
- [functions.php:35-50](file://includes/functions.php#L35-L50)

## Conclusion
The plugin provides a straightforward, extensible approach to controlling menu visibility with three distinct modes:
- Configure login state and role-based rules via the admin panel
- Store settings as post meta for each menu item
- Apply rules efficiently during menu rendering with optimized performance for common scenarios
- Enhanced role validation using WordPress's built-in functions for better security and reliability

Use these capabilities to implement member-only navigation, admin-exclusive menus, public-facing content organization, and universal visibility with clear, maintainable rules.

## Appendices

### Step-by-Step: Setting Up Role-Based Access Control
1. Install and activate the plugin.
2. Navigate to Appearance > Menus.
3. Open a menu item's settings and expand Audience Visibility.
4. Choose User Status:
   - Everyone
   - Logged-in users only
   - Logged-out users only
5. Choose Role Visibility Mode:
   - Show to all roles (default for new items)
   - Hide for selected roles
   - Show only to selected roles
6. Select one or more roles (Administrator, Editor, Author, Contributor, Subscriber).
7. Save the menu.

**Section sources**
- [README.txt:38-42](file://README.txt#L38-L42)
- [custom-fields.php:3-145](file://includes/admin/custom-fields.php#L3-L145)

### Practical Examples

- Universal visibility (new default):
  - User Status: Everyone
  - Mode: Show to all roles (default)
  - Result: Item visible to everyone, role checkboxes disabled

- Member-only navigation:
  - User Status: Logged-in users only
  - Mode: Show only to selected roles
  - Roles: Subscriber, Author, Editor, Administrator
  - Result: Only logged-in members see the item.

- Admin-exclusive menus:
  - User Status: Logged-in users only
  - Mode: Show only to selected roles
  - Roles: Administrator
  - Result: Only administrators see the item.

- Public-facing content organization:
  - User Status: Everyone
  - Mode: Hide for selected roles
  - Roles: Contributor
  - Result: Everyone sees the item except Contributors.

- Guest-only links:
  - User Status: Logged-out users only
  - Result: Only visitors who are not logged in see the item.

- Mixed audience content:
  - User Status: Everyone
  - Mode: Hide for selected roles
  - Roles: Subscriber
  - Result: Everyone sees the item except subscribers.

**Section sources**
- [functions.php:1-67](file://includes/functions.php#L1-L67)
- [custom-fields.php:3-145](file://includes/admin/custom-fields.php#L3-L145)
- [README.txt:44-83](file://README.txt#L44-L83)