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

<div id="notice-grid" class="notice-grid"
     data-poll-url="/api/notices/active"
     data-poll-interval="30000">
    <?php if (empty($notices)): ?>
        <div class="card">
            <div class="card-body text-center text-muted">
                <p>No active notices at this time. Check back later.</p>
            </div>
        </div>
    <?php else: ?>
        <?php foreach ($notices as $notice): ?>
            <div class="card notice-card <?= $notice['priority'] === 'urgent' ? 'urgent' : '' ?>">
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
                        <span class="badge <?= $notice['priority'] === 'urgent' ? 'badge-urgent' : 'badge-normal' ?>">
                            <?= $notice['priority'] === 'urgent' ? 'Urgent' : 'Normal' ?>
                        </span>
                        <?php if (!empty($notice['category_name'])): ?>
                            <span class="badge badge-normal"><?= htmlspecialchars($notice['category_name']) ?></span>
                        <?php endif; ?>
                        <span><?= date('M j, Y', strtotime($notice['created_at'])) ?></span>
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
            // For simplicity, reload the page with category filter
            var val = this.value;
            if (val) {
                window.location.href = '/?category=' + val;
            } else {
                window.location.href = '/';
            }
        });
    }
});
</script>
