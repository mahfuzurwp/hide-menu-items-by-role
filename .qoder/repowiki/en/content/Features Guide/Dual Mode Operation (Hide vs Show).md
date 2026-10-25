# Dual Mode Operation (Hide vs Show)

<cite>
**Referenced Files in This Document**
- [hide-menu-items-by-role.php](file://hide-menu-items-by-role.php)
- [functions.php](file://includes/functions.php)
- [custom-fields.php](file://includes/admin/custom-fields.php)
- [README.txt](file://README.txt)
</cite>

## Update Summary
**Changes Made**
- Updated terminology from "dual mode" to "triple mode operation" to reflect the new third mode
- Added documentation for the new "Show to all roles" mode that disables role restrictions
- Updated examples and diagrams to show three modes instead of two
- Revised data storage format documentation to include the new 'all' mode value
- Enhanced guidance section with recommendations for choosing between the three modes

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
This document explains the triple mode operation that supports "show to all roles", "hide from roles", and "show only to roles" logic for WordPress menu items. It details how mode selection affects role configuration interpretation, resulting visibility behavior, internal switching logic, data storage format, consistency across menu items, and guidance for choosing the appropriate mode. It also addresses conflicts and edge cases when mixing modes within the same menu structure.

## Project Structure
The plugin is organized into a main entry file, core filtering logic, admin custom fields for menu configuration, assets, and documentation:
- Main plugin bootstrap and asset enqueueing
- Core filter hook that evaluates visibility per menu item
- Admin UI to configure login state, mode, and roles per menu item
- Documentation describing features and usage

```mermaid
graph TB
A["hide-menu-items-by-role.php<br/>Plugin bootstrap & assets"] --> B["includes/functions.php<br/>Menu visibility filter"]
A --> C["includes/admin/custom-fields.php<br/>Admin UI & save handlers"]
D["assets/css/hmibr-admin.css"] -.-> A
E["assets/js/hmibr-admin.js"] -.-> A
F["README.txt<br/>User-facing docs"] -.-> A
```

**Diagram sources**
- [hide-menu-items-by-role.php:20-46](file://hide-menu-items-by-role.php#L20-L46)
- [functions.php:1-56](file://includes/functions.php#L1-L56)
- [custom-fields.php:1-126](file://includes/admin/custom-fields.php#L1-L126)

**Section sources**
- [hide-menu-items-by-role.php:1-58](file://hide-menu-items-by-role.php#L1-L58)
- [functions.php:1-56](file://includes/functions.php#L1-L56)
- [custom-fields.php:1-126](file://includes/admin/custom-fields.php#L1-L126)
- [README.txt:10-63](file://README.txt#L10-L63)

## Core Components
- Plugin bootstrap: registers admin assets on the menu editor screen and includes required files.
- Admin custom fields: adds an "Audience Visibility" panel with fields for login state, mode (show to all/hide/show), and user roles; saves these values as post meta for each menu item.
- Visibility filter: runs during menu rendering to decide whether to show or hide each menu item based on current user context, login state, mode, and selected roles.

Key responsibilities:
- Data capture and persistence of mode and roles per menu item.
- Runtime evaluation of visibility using the stored mode and roles.
- Early exit optimization when login state alone resolves visibility.

**Section sources**
- [hide-menu-items-by-role.php:20-46](file://hide-menu-items-by-role.php#L20-L46)
- [custom-fields.php:3-72](file://includes/admin/custom-fields.php#L3-L72)
- [custom-fields.php:74-126](file://includes/admin/custom-fields.php#L74-L126)
- [functions.php:1-56](file://includes/functions.php#L1-L56)

## Architecture Overview
The system uses a classic WordPress pattern:
- Admin UI writes configuration to post meta for each menu item.
- The runtime filter reads this meta and applies rules to the menu object list before output.

```mermaid
sequenceDiagram
participant Admin as "Admin UI"
participant Meta as "Post Meta Storage"
participant Filter as "wp_nav_menu_objects Filter"
participant Menu as "Menu Rendering"
Admin->>Meta : Save _hmibr_login_state, _hmibr_mode, _menu_item_user_roles
Note over Admin,Meta : User selects visibility mode and roles in menu editor
Menu->>Filter : Request menu objects
Filter->>Meta : Read meta for each menu item
Filter->>Filter : Apply login state rule
Filter->>Filter : Apply mode + roles rule
Filter-->>Menu : Return filtered menu objects
```

**Diagram sources**
- [custom-fields.php:74-126](file://includes/admin/custom-fields.php#L74-L126)
- [functions.php:1-56](file://includes/functions.php#L1-L56)

## Detailed Component Analysis

### Triple Mode Logic: Show to All vs Hide vs Show
The visibility decision follows a multi-stage process:
1. Login state check: If the login state rule excludes the current visitor, the item is hidden immediately.
2. Role-based mode check:
   - Show to all roles ("Show to all roles"): Skip role checking entirely, item remains visible regardless of roles.
   - Hide mode ("Hide for selected roles"): Hide the item if the current user has any of the configured roles.
   - Show mode ("Show only to selected roles"): Hide the item unless the current user has at least one of the configured roles. If no roles are configured in show mode, the item remains visible to everyone.

```mermaid
flowchart TD
Start(["Start per-item evaluation"]) --> LoginCheck["Read login state<br/>(any/logged_in/logged_out)"]
LoginCheck --> LoggedInOnly{"logged_in but not logged in?"}
LoggedInOnly --> |Yes| HideItem["Hide item"] --> NextItem["Next item"]
LoggedInOnly --> |No| LoggedOutOnly{"logged_out but logged in?"}
LoggedOutOnly --> |Yes| HideItem2["Hide item"] --> NextItem
LoggedOutOnly --> |No| HasRoles{"Any roles saved?"}
HasRoles --> |No| KeepItem["Keep item"] --> NextItem
HasRoles --> |Yes| ReadMode["Read mode<br/>(all/hide/show)"]
ReadMode --> ModeAll{"mode == 'all'?"}
ModeAll --> |Yes| KeepItem2["Skip role checks,<br/>keep item"] --> NextItem
ModeAll --> |No| ModeHide{"mode == 'hide'?"}
ModeHide --> |Yes| OverlapHide{"User role overlaps configured roles?"}
OverlapHide --> |Yes| HideItem3["Hide item"] --> NextItem
OverlapHide --> |No| KeepItem3["Keep item"] --> NextItem
ModeHide --> |No| ModeShow{"mode == 'show'?"}
ModeShow --> |Yes| OverlapShow{"User role overlaps configured roles?"}
OverlapShow --> |No| HideItem4["Hide item"] --> NextItem
OverlapShow --> |Yes| KeepItem4["Keep item"] --> NextItem
ModeShow --> |No| KeepItem5["Keep item"] --> NextItem
```

**Diagram sources**
- [functions.php:15-51](file://includes/functions.php#L15-L51)

#### Examples: Identical Role Settings, Different Modes
Assume the same set of roles is configured for a menu item. The outcome depends on the selected mode:
- Show to all roles mode with roles [editor]:
  - Editor sees the item as visible.
  - Subscriber sees the item as visible.
  - Role settings are ignored completely.
- Hide mode with roles [editor]:
  - Editor sees the item as hidden.
  - Subscriber sees the item as visible.
- Show mode with roles [editor]:
  - Editor sees the item as visible.
  - Subscriber sees the item as hidden.

These examples illustrate that the same role configuration can produce different visibility outcomes depending on the chosen mode, with "show to all roles" providing the broadest visibility.

**Section sources**
- [functions.php:32-51](file://includes/functions.php#L32-L51)
- [README.txt:48-66](file://README.txt#L48-L66)

### Internal Switching Logic and Filtering Algorithm
- The mode is read from post meta `_hmibr_mode`, defaulting to `all` for new items without existing role configurations, or `hide` for items that already have roles saved.
- The algorithm first checks for the 'all' mode and skips role processing entirely if detected.
- For other modes, it computes overlap between the current user's roles and the configured roles for the item.
- In hide mode, overlap means hide; in show mode, lack of overlap means hide (unless no roles are configured).

```mermaid
flowchart TD
A["Get mode from meta<br/>(_hmibr_mode)"] --> B{"mode == 'all'?"}
B --> |Yes| C["Skip role processing,<br/>keep item visible"]
B --> |No| D["Compute overlap<br/>(user roles ∩ configured roles)"]
D --> E{"mode == 'hide'?"}
E --> |Yes| F{"overlap?"}
F --> |Yes| G["Hide item"]
F --> |No| H["Show item"]
E --> |No| I{"mode == 'show'?"}
I --> |Yes| J{"overlap?"}
J --> |Yes| K["Show item"]
J --> |No| L["Hide item"]
I --> |No| M["Show item"]
```

**Diagram sources**
- [functions.php:32-51](file://includes/functions.php#L32-L51)

**Section sources**
- [functions.php:32-51](file://includes/functions.php#L32-L51)

### Data Storage Format and Consistency
Per menu item, the plugin stores:
- `_hmibr_login_state`: One of `any`, `logged_in`, `logged_out`.
- `_hmibr_mode`: One of `all`, `hide`, `show`.
- `_menu_item_user_roles`: Array of role slugs.

Consistency mechanisms:
- Validation ensures only allowed values are saved for login state and mode.
- Roles are validated against editable roles before saving.
- Defaults are applied when values are missing: login state defaults to `any`, mode defaults to `all` for new items without existing roles, or `hide` for items with existing roles.

```mermaid
erDiagram
MENU_ITEM {
int id PK
string title
}
META_LOGIN_STATE {
int menu_item_id FK
enum value "any|logged_in|logged_out"
}
META_MODE {
int menu_item_id FK
enum value "all|hide|show"
}
META_ROLES {
int menu_item_id FK
string role_slug
}
MENU_ITEM ||--o{ META_LOGIN_STATE : "has"
MENU_ITEM ||--o{ META_MODE : "has"
MENU_ITEM ||--o{ META_ROLES : "has"
```

**Diagram sources**
- [custom-fields.php:107-126](file://includes/admin/custom-fields.php#L107-L126)
- [custom-fields.php:74-106](file://includes/admin/custom-fields.php#L74-L106)

**Section sources**
- [custom-fields.php:74-126](file://includes/admin/custom-fields.php#L74-L126)
- [functions.php:16-35](file://includes/functions.php#L16-L35)

### Choosing the Appropriate Mode
Use "show to all roles" mode when you want maximum visibility with no role restrictions:
- Example: Show general navigation links to everyone regardless of their role.

Use hide mode when you want broad visibility with exceptions:
- Example: Show most content to everyone except specific roles (e.g., hide sensitive links from editors).

Use show mode when you want restricted access:
- Example: Show administrative links only to administrators or editors.

Guidance:
- Prefer "show to all roles" for public-facing content that should be accessible to everyone.
- Prefer hide mode for "default-visible with exclusions."
- Prefer show mode for "default-hidden with inclusions."
- When using show mode without selecting any roles, the item will be visible to everyone by design to avoid accidental hiding during setup.

**Section sources**
- [README.txt:48-66](file://README.txt#L48-L66)
- [functions.php:44-51](file://includes/functions.php#L44-L51)

### Mixing Modes Within the Same Menu
It is safe to mix all three modes across different menu items in the same menu because the evaluation is performed per item. However, consider:
- Cognitive load: Users may confuse why some items follow exclusion logic while others follow inclusion logic or show to all.
- Maintenance: Clearly document intended behavior for each item.
- Edge case: Ensure consistent expectations around empty role sets in show mode (visible to all) and the 'all' mode (role settings ignored).

Best practices:
- Use "show to all roles" for general audience items that should be universally visible.
- Use hide mode for general audience items with a few exceptions.
- Use show mode for privileged sections requiring explicit role membership.
- Avoid overly complex combinations that could lead to confusion; group related items under consistent modes where possible.

[No sources needed since this section provides general guidance]

## Dependency Analysis
The plugin's runtime dependency chain:
- Admin UI writes meta via custom field save handler.
- The visibility filter reads meta and applies rules.
- Assets are enqueued only on the menu editor screen.

```mermaid
graph LR
UI["Admin UI<br/>(custom-fields.php)"] --> META["Post Meta"]
META --> FILTER["Visibility Filter<br/>(functions.php)"]
BOOT["Bootstrap<br/>(hide-menu-items-by-role.php)"] --> ASSETS["Admin Assets"]
BOOT --> INCLUDES["Include functions & custom fields"]
```

**Diagram sources**
- [hide-menu-items-by-role.php:20-46](file://hide-menu-items-by-role.php#L20-L46)
- [custom-fields.php:74-126](file://includes/admin/custom-fields.php#L74-L126)
- [functions.php:1-56](file://includes/functions.php#L1-L56)

**Section sources**
- [hide-menu-items-by-role.php:20-46](file://hide-menu-items-by-role.php#L20-L46)
- [custom-fields.php:74-126](file://includes/admin/custom-fields.php#L74-L126)
- [functions.php:1-56](file://includes/functions.php#L1-L56)

## Performance Considerations
- Early login-state checks short-circuit further processing when login state alone determines visibility, reducing overhead.
- The 'all' mode provides an additional performance benefit by skipping role processing entirely.
- Role overlap computation is minimal and performed only when necessary (roles are present, login state does not resolve visibility, and mode is not 'all').
- Asset enqueueing is limited to the menu editor screen to avoid unnecessary loading elsewhere.

Recommendations:
- Use "show to all roles" mode for items that don't need role-based restrictions to improve performance.
- Keep role lists concise and relevant.
- Avoid excessive nested menus with many conditional items if performance becomes a concern.

**Section sources**
- [functions.php:15-24](file://includes/functions.php#L15-L24)
- [functions.php:35-38](file://includes/functions.php#L35-L38)
- [hide-menu-items-by-role.php:20-41](file://hide-menu-items-by-role.php#L20-L41)

## Troubleshooting Guide
Common issues and resolutions:
- Item unexpectedly hidden:
  - Check login state setting; ensure it matches the intended audience.
  - Verify mode and roles; confirm whether hide or show mode aligns with intent.
  - Check if mode is set to 'all' - this would skip role checking entirely.
- Item unexpectedly visible:
  - In show mode, verify that at least one configured role matches the current user.
  - Remember that show mode with no roles selected shows the item to everyone.
  - Check if mode is set to 'all' - this ignores role settings completely.
- Fields disabled in admin:
  - When login state is set to "Logged-out users only," the mode and roles fields are intentionally disabled to prevent conflicting configurations.

Validation and defaults:
- Only allowed values are accepted for login state and mode; invalid inputs are normalized to defaults.
- Roles are validated against editable roles before saving.
- New menu items default to "show to all roles" mode unless they already have saved roles.

**Section sources**
- [custom-fields.php:63-90](file://includes/admin/custom-fields.php#L63-L90)
- [custom-fields.php:107-126](file://includes/admin/custom-fields.php#L107-L126)
- [functions.php:15-51](file://includes/functions.php#L15-L51)
- [README.txt:55-66](file://README.txt#L55-L66)

## Conclusion
The plugin's triple mode operation enables flexible control over menu visibility:
- "Show to all roles" mode ignores role restrictions and shows items to everyone.
- Hide mode excludes specified roles from seeing an item.
- Show mode includes only specified roles to see an item.
The mode selection directly changes how role configurations are interpreted, allowing identical role settings to yield different visibility outcomes. The system persists mode and roles per menu item and applies them consistently during rendering. By understanding the internal logic and choosing the appropriate mode for your use case, you can implement clear, maintainable visibility rules across your menus.