---
kind: configuration_system
name: Configuration System — WordPress Plugin Constants and Post Meta as Config
category: configuration_system
scope:
    - '**'
source_files:
    - hide-menu-items-by-role.php
    - includes/functions.php
    - includes/admin/custom-fields.php
---

## What system/approach is used

This repository does not implement a dedicated configuration framework. It is a small WordPress plugin that relies entirely on:

1. **PHP constants** defined at the top of the main plugin file for runtime paths.
2. **WordPress post meta** (stored in the `wp_postmeta` table) to persist per-menu-item settings such as allowed roles, visibility mode (`hide`/`show`), and login-state gating.
3. **No external config files** — there are no `.env`, YAML, TOML, JSON, or INI files; no environment-variable loader; no feature-flag service.

## Key files and packages

- `hide-menu-items-by-role.php` — main plugin bootstrap; defines constants and loads sub-modules.
- `includes/functions.php` — runtime filtering logic that reads post meta to decide which menu items to show/hide.
- `includes/admin/custom-fields.php` — admin UI that writes the same post meta keys used by the runtime filter.

## Architecture and conventions

### Constants
The main file defines two path constants early:

```php
define('HMI_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('HMI_PLUGIN_URL', plugin_dir_url(__FILE__));
```

These are used to resolve asset URLs and to `require_once` the included modules. No other application-level constants exist.

### Runtime configuration via post meta
All user-facing configuration lives as post meta attached to menu item posts. The runtime reader in `includes/functions.php` uses these meta keys:

| Meta key | Purpose | Default / fallback |
|---|---|---|
| `_hmibr_login_state` | Gating by login state: `logged_in`, `logged_out`, or `any` | `'any'` |
| `_menu_item_user_roles` | List of roles that the rule applies to | empty array (no rule) |
| `_hmibr_mode` | Whether matching roles should be hidden or shown | `'hide'` |

The filter hook `wp_nav_menu_objects` runs on every nav menu render, reads the current user's roles via `wp_get_current_user()->roles` (falling back to `['guest']` when not logged in), and removes items based on the above meta.

### Admin-side configuration
The admin panel (`includes/admin/custom-fields.php`) renders custom fields in the WordPress Nav Menus editor so editors can set those meta values. There is no separate settings page, options API usage, or network-wide configuration surface observed in this codebase.

### Initialization
The plugin hooks into `plugins_loaded` via `add_action('plugins_loaded', 'hmi_init')`; the `hmi_init()` function body is currently empty, meaning no additional configuration loading occurs at boot time.

## Conventions and constraints

- Configuration is **per-menu-item**, not global. Each menu item carries its own role/login-state/mode metadata.
- Values are read with `get_post_meta(..., true)` and use PHP truthiness/fallbacks (`?: 'any'`, `?: 'hide'`) rather than explicit validation — invalid or missing values fall through to defaults.
- Role lists are treated as arrays but are stored as whatever `get_post_meta` returns; the code defensively casts both sides to arrays before `array_intersect`.
- There is **no schema enforcement**, sanitization, or capability check around reading/writing these meta keys visible in the provided files.
- No environment variables, `.env` files, or external config formats are referenced anywhere in the repository.