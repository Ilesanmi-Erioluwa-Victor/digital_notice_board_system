<div class="page-header">
    <h1>Active Notices</h1>
</div>

<div class="filter-bar">
    <div class="search-box">
        <input type="text" id="search-input" class="form-control" placeholder="Search notices..." aria-label="Search notices">
        <button id="search-btn" class="btn btn-primary">Search</button>
    </div>
    <select id="category-filter" class="form-control">
        <option value="">All Categories</option>
        <?php foreach ($categories as $cat): ?>
            <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
        <?php endforeach; ?>
    </select>
</div>

<?php if (!empty($pinnedNotices)): ?>
<div class="pinned-section" style="margin-bottom:1.5rem;">
    <h2 style="font-size:1.1rem;color:var(--color-warning);margin-bottom:0.75rem;">&#128204; Pinned Notices</h2>
    <div class="notice-grid">
        <?php foreach ($pinnedNotices as $notice): ?>
            <div class="card notice-card urgent">
                <div class="card-body">
                    <h3 class="notice-title">
                        <a href="/notice/<?= $notice['id'] ?>">
                            <?= htmlspecialchars($notice['title']) ?>
                        </a>
                    </h3>
                    <p class="notice-body">
                        <?= htmlspecialchars(substr($notice['body'], 0, 200)) ?>
                        <?= strlen($notice['body']) > 200 ? '...' : '' ?>
                    </p>
                    <div class="notice-meta">
                        <span class="badge <?= $notice['priority'] === 'high' ? 'badge-urgent' : ($notice['priority'] === 'medium' ? 'badge-expiring' : 'badge-normal') ?>">
                            <?= ucfirst(htmlspecialchars($notice['priority'] ?? 'normal')) ?>
                        </span>
                        <?php if (!empty($notice['category_name'])): ?>
                            <span class="badge badge-normal"><?= htmlspecialchars($notice['category_name']) ?></span>
                        <?php endif; ?>
                        <span><?= date('M j, Y', strtotime($notice['created_at'])) ?></span>
                        <?php if (\App\Core\Auth::isLoggedIn()): ?>
                            <button class="bookmark-btn" data-notice-id="<?= $notice['id'] ?>" data-bookmarked="false" title="Bookmark this notice" style="background:none;border:none;cursor:pointer;font-size:1.2rem;line-height:1;padding:0;color:var(--color-warning);">&#9734;</button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<div id="notice-grid" class="notice-grid"
     data-poll-url="/api/notices/active"
     data-poll-interval="30000"
     data-user-loggedin="<?= \App\Core\Auth::isLoggedIn() ? 'true' : 'false' ?>">
    <?php if (empty($regularNotices)): ?>
        <div class="card">
            <div class="card-body text-center text-muted">
                <p>No active notices at this time. Check back later.</p>
            </div>
        </div>
    <?php else: ?>
        <?php foreach ($regularNotices as $notice): ?>
            <div class="card notice-card <?= $notice['priority'] === 'high' ? 'urgent' : '' ?>">
                <div class="card-body">
                    <h3 class="notice-title">
                        <a href="/notice/<?= $notice['id'] ?>">
                            <?= htmlspecialchars($notice['title']) ?>
                        </a>
                    </h3>
                    <p class="notice-body">
                        <?= htmlspecialchars(substr($notice['body'], 0, 200)) ?>
                        <?= strlen($notice['body']) > 200 ? '...' : '' ?>
                    </p>
                    <div class="notice-meta">
                        <span class="badge <?= $notice['priority'] === 'high' ? 'badge-urgent' : ($notice['priority'] === 'medium' ? 'badge-expiring' : 'badge-normal') ?>">
                            <?= ucfirst(htmlspecialchars($notice['priority'] ?? 'normal')) ?>
                        </span>
                        <?php if (!empty($notice['category_name'])): ?>
                            <span class="badge badge-normal"><?= htmlspecialchars($notice['category_name']) ?></span>
                        <?php endif; ?>
                        <span><?= date('M j, Y', strtotime($notice['created_at'])) ?></span>
                        <?php if (\App\Core\Auth::isLoggedIn()): ?>
                            <button class="bookmark-btn" data-notice-id="<?= $notice['id'] ?>" data-bookmarked="false" title="Bookmark this notice" style="background:none;border:none;cursor:pointer;font-size:1.2rem;line-height:1;padding:0;color:var(--color-warning);">&#9734;</button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<script src="/assets/js/ajax-polling.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var searchInput = document.getElementById('search-input');
    var searchBtn = document.getElementById('search-btn');
    var categoryFilter = document.getElementById('category-filter');

    function doSearch() {
        var q = searchInput ? searchInput.value.trim() : '';
        if (q.length > 0) {
            window.noticePolling.fetch('/api/notices/search?q=' + encodeURIComponent(q))
                .then(window.noticePolling.render);
        } else {
            window.noticePolling.fetch('/api/notices/active')
                .then(window.noticePolling.render);
        }
    }

    if (searchBtn) {
        searchBtn.addEventListener('click', doSearch);
    }
    if (searchInput) {
        searchInput.addEventListener('keyup', function (e) {
            if (e.key === 'Enter') doSearch();
        });
    }
    if (categoryFilter) {
        categoryFilter.addEventListener('change', function () {
            var val = this.value;
            if (val) {
                window.location.href = '/?category=' + val;
            } else {
                window.location.href = '/';
            }
        });
    }

    document.querySelectorAll('.bookmark-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var id = this.getAttribute('data-notice-id');
            var csrf = document.querySelector('meta[name="csrf-token"]');
            var token = csrf ? csrf.getAttribute('content') : '';
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
});
</script>
