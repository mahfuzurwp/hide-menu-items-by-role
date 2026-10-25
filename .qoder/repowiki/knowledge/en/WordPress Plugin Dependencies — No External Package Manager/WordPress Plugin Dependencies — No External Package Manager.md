---
kind: dependency_management
name: WordPress Plugin Dependencies — No External Package Manager
category: dependency_management
scope:
    - '**'
source_files:
    - hide-menu-items-by-role.php
    - README.txt
    - .gitignore
---

## Approach

This repository is a standalone WordPress plugin with **no external dependency management system**. There are no `package.json`, `composer.json`, `go.mod`, lockfiles, or vendor directories. The plugin declares its only dependencies inline in the PHP plugin header and in the README:

- **WordPress core**: declared via `Requires at least: 5.0` in both `hide-menu-items-by-role.php` (line 7) and `README.txt` (line 4), with `Tested up to: 7.0.0` in `README.txt` (line 5).
- **PHP runtime**: declared as `Requires PHP: 7.0` in the plugin header (line 8).
- **jQuery**: loaded as a dependency of the admin script via `wp_enqueue_script(..., array('jquery'), ...)` in `hide-menu-items-by-role.php` (line 36), relying on WordPress core's bundled jQuery rather than an external package.

## Key Files

- `hide-menu-items-by-role.php` — main plugin bootstrap; contains all dependency declarations (plugin header metadata, asset versioning, and `require_once` for internal files `includes/functions.php` and `includes/admin/custom-fields.php`).
- `README.txt` — WordPress.org plugin directory metadata; carries the canonical `Requires at least` / `Tested up to` / `Stable tag` fields that drive compatibility checks on wordpress.org.
- `.gitignore` — ignores `node_modules/`, `npm-debug.log`, `yarn-error.log`, `composer.lock`, `dist/`, `build/`, indicating that while development tooling may have been used at some point, none of its artifacts are committed.

## Conventions Observed

1. **No third-party libraries are vendored or installed** — the entire codebase under `assets/css`, `assets/js`, `includes/`, and the root PHP file is self-contained.
2. **Version pinning is manual and duplicated**: the plugin version `2.0.0` appears in the PHP header (`Version:` line 6), the CSS/JS enqueue calls (lines 30, 37), and the `README.txt` (`Stable tag: 2.0.0`, line 6). There is no single source of truth.
3. **Asset versioning** uses the plugin version string directly in `wp_enqueue_style` / `wp_enqueue_script` calls, so cache busting is tied to the release number.
4. **Internal file loading** uses absolute `require_once` against `HMI_PLUGIN_DIR` and `plugin_dir_path(__FILE__)`; there is no autoloader or PSR-4 mapping.
5. **Development tooling is excluded from the repo**: `.gitignore` lists `node_modules/`, `npm-debug.log`, `yarn-error.log`, `composer.lock`, `dist/`, and `build/`, confirming that any prior npm/composer usage is not part of the distributed artifact.

## Constraints Enforced by the Platform

- WordPress.org's plugin directory validator enforces the `Requires at least` / `Tested up to` fields in `README.txt` and the plugin header; these are the effective compatibility constraints for distribution.
- The plugin's runtime depends on WordPress core APIs (`get_current_screen`, `is_object`, `wp_enqueue_style`, `wp_enqueue_script`, `add_action`, `plugin_dir_path`, `plugin_dir_url`) — upgrading WordPress beyond what is marked `Tested up to` is outside the scope of this repo's guarantees.