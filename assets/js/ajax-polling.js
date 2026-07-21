/**
 * AJAX Polling Module
 *
 * Fetches active notices from /api/notices/active at a configurable interval
 * and re-renders the notice grid without a full page reload.
 * Provides a "real-time" experience for public viewers and kiosk displays.
 */

(function () {
  'use strict';

  var POLL_INTERVAL = 30000; // 30 seconds — matches config constant AJAX_POLL_INTERVAL
  var noticeGrid = document.getElementById('notice-grid');
  var templateNotice = document.getElementById('template-notice');

  /**
   * Fetch active notices from the API endpoint.
   * @param {string} url - API URL to fetch
   * @returns {Promise<Array>} Array of notice objects
   */
  function fetchNotices(url) {
    url = url || '/api/notices/active';
    return fetch(url)
      .then(function (response) {
        if (!response.ok) {
          throw new Error('Network response was not ok');
        }
        return response.json();
      })
      .catch(function (err) {
        console.error('AJAX polling fetch error:', err);
        return [];
      });
  }

  /**
   * Render notice cards into the grid.
   * @param {Array} notices - Array of notice objects from the API
   */
  function renderNotices(notices) {
    if (!noticeGrid) return;

    // Clear current notices (keep the template node)
    while (noticeGrid.firstChild) {
      noticeGrid.removeChild(noticeGrid.firstChild);
    }

    if (!notices || notices.length === 0) {
      noticeGrid.innerHTML =
        '<div class="card"><div class="card-body text-center text-muted">' +
        '<p>No active notices at this time. Check back later.</p></div></div>';
      return;
    }

    notices.forEach(function (notice) {
      var card = document.createElement('div');
      card.className = 'card notice-card';
      if (notice.priority === 'urgent') {
        card.classList.add('urgent');
      }

      var priorityBadge =
        notice.priority === 'urgent'
          ? '<span class="badge badge-urgent">Urgent</span>'
          : '<span class="badge badge-normal">Normal</span>';

      var categoryHtml = notice.category_name
        ? '<span class="badge badge-normal">' +
          escapeHtml(notice.category_name) +
          '</span>'
        : '';

      var bodyTruncated = notice.body
        ? escapeHtml(notice.body.substring(0, 200))
        : '';

      card.innerHTML =
        '<div class="card-body">' +
        '<h3 class="notice-title">' +
        '<a href="/notice/' +
        notice.id +
        '">' +
        escapeHtml(notice.title) +
        '</a></h3>' +
        '<p class="notice-body">' +
        bodyTruncated +
        (notice.body && notice.body.length > 200 ? '...' : '') +
        '</p>' +
        '<div class="notice-meta">' +
        priorityBadge +
        categoryHtml +
        '<span>' +
        formatDate(notice.created_at) +
        '</span>' +
        '</div>' +
        '</div>';

      noticeGrid.appendChild(card);
    });
  }

  /**
   * Start polling the API endpoint at the configured interval.
   * @param {string} url - API URL to poll
   * @param {number} interval - Polling interval in milliseconds
   */
  function startPolling(url, interval) {
    interval = interval || POLL_INTERVAL;

    // Initial fetch
    fetchNotices(url).then(renderNotices);

    // Subsequent fetches on interval
    setInterval(function () {
      fetchNotices(url).then(renderNotices);
    }, interval);
  }

  /**
   * Escape HTML entities to prevent XSS.
   * @param {string} text
   * @returns {string}
   */
  function escapeHtml(text) {
    if (!text) return '';
    var div = document.createElement('div');
    div.appendChild(document.createTextNode(text));
    return div.innerHTML;
  }

  /**
   * Format an ISO date string to a readable format.
   * @param {string} dateStr
   * @returns {string}
   */
  function formatDate(dateStr) {
    if (!dateStr) return '';
    var d = new Date(dateStr);
    return d.toLocaleDateString('en-US', {
      year: 'numeric',
      month: 'short',
      day: 'numeric',
    });
  }

  // Expose public API if running on a page that needs AJAX polling
  window.noticePolling = {
    start: startPolling,
    fetch: fetchNotices,
    render: renderNotices,
  };

  // Auto-start if the notice grid element exists on the page
  if (noticeGrid) {
    var pollUrl = noticeGrid.getAttribute('data-poll-url') || '/api/notices/active';
    var pollInterval = parseInt(noticeGrid.getAttribute('data-poll-interval'), 10) || POLL_INTERVAL;
    startPolling(pollUrl, pollInterval);
  }
})();
