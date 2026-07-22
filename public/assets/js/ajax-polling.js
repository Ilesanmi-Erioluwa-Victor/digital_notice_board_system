(function () {
  'use strict';

  var POLL_INTERVAL = 30000;
  var noticeGrid = document.getElementById('notice-grid');

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

  function renderNotices(notices) {
    if (!noticeGrid) return;

    while (noticeGrid.firstChild) {
      noticeGrid.removeChild(noticeGrid.firstChild);
    }

    if (!notices || notices.length === 0) {
      noticeGrid.innerHTML =
        '<div class="card"><div class="card-body text-center text-muted">' +
        '<p>No active notices at this time. Check back later.</p></div></div>';
      return;
    }

    var isLoggedIn = noticeGrid.getAttribute('data-user-loggedin') === 'true';

    notices.forEach(function (notice) {
      var card = document.createElement('div');
      card.className = 'card notice-card';
      if (notice.priority === 'high') {
        card.classList.add('urgent');
      }

      var priorityClass = 'badge-normal';
      var priorityLabel = 'Normal';
      if (notice.priority === 'high') {
        priorityClass = 'badge-urgent';
        priorityLabel = 'High';
      } else if (notice.priority === 'medium') {
        priorityClass = 'badge-expiring';
        priorityLabel = 'Medium';
      } else if (notice.priority === 'low') {
        priorityClass = 'badge-normal';
        priorityLabel = 'Low';
      }

      var priorityBadge = '<span class="badge ' + priorityClass + '">' + priorityLabel + '</span>';

      var categoryHtml = notice.category_name
        ? '<span class="badge badge-normal">' +
          escapeHtml(notice.category_name) +
          '</span>'
        : '';

      var bodyTruncated = notice.body
        ? escapeHtml(notice.body.substring(0, 200))
        : '';

      var bookmarkHtml = '';
      if (isLoggedIn) {
        var starState = notice.is_bookmarked ? '&#9733;' : '&#9734;';
        var bookmarkedAttr = notice.is_bookmarked ? 'true' : 'false';
        bookmarkHtml =
          '<button class="bookmark-btn" data-notice-id="' +
          notice.id +
          '" data-bookmarked="' +
          bookmarkedAttr +
          '" title="Bookmark this notice" style="background:none;border:none;cursor:pointer;font-size:1.2rem;line-height:1;padding:0;color:var(--color-warning);">' +
          starState +
          '</button>';
      }

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
        bookmarkHtml +
        '</div>' +
        '</div>';

      noticeGrid.appendChild(card);
    });

    document.querySelectorAll('.bookmark-btn').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var id = this.getAttribute('data-notice-id');
        var meta = document.querySelector('meta[name="csrf-token"]');
        var token = meta ? meta.getAttribute('content') : '';
        var self = this;
        fetch('/api/notices/bookmark/' + id, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-CSRF-Token': token
          },
          body: 'csrf_token=' + encodeURIComponent(token)
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
          if (data.bookmarked) {
            self.innerHTML = '&#9733;';
            self.setAttribute('data-bookmarked', 'true');
          } else {
            self.innerHTML = '&#9734;';
            self.setAttribute('data-bookmarked', 'false');
          }
        })
        .catch(function () {});
      });
    });
  }

  function startPolling(url, interval) {
    interval = interval || POLL_INTERVAL;
    fetchNotices(url).then(renderNotices);
    setInterval(function () {
      fetchNotices(url).then(renderNotices);
    }, interval);
  }

  function escapeHtml(text) {
    if (!text) return '';
    var div = document.createElement('div');
    div.appendChild(document.createTextNode(text));
    return div.innerHTML;
  }

  function formatDate(dateStr) {
    if (!dateStr) return '';
    var d = new Date(dateStr);
    return d.toLocaleDateString('en-US', {
      year: 'numeric',
      month: 'short',
      day: 'numeric',
    });
  }

  window.noticePolling = {
    start: startPolling,
    fetch: fetchNotices,
    render: renderNotices,
  };

  if (noticeGrid) {
    var pollUrl = noticeGrid.getAttribute('data-poll-url') || '/api/notices/active';
    var pollInterval = parseInt(noticeGrid.getAttribute('data-poll-interval'), 10) || POLL_INTERVAL;
    startPolling(pollUrl, pollInterval);
  }
})();
