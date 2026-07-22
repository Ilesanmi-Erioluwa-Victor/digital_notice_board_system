/**
 * Admin JavaScript — Dashboard and management UI interactions
 *
 * Handles:
 * - Confirm dialogs for delete actions
 * - Form validation (client-side)
 * - File upload size/type checks
 * - Approve/reject confirmations
 * - Target audience type handler
 * - Analytics chart rendering
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

  // ─── Approve / Reject Confirmation ────────────────────────────────────────

  document.addEventListener('click', function (e) {
    var target = e.target.closest('.btn-approve, .btn-reject');
    if (!target) return;

    var action = target.classList.contains('btn-approve') ? 'approve' : 'reject';
    if (!confirm('Are you sure you want to ' + action + ' this notice?')) {
      e.preventDefault();
    }
  });

  // ─── Target Audience Type Handler ─────────────────────────────────────────

  document.addEventListener('change', function (e) {
    if (e.target.matches('#audience_type')) {
      var targetIdsGroup = document.getElementById('target-ids-group');
      if (targetIdsGroup) {
        targetIdsGroup.style.display = e.target.value === 'specific' ? 'block' : 'none';
      }
    }
  });

  // ─── Analytics Chart Rendering ────────────────────────────────────────────

  window.renderBarChart = function (containerId, data, labelKey, valueKey) {
    var container = document.getElementById(containerId);
    if (!container) return;

    container.classList.add('bar-chart');
    container.innerHTML = '';

    data.forEach(function (item) {
      var barItem = document.createElement('div');
      barItem.className = 'bar-item';

      var label = document.createElement('span');
      label.className = 'bar-label';
      label.textContent = item[labelKey];

      var fill = document.createElement('div');
      fill.className = 'bar-fill';

      var fillInner = document.createElement('div');
      fillInner.className = 'bar-fill-inner';
      fillInner.style.width = item[valueKey] + '%';

      var value = document.createElement('span');
      value.className = 'bar-value';
      value.textContent = item[valueKey];

      fill.appendChild(fillInner);
      barItem.appendChild(label);
      barItem.appendChild(fill);
      barItem.appendChild(value);
      container.appendChild(barItem);
    });
  };
})();
