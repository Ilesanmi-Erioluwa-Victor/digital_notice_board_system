(function () {
  'use strict';

  var ROTATION_INTERVAL = 9000;
  var POLL_INTERVAL = 30000;
  var notices = [];
  var currentIndex = 0;
  var rotationTimer = null;

  var kioskTitle = document.getElementById('kiosk-title');
  var kioskContent = document.getElementById('kiosk-content');
  var kioskClock = document.getElementById('kiosk-clock');

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

  function showNotice(index) {
    if (!notices.length || !kioskContent || !kioskTitle) return;

    var notice = notices[index % notices.length];
    if (!notice) return;

    kioskContent.style.opacity = '0';
    kioskTitle.style.opacity = '0';

    setTimeout(function () {
      kioskTitle.textContent = notice.title || 'Notice';

      if (notice.priority === 'high') {
        kioskTitle.style.color = '#fca5a5';
      } else if (notice.priority === 'medium') {
        kioskTitle.style.color = '#fcd34d';
      } else {
        kioskTitle.style.color = '#ffffff';
      }

      var priorityClass = 'priority-low';
      if (notice.priority === 'high') priorityClass = 'priority-high';
      else if (notice.priority === 'medium') priorityClass = 'priority-medium';

      kioskContent.innerHTML =
        '<div class="' + priorityClass + '">' +
        (notice.body || '').replace(/\n/g, '<br>') +
        '</div>';

      kioskContent.style.opacity = '1';
      kioskTitle.style.opacity = '1';
    }, 300);
  }

  function nextNotice() {
    if (notices.length === 0) return;
    currentIndex = (currentIndex + 1) % notices.length;
    showNotice(currentIndex);
  }

  function refreshNotices() {
    fetchNotices().then(function (data) {
      if (data && data.length > 0) {
        var pinned = data.filter(function (n) { return n.is_pinned; });
        var unpinned = data.filter(function (n) { return !n.is_pinned; });
        notices = pinned.concat(unpinned);
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

  function init() {
    updateClock();
    setInterval(updateClock, 1000);

    refreshNotices();
    setInterval(refreshNotices, POLL_INTERVAL);

    rotationTimer = setInterval(nextNotice, ROTATION_INTERVAL);

    document.addEventListener('keydown', function (e) {
      if (e.key === 'ArrowRight' || e.key === 'ArrowDown') {
        nextNotice();
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

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
