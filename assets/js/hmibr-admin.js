/**
 * Hide Menu Items by Role - Admin JavaScript
 */

jQuery(document).ready(function($) {
    function updateFieldStates() {
        // Find all login state selects and update their related fields
        $('select[name^="hmibr_login_state["]').each(function() {
            var $loginStateSelect = $(this);
            var loginState = $loginStateSelect.val();
            var isLoggedOutOnly = (loginState === 'logged_out');
            
            // Extract the item ID from the field name
            var fieldName = $loginStateSelect.attr('name');
            var matches = fieldName.match(/hmibr_login_state\[(\d+)\]/);
            if (!matches) return;
            var itemId = matches[1];
            
            // Disable/enable mode field
            var $modeSelect = $('#edit-menu-item-mode-' + itemId);
            $modeSelect.prop('disabled', isLoggedOutOnly);
            if (isLoggedOutOnly) {
                $modeSelect.closest('p').addClass('hmibr-disabled');
            } else {
                $modeSelect.closest('p').removeClass('hmibr-disabled');
            }
            
            // Disable/enable user roles field
            $('.edit-menu-item-custom[name*="menu-item-user-roles[' + itemId + ']"]').prop('disabled', isLoggedOutOnly);
            $('#edit-menu-item-user-roles-' + itemId).closest('div.field-custom').toggleClass('hmibr-disabled', isLoggedOutOnly);
        });
    }
    
    // Initialize state on page load
    updateFieldStates();
    
    // Update state when any login state changes
    $('select[name^="hmibr_login_state["]').change(function() {
        updateFieldStates();
    });
});