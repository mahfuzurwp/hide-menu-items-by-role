# Technical Architecture

<cite>
**Referenced Files in This Document**
- [hide-menu-items-by-role.php](file://hide-menu-items-by-role.php)
- [functions.php](file://includes/functions.php)
- [custom-fields.php](file://includes/admin/custom-fields.php)
- [hmibr-admin.css](file://assets/css/hmibr-admin.css)
- [hmibr-admin.js](file://assets/js/hmibr-admin.js)
- [README.txt](file://README.txt)
</cite>

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
This document describes the technical architecture of the Hide Menu Items by Role plugin. It follows WordPress plugin conventions with a clear separation between core runtime logic, admin interface, and assets. The plugin uses WordPress hooks to integrate seamlessly: it registers admin UI fields for menu items, persists configuration via post meta, and filters menu output at runtime using a core filter.

The design emphasizes modularity and extensibility:
- Plugin entry point wires up asset loading and includes modules.
- Admin module adds custom fields and saves configuration.
- Core module applies visibility rules during menu rendering.

## Project Structure
The plugin is organized into logical layers:
- Root plugin file: defines metadata, constants, asset enqueueing, and bootstrap.
- Includes:
  - functions.php: runtime menu filtering engine.
  - admin/custom-fields.php: admin UI and persistence for menu item settings.
- Assets:
  - CSS and JS files loaded only on the menu editor screen.

```mermaid
graph TB
Entry["Plugin Entry<br/>hide-menu-items-by-role.php"] --> AdminUI["Admin UI & Persistence<br/>includes/admin/custom-fields.php"]
Entry --> Runtime["Runtime Filter Engine<br/>includes/functions.php"]
Entry --> Assets["Admin Assets<br/>assets/css/*.css<br/>assets/js/*.js"]
AdminUI --> Meta["Post Meta Storage<br/>(menu item meta)"]
Runtime --> Meta
```

**Diagram sources**
- [hide-menu-items-by-role.php:16-46](file://hide-menu-items-by-role.php#L16-L46)
- [custom-fields.php:94-146](file://includes/admin/custom-fields.php#L94-L146)
- [functions.php:1-49](file://includes/functions.php#L1-L49)

**Section sources**
- [hide-menu-items-by-role.php:1-54](file://hide-menu-items-by-role.php#L1-L54)
- [README.txt:10-30](file://README.txt#L10-L30)

## Core Components
- Plugin entry point:
  - Defines plugin metadata and constants.
  - Enqueues admin CSS/JS on the nav-menus screen.
  - Includes core modules and initializes the plugin lifecycle hook.
- Admin field manager:
  - Adds an “Audience Visibility” panel to each menu item.
  - Provides controls for login state, mode (hide/show), and user roles.
  - Validates and persists settings to post meta.
- Runtime menu filter:
  - Hooks into the menu object pipeline.
  - Applies login state and role-based visibility rules.
  - Returns filtered menu items for rendering.

**Section sources**
- [hide-menu-items-by-role.php:16-53](file://hide-menu-items-by-role.php#L16-L53)
- [custom-fields.php:3-146](file://includes/admin/custom-fields.php#L3-L146)
- [functions.php:1-49](file://includes/functions.php#L1-L49)

## Architecture Overview
The plugin integrates with WordPress through actions and filters:
- Action hooks:
  - admin_enqueue_scripts: load admin assets on the menu editor.
  - wp_nav_menu_item_custom_fields: render custom fields per menu item.
  - wp_update_nav_menu_item: persist menu item settings.
  - plugins_loaded: initialize plugin code.
- Filter hooks:
  - wp_nav_menu_objects: apply visibility rules to menu items at runtime.

```mermaid
sequenceDiagram
participant WP as "WordPress"
participant Entry as "Entry Point<br/>hide-menu-items-by-role.php"
participant Admin as "Admin UI<br/>custom-fields.php"
participant Store as "Post Meta"
participant Runtime as "Filter Engine<br/>functions.php"
WP->>Entry : Load plugin
Entry->>WP : add_action('admin_enqueue_scripts')
Entry->>WP : add_action('plugins_loaded')
Entry->>Admin : require_once
Entry->>Runtime : require_once
WP->>Admin : Hook 'wp_nav_menu_item_custom_fields'
Admin->>Store : get_post_meta(...)
Admin-->>WP : Render fields
WP->>Admin : Hook 'wp_update_nav_menu_item'
Admin->>Store : update_post_meta(...)
WP->>Runtime : Filter 'wp_nav_menu_objects'
Runtime->>Store : get_post_meta(...)
Runtime-->>WP : Return filtered items
```

**Diagram sources**
- [hide-menu-items-by-role.php:20-46](file://hide-menu-items-by-role.php#L20-L46)
- [custom-fields.php:94-146](file://includes/admin/custom-fields.php#L94-L146)
- [functions.php:49-49](file://includes/functions.php#L49-L49)

## Detailed Component Analysis

### Plugin Entry Point
Responsibilities:
- Define constants for plugin directory and URL.
- Conditionally enqueue admin styles and scripts on the nav-menus screen.
- Include core modules and register initialization hook.

Key behaviors:
- Asset enqueueing is scoped to the nav-menus screen to avoid unnecessary overhead elsewhere.
- Bootstrap uses a standard WordPress action to ensure modules are included early.

```mermaid
flowchart TD
Start(["Plugin Loaded"]) --> Define["Define Constants"]
Define --> Enqueue["Enqueue Admin Assets on Nav Menus Screen"]
Enqueue --> Include["Include Core Modules"]
Include --> InitHook["Register Initialization Hook"]
InitHook --> End(["Ready"])
```

**Diagram sources**
- [hide-menu-items-by-role.php:16-53](file://hide-menu-items-by-role.php#L16-L53)

**Section sources**
- [hide-menu-items-by-role.php:16-53](file://hide-menu-items-by-role.php#L16-L53)

### Admin Field Manager
Responsibilities:
- Render a collapsible “Audience Visibility” panel for each menu item.
- Provide controls for:
  - Login state: Everyone, Logged-in users only, Logged-out users only.
  - Mode: Hide from these roles or Show only to these roles.
  - User roles: Multi-select checkboxes derived from current roles.
- Persist settings to post meta upon saving a menu item.

Data model (post meta keys):
- _hmibr_login_state: one of any, logged_in, logged_out.
- _hmibr_mode: hide or show.
- _menu_item_user_roles: array of role slugs.
- _menu_item_menu_id and _menu_item_menu_name: context metadata.

Validation and sanitization:
- Roles are sanitized and validated against editable roles.
- Login state and mode values are whitelisted before saving.

Client-side behavior:
- When login state is set to “Logged-out users only,” the mode and roles fields are disabled to prevent conflicting configurations.

```mermaid
classDiagram
class AdminFields {
+renderPanel(id, item, depth, args)
+saveFields(menu_id, menu_item_db_id, menu_item_data)
}
class PostMeta {
+_hmibr_login_state
+_hmibr_mode
+_menu_item_user_roles
+_menu_item_menu_id
+_menu_item_menu_name
}
AdminFields --> PostMeta : "reads/writes"
```

**Diagram sources**
- [custom-fields.php:3-94](file://includes/admin/custom-fields.php#L3-L94)
- [custom-fields.php:97-146](file://includes/admin/custom-fields.php#L97-L146)

**Section sources**
- [custom-fields.php:3-146](file://includes/admin/custom-fields.php#L3-L146)

### Runtime Menu Filter Engine
Responsibilities:
- Intercept menu objects during rendering.
- Apply visibility rules based on:
  - Current user’s login state.
  - Selected roles and mode for each menu item.

Processing logic:
- Determine the current menu ID from arguments.
- Retrieve the current user’s roles; default to guest if not logged in.
- For each menu item:
  - Check login state rule first; exclude if mismatched.
  - If no roles are configured, keep the item.
  - Apply mode:
    - Hide mode: remove item if user has any selected role.
    - Show mode: remove item if user does not have any selected role (unless no roles selected, then show to everyone).

```mermaid
flowchart TD
Start(["Menu Rendering"]) --> GetArgs["Get Menu ID from Args"]
GetArgs --> GetUserRoles["Get Current User Roles<br/>(or 'guest' if not logged in)"]
GetUserRoles --> LoopItems["For Each Menu Item"]
LoopItems --> ReadMeta["Read Post Meta:<br/>login_state, mode, roles"]
ReadMeta --> CheckLogin{"Login State Matches?"}
CheckLogin --> |No| ExcludeItem["Exclude Item"]
CheckLogin --> |Yes| HasRoles{"Any Roles Configured?"}
HasRoles --> |No| KeepItem["Keep Item"]
HasRoles --> |Yes| ApplyMode{"Mode = Hide or Show?"}
ApplyMode --> |Hide| HideLogic["If user has any selected role -> Exclude"]
ApplyMode --> |Show| ShowLogic["If user has none of selected roles -> Exclude"]
HideLogic --> NextItem["Next Item"]
ShowLogic --> NextItem
KeepItem --> NextItem
ExcludeItem --> NextItem
NextItem --> Done(["Return Filtered Items"])
```

**Diagram sources**
- [functions.php:2-49](file://includes/functions.php#L2-L49)

**Section sources**
- [functions.php:2-49](file://includes/functions.php#L2-L49)

### Data Flow: From Admin Configuration to Runtime Filtering
```mermaid
sequenceDiagram
participant Editor as "Menu Editor"
participant Admin as "Admin Fields<br/>custom-fields.php"
participant Meta as "Post Meta"
participant Runtime as "Filter Engine<br/>functions.php"
participant Frontend as "Frontend Menu"
Editor->>Admin : Configure visibility fields
Admin->>Meta : Save _hmibr_login_state, _hmibr_mode, _menu_item_user_roles
Frontend->>Runtime : Request menu objects
Runtime->>Meta : Read saved meta for each item
Runtime-->>Frontend : Return filtered menu items
```

**Diagram sources**
- [custom-fields.php:97-146](file://includes/admin/custom-fields.php#L97-L146)
- [functions.php:2-49](file://includes/functions.php#L2-L49)

## Dependency Analysis
- Entry point depends on:
  - Admin module for UI and persistence.
  - Runtime module for filtering.
  - Assets for admin styling and interactivity.
- Admin module depends on:
  - WordPress roles API to list available roles.
  - Post meta API to read/write configuration.
- Runtime module depends on:
  - WordPress user APIs to determine current user roles.
  - Post meta API to read configuration.

```mermaid
graph LR
Entry["hide-menu-items-by-role.php"] --> Admin["includes/admin/custom-fields.php"]
Entry --> Runtime["includes/functions.php"]
Admin --> Meta["Post Meta"]
Runtime --> Meta
Entry --> Assets["assets/*"]
```

**Diagram sources**
- [hide-menu-items-by-role.php:44-46](file://hide-menu-items-by-role.php#L44-L46)
- [custom-fields.php:8-8](file://includes/admin/custom-fields.php#L8-L8)
- [functions.php:8-8](file://includes/functions.php#L8-L8)

**Section sources**
- [hide-menu-items-by-role.php:44-46](file://hide-menu-items-by-role.php#L44-L46)
- [custom-fields.php:8-8](file://includes/admin/custom-fields.php#L8-L8)
- [functions.php:8-8](file://includes/functions.php#L8-L8)

## Performance Considerations
- Early exclusion by login state reduces unnecessary role checks for items that do not match the login requirement.
- Skipping role checks when no roles are configured avoids extra processing.
- Assets are enqueued only on the nav-menus screen to minimize overhead on other admin pages.
- Using post meta keeps configuration lightweight and leverages WordPress caching mechanisms.

[No sources needed since this section provides general guidance]

## Troubleshooting Guide
Common issues and resolutions:
- Menu items not hiding as expected:
  - Verify that roles are correctly selected and saved in the menu item editor.
  - Confirm that the mode is set appropriately (hide vs show).
  - Ensure login state setting matches the intended audience.
- Settings not persisting:
  - Check that the save hook is triggered and meta keys are updated.
  - Validate that role slugs exist and are editable.
- Admin fields not appearing:
  - Confirm the plugin is active and the nav-menus screen is being used.
  - Ensure required files are included by the entry point.

**Section sources**
- [custom-fields.php:97-146](file://includes/admin/custom-fields.php#L97-L146)
- [functions.php:2-49](file://includes/functions.php#L2-L49)

## Conclusion
The Hide Menu Items by Role plugin implements a clean, hook-driven architecture aligned with WordPress best practices. The separation of concerns between the entry point, admin UI, and runtime filter enables maintainability and extensibility. Configuration is stored efficiently using post meta, and runtime filtering leverages core filters to integrate seamlessly with WordPress navigation. The modular structure supports future enhancements while keeping performance and usability in focus.

[No sources needed since this section summarizes without analyzing specific files]