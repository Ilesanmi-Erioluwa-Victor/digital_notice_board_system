/**
 * Main JavaScript — Site-wide utilities
 *
 * Handles:
 * - Hamburger menu toggle for mobile navigation
 * - Toast notification system
 * - CSRF token injection for AJAX requests
 * - Bookmark toggle
 * - Notice status/priority CSS classes
 */

(function () {
  'use strict';

  // ─── Hamburger Menu Toggle ───────────────────────────────────────────────

  const hamburger = document.getElementById('hamburger');
  const mainNav = document.getElementById('main-nav');

  if (hamburger && mainNav) {
    hamburger.addEventListener('click', function () {
      mainNav.classList.toggle('open');
      const expanded = mainNav.classList.contains('open');
      hamburger.setAttribute('aria-expanded', expanded);
      hamburger.innerHTML = expanded ? '&#10005;' : '&#9776;';
    });

    // Close menu when clicking outside
    document.addEventListener('click', function (e) {
      if (!hamburger.contains(e.target) && !mainNav.contains(e.target)) {
        mainNav.classList.remove('open');
        hamburger.setAttribute('aria-expanded', 'false');
        hamburger.innerHTML = '&#9776;';
      }
    });
  }

  // ─── Toast Notification System ────────────────────────────────────────────

  window.showToast = function (message, type) {
    type = type || 'info';
    var container = document.getElementById('toast-container');
    if (!container) {
      container = document.createElement('div');
      container.id = 'toast-container';
      container.className = 'toast-container';
      document.body.appendChild(container);
    }

    var toast = document.createElement('div');
    toast.className = 'toast toast-' + type;
    toast.textContent = message;

    container.appendChild(toast);

    // Remove toast after 4 seconds
    setTimeout(function () {
      toast.style.opacity = '0';
      toast.style.transition = 'opacity 300ms';
      setTimeout(function () {
        if (toast.parentNode) {
          toast.parentNode.removeChild(toast);
        }
      }, 300);
    }, 4000);
  };

  // ─── CSRF Token for AJAX Requests ────────────────────────────────────────

  window.getCsrfToken = function () {
    var meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.getAttribute('content') : '';
  };

  // ─── Unread Notice Count Badge ───────────────────────────────────────────
  // Tracks the latest seen notice ID in localStorage and compares it against
  // the latest notice from /api/notices/active, showing a badge count on the
  // Home nav link when there are newer notices.

  function updateUnreadBadge() {
    var badge = document.getElementById('unread-badge');
    if (!badge) return;

    var lastSeenId = parseInt(localStorage.getItem('lastSeenNoticeId'), 10) || 0;

    fetch('/api/notices/active')
      .then(function (r) { return r.json(); })
      .then(function (notices) {
        if (!notices || notices.length === 0) {
          badge.style.display = 'none';
          return;
        }

        var latestId = 0;
        notices.forEach(function (n) { latestId = Math.max(latestId, parseInt(n.id, 10)); });

        var unread = 0;
        notices.forEach(function (n) {
          if (parseInt(n.id, 10) > lastSeenId) {
            unread++;
          }
        });

        if (unread > 0) {
          badge.textContent = unread;
          badge.style.display = 'inline';
        } else {
          badge.style.display = 'none';
        }

        // Update last seen ID from the latest active notice
        if (latestId > 0) {
          localStorage.setItem('lastSeenNoticeId', String(latestId));
        }
      })
      .catch(function () {});
  }

  // Run on page load and every 60 seconds
  document.addEventListener('DOMContentLoaded', function () {
    updateUnreadBadge();
    setInterval(updateUnreadBadge, 60000);
  });

  // ─── Auto-hide flash messages ─────────────────────────────────────────────

  document.addEventListener('DOMContentLoaded', function () {
    var flashMessages = document.querySelectorAll('.flash-message');
    flashMessages.forEach(function (msg) {
      setTimeout(function () {
        msg.style.opacity = '0';
        msg.style.transition = 'opacity 300ms';
        setTimeout(function () {
          if (msg.parentNode) msg.parentNode.removeChild(msg);
        }, 300);
      }, 4000);
    });
  });

  // ─── Bookmark Toggle ──────────────────────────────────────────────────────

  document.addEventListener('click', function (e) {
    var btn = e.target.closest('.bookmark-btn');
    if (!btn || btn.classList.contains('btn-loading')) return;
    var noticeId = btn.getAttribute('data-notice-id');
    if (!noticeId) return;

    btn.classList.add('btn-loading');
    btn.disabled = true;
    var origHTML = btn.innerHTML;
    btn.innerHTML = '<span class="spinner"></span>';

    var xhr = new XMLHttpRequest();
    xhr.open('POST', '/api/notices/bookmark/' + noticeId, true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.setRequestHeader('X-CSRF-TOKEN', getCsrfToken());
    xhr.onload = function () {
      btn.classList.remove('btn-loading');
      btn.disabled = false;
      btn.innerHTML = origHTML;
      if (xhr.status >= 200 && xhr.status < 300) {
        btn.classList.toggle('active');
        showToast(btn.classList.contains('active') ? 'Bookmarked' : 'Bookmark removed', 'success');
      } else {
        showToast('Failed to toggle bookmark', 'error');
      }
    };
    xhr.onerror = function () {
      btn.classList.remove('btn-loading');
      btn.disabled = false;
      btn.innerHTML = origHTML;
      showToast('Network error', 'error');
    };
    xhr.send('_token=' + encodeURIComponent(getCsrfToken()));
  });

  // ─── Form Submit Loading States ──────────────────────────────────────────

  document.addEventListener('submit', function (e) {
    if (e.defaultPrevented) return;
    var btn = e.target.querySelector('button[type="submit"]');
    if (!btn || btn.disabled) return;
    btn.disabled = true;
    btn.classList.add('btn-loading');
    btn.setAttribute('data-orig-html', btn.innerHTML);
    btn.innerHTML = '<span class="spinner"></span> ' + btn.textContent.trim();
  });

  // ─── Notice Status Colors ─────────────────────────────────────────────────

  window.getStatusBadgeClass = function (status) {
    var map = {
      'published': 'badge-published',
      'draft': 'badge-draft',
      'archived': 'badge-archived',
      'pending': 'badge-pending',
      'approved': 'badge-approved',
      'rejected': 'badge-rejected'
    };
    return map[status] || 'badge-draft';
  };

  window.getPriorityBadgeClass = function (priority) {
    var map = {
      'high': 'badge-high',
      'medium': 'badge-medium',
      'low': 'badge-low'
    };
    return map[priority] || 'badge-normal';
  };
})();
