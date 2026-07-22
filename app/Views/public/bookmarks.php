<div class="page-header">
    <h1>My Bookmarks</h1>
    <p class="text-muted" style="margin-top:0.25rem;">Notices you've bookmarked for quick access.</p>
</div>

<?php if (empty($bookmarks)): ?>
<div class="card"><div class="card-body text-center text-muted"><p>You haven't bookmarked any notices yet.</p><a href="/" class="btn btn-primary mt-2">Browse Notices</a></div></div>
<?php else: ?>
<div class="notice-grid">
    <?php foreach ($bookmarks as $bm): ?>
    <div class="card notice-card <?= $bm['priority'] === 'high' ? 'urgent' : '' ?>">
        <div class="card-body">
            <h3 class="notice-title"><a href="/notice/<?= $bm['notice_id'] ?>"><?= htmlspecialchars($bm['notice_title']) ?></a></h3>
            <p class="notice-body"><?= htmlspecialchars(substr($bm['notice_body'], 0, 200)) ?><?= strlen($bm['notice_body']) > 200 ? '...' : '' ?></p>
            <div class="notice-meta">
                <span class="text-muted">Bookmarked <?= date('M j, Y', strtotime($bm['created_at'])) ?></span>
                <button class="btn-icon bookmark-btn" data-notice-id="<?= $bm['notice_id'] ?>" data-bookmarked="true" title="Remove bookmark">&#9733;</button>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function () {
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
                if (!data.bookmarked) {
                    var card = self.closest('.notice-card');
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