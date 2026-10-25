---
kind: business_term
name: Business Glossary
category: business_term
scope:
    - '**'
---

### Audience Visibility
- Definition：The collapsible card section in the WordPress nav-menu item settings panel where role-based and login-state visibility rules are configured; introduced as the native `<details>`/`<summary>` disclosure wrapper around the role checkboxes and visibility selects.

### Hide from these roles
- Definition：The default visibility mode: a menu item is visible to everyone EXCEPT the selected user roles. This is the original behavior of the plugin.

### Show only to these roles
- Definition：The newer visibility mode: a menu item is visible ONLY to the selected user roles. If no roles are selected in this mode, the item remains visible to everyone to avoid accidental hiding during setup.

### Login state visibility
- Definition：An independent visibility control available alongside role-based rules, with three states: Everyone (no restriction), Logged-in users only, and Logged-out users only. When set to 'Logged-out users only', role-based rules are skipped for performance and other fields are visually disabled.
