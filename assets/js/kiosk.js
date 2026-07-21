/**
 * Kiosk Display Mode
 *
 * Full-screen auto-cycling display of active notices designed for a
 * physically mounted screen. Rotates through notices every 8-10 seconds
 * with a fade transition. Displays a live clock in the corner.
 */

(function () {
  'use strict';

  var ROTATION_INTERVAL = 9000; // 9 seconds — matches KIOSK_ROTATION_INTERVAL config
  var POLL_INTERVAL = 30000;    // Refresh notice list every 30s
  var notices = [];
  var currentIndex = 0;
  var rotationTimer = null;

  var kioskTitle = document.getElementById('kiosk-title');
  var kioskContent = document.getElementById('kiosk-content');
  var kioskClock = document.getElementById('kiosk-clock');

  /**
   * Update the clock display every second.
   */
  function updateClock() {
    if (!kioskClock) return;
    var now = new Date();
    var dateStr = now.toLocaleDateString('en-US', {
      weekday: 'long',
      year: 'numeric',
      month: 'long',
      day: 'numeric',
    });
    var timeStr = now.toLocaleTimeString('en-US', {
      hour: '2-digit',
      minute: '2-digit',
      second: '2-digit',
    });
    kioskClock.textContent = dateStr + ' | ' + timeStr;
  }

  /**
   * Fetch notices from the API.
   * @returns {Promise<Array>}
   */
  function fetchNotices() {
    return fetch('/api/notices/active')
      .then(function (r) {
        if (!r.ok) throw new Error('Fetch failed');
        return r.json();
      })
      .catch(function (err) {
        console.error('Kiosk fetch error:', err);
        return [];
      });
  }

  /**
   * Show the notice at the given index with a fade transition.
   * @param {number} index
   */
  function showNotice(index) {
    if (!notices.length || !kioskContent || !kioskTitle) return;

    var notice = notices[index % notices.length];
    if (!notice) return;

    // Fade out, update, fade in
    kioskContent.style.opacity = '0';
    kioskTitle.style.opacity = '0';

    setTimeout(function () {
      kioskTitle.textContent = notice.title || 'Notice';
      kioskContent.innerHTML = (notice.body || '')
        .replace(/\n/g, '<br>');

      if (notice.priority === 'urgent') {
        kioskTitle.style.color = '#fca5a5';
      } else {
        kioskTitle.style.color = '#ffffff';
      }

      kioskContent.style.opacity = '1';
      kioskTitle.style.opacity = '1';
    }, 300);
  }

  /**
   * Advance to the next notice.
   */
  function nextNotice() {
    if (notices.length === 0) return;
    currentIndex = (currentIndex + 1) % notices.length;
    showNotice(currentIndex);
  }

  /**
   * Update the notice list from the API and reset the rotation.
   */
  function refreshNotices() {
    fetchNotices().then(function (data) {
      if (data && data.length > 0) {
        notices = data;
        currentIndex = 0;
        showNotice(0);
      } else {
        notices = [];
        kioskTitle.textContent = 'No Active Notices';
        kioskContent.innerHTML =
          '<p>Check back later for new announcements.</p>';
      }
    });
  }

  /**
   * Initialize the kiosk display.
   */
  function init() {
    // Start the clock
    updateClock();
    setInterval(updateClock, 1000);

    // Fetch initial notices, then poll every 30s
    refreshNotices();
    setInterval(refreshNotices, POLL_INTERVAL);

    // Start rotation timer
    rotationTimer = setInterval(nextNotice, ROTATION_INTERVAL);

    // Keyboard navigation (for testing)
    document.addEventListener('keydown', function (e) {
      if (e.key === 'ArrowRight' || e.key === 'ArrowDown') {
        nextNotice();
        // Reset rotation timer
        clearInterval(rotationTimer);
        rotationTimer = setInterval(nextNotice, ROTATION_INTERVAL);
      } else if (e.key === 'ArrowLeft' || e.key === 'ArrowUp') {
        currentIndex =
          (currentIndex - 1 + notices.length) % notices.length;
        showNotice(currentIndex);
        clearInterval(rotationTimer);
        rotationTimer = setInterval(nextNotice, ROTATION_INTERVAL);
      }
    });
  }

  // Start when the DOM is ready
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
