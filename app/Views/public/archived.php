<div class="page-header">
    <h1>Archived Notices</h1>
    <p class="text-muted" style="margin-top:0.25rem;">Notices you've archived. Unarchive to see them on your feed again.</p>
</div>

<?php if (empty($archived)): ?>
<div class="card"><div class="card-body text-center text-muted"><p>No archived notices.</p><a href="/" class="btn btn-primary mt-2">Browse Notices</a></div></div>
<?php else: ?>
<div class="notice-grid">
    <?php foreach ($archived as $a): ?>
    <div class="card notice-card <?= $a['priority'] === 'high' ? 'urgent' : '' ?>">
        <div class="card-body">
            <h3 class="notice-title"><a href="/notice/<?= $a['notice_id'] ?>"><?= htmlspecialchars($a['notice_title']) ?></a></h3>
            <p class="notice-body"><?= htmlspecialchars(substr($a['body'], 0, 200)) ?><?= strlen($a['body']) > 200 ? '...' : '' ?></p>
            <div class="notice-meta">
                <?php if (!empty($a['category_name'])): ?><span class="badge badge-normal"><?= htmlspecialchars($a['category_name']) ?></span><?php endif; ?>
                <span class="text-muted">Archived <?= date('M j, Y', strtotime($a['archived_at'])) ?></span>
                <button class="btn-icon unarchive-btn" data-notice-id="<?= $a['notice_id'] ?>" title="Unarchive this notice">&#128210;</button>
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
    document.querySelectorAll('.unarchive-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var id = this.getAttribute('data-notice-id');
            var token = csrfToken();
            var self = this;
            fetch('/api/notices/archive/' + id, {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded', 'X-CSRF-Token': token},
                body: 'csrf_token=' + encodeURIComponent(token)
            })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (!data.archived) {
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