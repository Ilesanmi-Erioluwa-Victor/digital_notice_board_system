/**
 * Admin JavaScript — Dashboard and management UI interactions
 *
 * Handles:
 * - Confirm dialogs for delete actions
 * - Form validation (client-side)
 * - File upload size/type checks
 */

(function () {
  'use strict';

  // ─── Confirm Delete Dialogs ───────────────────────────────────────────────

  document.addEventListener('click', function (e) {
    var target = e.target.closest('[data-confirm]');
    if (!target) return;

    var message = target.getAttribute('data-confirm') || 'Are you sure?';
    if (!confirm(message)) {
      e.preventDefault();
    }
  });

  // ─── File Upload Validation ───────────────────────────────────────────────

  document.addEventListener('change', function (e) {
    var input = e.target;
    if (!input || !input.hasAttribute('data-file-validate')) return;

    var maxSize = parseInt(input.getAttribute('data-max-size'), 10) || 5 * 1024 * 1024;
    var allowedTypes = (input.getAttribute('data-allowed-types') || 'pdf,jpg,png')
      .split(',')
      .map(function (t) { return t.trim().toLowerCase(); });

    var file = input.files && input.files[0];
    if (!file) return;

    // Check file extension
    var ext = file.name.split('.').pop().toLowerCase();
    if (allowedTypes.indexOf(ext) === -1) {
      showToast('Invalid file type. Allowed: ' + allowedTypes.join(', '), 'error');
      input.value = '';
      return;
    }

    // Check file size
    if (file.size > maxSize) {
      var sizeMB = (maxSize / (1024 * 1024)).toFixed(1);
      showToast('File too large. Maximum: ' + sizeMB + ' MB', 'error');
      input.value = '';
      return;
    }
  });

  // ─── Notice Form Validation ───────────────────────────────────────────────

  var noticeForm = document.getElementById('notice-form');
  if (noticeForm) {
    noticeForm.addEventListener('submit', function (e) {
      var title = document.getElementById('notice-title');
      var body = document.getElementById('notice-body');
      var errors = [];

      if (title && !title.value.trim()) {
        errors.push('Title is required.');
      }

      if (body && !body.value.trim()) {
        errors.push('Notice body is required.');
      }

      if (errors.length > 0) {
        e.preventDefault();
        showToast(errors.join(' '), 'error');
      }
    });
  }

  // ─── Category Form Validation ─────────────────────────────────────────────

  var categoryForm = document.getElementById('category-form');
  if (categoryForm) {
    categoryForm.addEventListener('submit', function (e) {
      var name = document.getElementById('category-name');
      if (name && !name.value.trim()) {
        e.preventDefault();
        showToast('Category name is required.', 'error');
      }
    });
  }

  // ─── User Role Change Confirmation ────────────────────────────────────────

  document.addEventListener('change', function (e) {
    if (e.target.matches('.role-select')) {
      if (!confirm('Change this user\'s role?')) {
        e.target.value = e.target.getAttribute('data-original-role');
      }
    }
  });
})();
