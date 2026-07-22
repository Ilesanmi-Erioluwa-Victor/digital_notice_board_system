<div class="card" style="max-width:800px;margin:0 auto;">
    <div class="card-body">
        <div class="notice-meta" style="margin-bottom:1rem;">
            <span class="badge <?= $notice['priority'] === 'high' ? 'badge-urgent' : ($notice['priority'] === 'medium' ? 'badge-expiring' : 'badge-normal') ?>">
                <?php $priorityMap = ['high' => 'High', 'medium' => 'Medium', 'low' => 'Low']; ?>
                <?= $priorityMap[$notice['priority']] ?? 'Normal' ?>
            </span>
            <?php if (!empty($notice['category_name'])): ?>
                <span class="badge badge-normal"><?= htmlspecialchars($notice['category_name']) ?></span>
            <?php endif; ?>
            <?php if (!empty($notice['status']) && $notice['status'] !== 'published'): ?>
                <span class="badge <?= $notice['status'] === 'pending' ? 'badge-expiring' : ($notice['status'] === 'rejected' ? 'badge-urgent' : 'badge-draft') ?>">
                    <?= ucfirst(htmlspecialchars($notice['status'])) ?>
                </span>
            <?php endif; ?>
            <?php if (!empty($notice['target_audience_type']) && $notice['target_audience_type'] !== 'everyone'): ?>
                <span class="badge badge-normal">
                    <?= ucfirst(htmlspecialchars($notice['target_audience_type'])) ?>
                </span>
            <?php endif; ?>
            <span class="text-muted">Posted by <?= htmlspecialchars($notice['author_name'] ?? 'Unknown') ?></span>
            <span class="text-muted"><?= date('F j, Y \a\t g:i A', strtotime($notice['created_at'])) ?></span>
        </div>

        <?php
        if (!isset($viewCount)) {
            $db = \App\Core\Database::getInstance();
            $vc = $db->fetchOne('SELECT COUNT(*) AS cnt FROM notice_views WHERE notice_id = :id', ['id' => $notice['id']]);
            $viewCount = (int)($vc['cnt'] ?? 0);
        }
        ?>
        <div style="margin-bottom:1rem;font-size:0.85rem;color:var(--color-text-secondary);">
            <span>&#128065; <?= $viewCount ?> view<?= $viewCount !== 1 ? 's' : '' ?></span>
            <?php if (\App\Core\Auth::isLoggedIn()): ?>
                <button id="detail-bookmark-btn" data-notice-id="<?= $notice['id'] ?>" data-bookmarked="<?= $isBookmarked ? 'true' : 'false' ?>" title="Bookmark this notice" style="background:none;border:none;cursor:pointer;font-size:1.3rem;line-height:1;padding:0;margin-left:0.75rem;color:var(--color-warning);vertical-align:middle;"><?= $isBookmarked ? '&#9733;' : '&#9734;' ?></button>
            <?php endif; ?>
        </div>

        <h1 style="font-size:1.75rem;margin-bottom:1rem;"><?= htmlspecialchars($notice['title']) ?></h1>

        <?php if (!empty($notice['rejection_reason'])): ?>
            <div class="card" style="border-left:4px solid var(--color-urgent);margin-bottom:1rem;background:#fef2f2;">
                <div class="card-body">
                    <strong style="color:var(--color-urgent);">Rejection Reason:</strong>
                    <p style="margin-top:0.25rem;"><?= htmlspecialchars($notice['rejection_reason']) ?></p>
                </div>
            </div>
        <?php endif; ?>

        <div style="font-size:1.05rem;line-height:1.8;color:var(--color-text-primary);">
            <?= nl2br(htmlspecialchars($notice['body'])) ?>
        </div>

        <?php if (!empty($notice['expires_at'])): ?>
            <p class="text-muted mt-3" style="font-size:0.85rem;">
                Expires: <?= date('F j, Y \a\t g:i A', strtotime($notice['expires_at'])) ?>
            </p>
        <?php endif; ?>
    </div>

    <?php if (!empty($attachments)): ?>
    <div class="card-footer">
        <strong>Attachments:</strong>
        <ul style="margin-top:0.5rem;list-style:none;">
            <?php foreach ($attachments as $att): ?>
                <?php $isImage = in_array(strtolower($att['file_type']), ['jpg', 'jpeg', 'png', 'gif']); ?>
                <li style="margin-bottom:0.5rem;">
                    <?php if ($isImage): ?>
                        <a href="/<?= htmlspecialchars($att['file_path']) ?>" target="_blank">
                            <img src="/<?= htmlspecialchars($att['file_path']) ?>" alt="<?= htmlspecialchars($att['original_name']) ?>" style="max-width:100%;max-height:400px;border-radius:var(--radius);border:1px solid var(--color-border);display:block;margin-bottom:0.25rem;">
                        </a>
                    <?php endif; ?>
                    <a href="/<?= htmlspecialchars($att['file_path']) ?>" target="_blank" class="btn btn-sm btn-secondary">
                        &#128206; <?= $isImage ? 'Open image' : 'Download ' . strtoupper(htmlspecialchars($att['file_type'])) ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>
</div>

<div class="mt-2 text-center">
    <a href="/" class="btn btn-primary">&larr; Back to All Notices</a>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var btn = document.getElementById('detail-bookmark-btn');
    if (btn) {
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
    }
});
</script>
