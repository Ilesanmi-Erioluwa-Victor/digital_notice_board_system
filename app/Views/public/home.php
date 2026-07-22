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
            <option value="<?= $cat['id'] ?>" <?= (isset($_GET['category']) && (int)$_GET['category'] === (int)$cat['id']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($cat['name']) ?>
            </option>
        <?php endforeach; ?>
    </select>
</div>

<?php if (!empty($pinnedNotices)): ?>
<div class="pinned-section" style="margin-bottom:1.5rem;">
    <h2 style="font-size:1.1rem;color:var(--color-warning);margin-bottom:0.75rem;">&#128204; Pinned Notices</h2>
    <div class="notice-grid">
        <?php foreach ($pinnedNotices as $notice):
            $isBm = in_array((int)$notice['id'], $bookmarkedIds, true);
        ?>
            <div class="card notice-card <?= $notice['priority'] === 'high' ? 'urgent' : '' ?>">
                <div class="card-body">
                    <h3 class="notice-title">
                        <a href="/notice/<?= $notice['id'] ?>"><?= htmlspecialchars($notice['title']) ?></a>
                    </h3>
                    <p class="notice-body"><?= htmlspecialchars(substr($notice['body'], 0, 200)) ?><?= strlen($notice['body']) > 200 ? '...' : '' ?></p>
                    <div class="notice-meta">
                        <span class="badge <?= $notice['priority'] === 'high' ? 'badge-urgent' : ($notice['priority'] === 'medium' ? 'badge-expiring' : 'badge-normal') ?>"><?= ucfirst(htmlspecialchars($notice['priority'] ?? 'normal')) ?></span>
                        <?php if (!empty($notice['category_name'])): ?><span class="badge badge-normal"><?= htmlspecialchars($notice['category_name']) ?></span><?php endif; ?>
                        <span><?= date('M j, Y', strtotime($notice['created_at'])) ?></span>
                        <?php if (\App\Core\Auth::isLoggedIn()): ?>
                            <button class="btn-icon bookmark-btn" data-notice-id="<?= $notice['id'] ?>" data-bookmarked="<?= $isBm ? 'true' : 'false' ?>" title="<?= $isBm ? 'Remove bookmark' : 'Bookmark this notice' ?>"><?= $isBm ? '&#9733;' : '&#9734;' ?></button>
                            <button class="btn-icon archive-btn" data-notice-id="<?= $notice['id'] ?>" title="Archive this notice">&nbsp;&#128210;</button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<div id="notice-grid" class="notice-grid">
    <?php if (empty($regularNotices)): ?>
        <div class="card"><div class="card-body text-center text-muted"><p>No active notices at this time. Check back later.</p></div></div>
    <?php else: ?>
        <?php foreach ($regularNotices as $notice):
            $isBm = in_array((int)$notice['id'], $bookmarkedIds, true);
        ?>
            <div class="card notice-card <?= $notice['priority'] === 'high' ? 'urgent' : '' ?>">
                <div class="card-body">
                    <h3 class="notice-title">
                        <a href="/notice/<?= $notice['id'] ?>"><?= htmlspecialchars($notice['title']) ?></a>
                    </h3>
                    <p class="notice-body"><?= htmlspecialchars(substr($notice['body'], 0, 200)) ?><?= strlen($notice['body']) > 200 ? '...' : '' ?></p>
                    <div class="notice-meta">
                        <span class="badge <?= $notice['priority'] === 'high' ? 'badge-urgent' : ($notice['priority'] === 'medium' ? 'badge-expiring' : 'badge-normal') ?>"><?= ucfirst(htmlspecialchars($notice['priority'] ?? 'normal')) ?></span>
                        <?php if (!empty($notice['category_name'])): ?><span class="badge badge-normal"><?= htmlspecialchars($notice['category_name']) ?></span><?php endif; ?>
                        <span><?= date('M j, Y', strtotime($notice['created_at'])) ?></span>
                        <?php if (\App\Core\Auth::isLoggedIn()): ?>
                            <button class="btn-icon bookmark-btn" data-notice-id="<?= $notice['id'] ?>" data-bookmarked="<?= $isBm ? 'true' : 'false' ?>" title="<?= $isBm ? 'Remove bookmark' : 'Bookmark this notice' ?>"><?= $isBm ? '&#9733;' : '&#9734;' ?></button>
                            <button class="btn-icon archive-btn" data-notice-id="<?= $notice['id'] ?>" title="Archive this notice">&nbsp;&#128210;</button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php if ($result['pages'] > 1): ?>
<div class="pagination" style="display:flex;justify-content:center;align-items:center;gap:0.5rem;margin-top:2rem;">
    <?php if ($result['page'] > 1): ?>
        <a href="?page=<?= $result['page'] - 1 ?><?= isset($_GET['category']) ? '&category=' . (int)$_GET['category'] : '' ?>" class="btn btn-secondary">&laquo; Previous</a>
    <?php endif; ?>
    <?php for ($i = 1; $i <= $result['pages']; $i++): ?>
        <a href="?page=<?= $i ?><?= isset($_GET['category']) ? '&category=' . (int)$_GET['category'] : '' ?>"
           class="btn <?= $i === $result['page'] ? 'btn-primary' : 'btn-secondary' ?>"
           style="min-width:36px;"><?= $i ?></a>
    <?php endfor; ?>
    <?php if ($result['page'] < $result['pages']): ?>
        <a href="?page=<?= $result['page'] + 1 ?><?= isset($_GET['category']) ? '&category=' . (int)$_GET['category'] : '' ?>" class="btn btn-secondary">Next &raquo;</a>
    <?php endif; ?>
</div>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var searchInput = document.getElementById('search-input');
    var searchBtn = document.getElementById('search-btn');
    var categoryFilter = document.getElementById('category-filter');

    function doSearch() {
        var q = searchInput ? searchInput.value.trim() : '';
        if (q.length > 0) {
            window.location.href = '/?search=' + encodeURIComponent(q);
        } else {
            window.location.href = '/';
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

    function csrfToken() {
        var meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
    }

    document.querySelectorAll('.bookmark-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var id = this.getAttribute('data-notice-id');
            var token = csrfToken();
            var self = this;
            fetch('/api/notices/bookmark/' + id, {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded', 'X-CSRF-Token': token},
                body: 'csrf_token=' + encodeURIComponent(token)
            })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data.bookmarked) {
                    self.innerHTML = '&#9733;';
                    self.setAttribute('data-bookmarked', 'true');
                    self.title = 'Remove bookmark';
                } else {
                    self.innerHTML = '&#9734;';
                    self.setAttribute('data-bookmarked', 'false');
                    self.title = 'Bookmark this notice';
                }
            })
            .catch(function () {});
        });
    });

    document.querySelectorAll('.archive-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var id = this.getAttribute('data-notice-id');
            var token = csrfToken();
            var card = this.closest('.notice-card');
            fetch('/api/notices/archive/' + id, {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded', 'X-CSRF-Token': token},
                body: 'csrf_token=' + encodeURIComponent(token)
            })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data.archived && card) {
                    card.style.transition = 'opacity 0.3s ease';
                    card.style.opacity = '0';
                    setTimeout(function () { card.remove(); }, 300);
                }
            })
            .catch(function () {});
        });
    });
});
</script>
