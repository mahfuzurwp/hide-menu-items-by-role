---
kind: error_handling
name: WordPress Settings API Error Messages for Menu Editor Validation
category: error_handling
scope:
    - '**'
source_files:
    - includes/admin/custom-fields.php
---

This WordPress plugin has minimal error handling. There is no custom exception hierarchy, no sentinel error constants, no `try`/`catch`, no `wp_die()` calls, and no structured logging. The only error-related code is in the admin validation path for the menu editor's custom fields.

**Key file:** `includes/admin/custom-fields.php`

The validation logic builds an array of user-facing messages (prefixed with `__('Error: ...', 'hmibyrole')`) when an invalid user role is selected during save. After validation, it pushes those messages into WordPress's settings error stack via:

```
add_settings_error('menu_editor', 'menu_editor_error', implode('<br>', $messages), 'error');
```

This uses the standard WordPress Settings API mechanism (`add_settings_error` / `settings_errors`) so that the admin UI renders the errors inline on the Nav Menus screen. The text domain used throughout is `hmibyrole`.

No other PHP files in the plugin (`hide-menu-items-by-role.php`, `includes/functions.php`) contain error handling — they rely on WordPress core to handle malformed input or missing data silently (e.g., `get_post_meta` returning empty/false values are treated as defaults). There is no global error handler, no middleware, and no panic/recover equivalent.