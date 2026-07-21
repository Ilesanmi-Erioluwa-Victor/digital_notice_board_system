<div class="card" style="max-width:800px;margin:0 auto;">
    <div class="card-body">
        <div class="notice-meta" style="margin-bottom:1rem;">
            <span class="badge <?= $notice['priority'] === 'urgent' ? 'badge-urgent' : 'badge-normal' ?>">
                <?= $notice['priority'] === 'urgent' ? 'Urgent' : 'Normal' ?>
            </span>
            <?php if (!empty($notice['category_name'])): ?>
                <span class="badge badge-normal"><?= htmlspecialchars($notice['category_name']) ?></span>
            <?php endif; ?>
            <span class="text-muted">Posted by <?= htmlspecialchars($notice['author_name'] ?? 'Unknown') ?></span>
            <span class="text-muted"><?= date('F j, Y \a\t g:i A', strtotime($notice['created_at'])) ?></span>
        </div>

        <h1 style="font-size:1.75rem;margin-bottom:1rem;"><?= htmlspecialchars($notice['title']) ?></h1>

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
                <li style="margin-bottom:0.25rem;">
                    <a href="/<?= htmlspecialchars($att['file_path']) ?>" target="_blank" class="btn btn-sm btn-secondary">
                        &#128206; Download <?= strtoupper(htmlspecialchars($att['file_type'])) ?>
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
