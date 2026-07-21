/**
 * Main JavaScript — Site-wide utilities
 *
 * Handles:
 * - Hamburger menu toggle for mobile navigation
 * - Toast notification system
 * - CSRF token injection for AJAX requests
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
})();
