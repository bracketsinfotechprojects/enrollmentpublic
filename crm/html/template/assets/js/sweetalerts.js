/**
 * Legacy theme file: ui-sweetalerts.html demo bindings lived here and called
 * addEventListener on #sweetalert-info etc. Those elements do not exist on CRM
 * pages, which caused: "Cannot read properties of null (reading 'addEventListener')".
 *
 * SweetAlert2 is loaded globally from CDN in includes/footer_includes.php.
 * Swal.fire(...) works without this file.
 */
(function () {
  'use strict';
})();
