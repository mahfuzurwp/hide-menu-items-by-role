/**
 * Hide Menu Items by Role - Admin JavaScript
 */

jQuery(document).ready(function($) {
    /**
     * Sync the locked state of the mode and role fields for one menu item, and
     * show or hide the "no roles selected" reminder.
     *
     * @param {string} itemId Menu item database ID.
     */
    function updateItemFields(itemId) {
        var $loginState = $('#edit-menu-item-login-state-' + itemId);
        var $mode = $('#edit-menu-item-mode-' + itemId);
        var isLoggedOutOnly = ($loginState.val() === 'logged_out');
        var isAllRoles = ($mode.val() === 'all');

        // The mode field is only locked while visibility is "Logged-out users only".
        $mode.prop('disabled', isLoggedOutOnly);
        $mode.closest('p').toggleClass('hmibr-disabled', isLoggedOutOnly);

        // Roles are locked for logged-out only, and when the item is shown to all roles.
        var rolesDisabled = isLoggedOutOnly || isAllRoles;
        var $roleBoxes = $('.edit-menu-item-custom[name*="menu-item-user-roles[' + itemId + ']"]');
        $roleBoxes.prop('disabled', rolesDisabled);
        $('#edit-menu-item-user-roles-' + itemId).toggleClass('hmibr-disabled', rolesDisabled);

        // Remind editors that an active role rule does nothing until a role is picked.
        var hasSelectedRole = $roleBoxes.filter(':checked').length > 0;
        $('#edit-menu-item-user-roles-hint-' + itemId).prop('hidden', rolesDisabled || hasSelectedRole);
    }

    /**
     * Apply the field states to every menu item on the page.
     */
    function updateAllItemFields() {
        $('select[name^="hmibr_login_state["]').each(function() {
            var matches = $(this).attr('name').match(/hmibr_login_state\[(\d+)\]/);
            if (matches) {
                updateItemFields(matches[1]);
            }
        });
    }

    // Update the item whenever its user status, role visibility, or roles change.
    $(document).on('change', 'select[name^="hmibr_login_state["], select[name^="hmibr_mode["], input[name^="menu-item-user-roles["]', function() {
        var matches = $(this).attr('name').match(/\[(\d+)\]/);
        if (matches) {
            updateItemFields(matches[1]);
        }
    });

    // Initialize existing items, and items added through the menu editor.
    updateAllItemFields();
    $(document).on('menu-item-added', updateAllItemFields);
});
