<div class="admin-layout">
    <?php require __DIR__ . '/../layouts/sidebar.php'; ?>
    <div>
        <div class="page-header">
            <h1>Activity Logs</h1>
        </div>

        <?php if (empty($logs)): ?>
            <div class="card">
                <div class="card-body text-center text-muted">
                    <p>No activity recorded yet.</p>
                </div>
            </div>
        <?php else: ?>
            <div class="card">
                <div class="card-body" style="padding-bottom:0;">
                    <?php foreach ($logs as $log): ?>
                        <div class="log-entry">
                            <span class="log-time"><?= date('M j, Y g:i A', strtotime($log['timestamp'])) ?></span>
                            <span class="log-action"><?= htmlspecialchars($log['action']) ?></span>
                            <span class="log-details"><?= htmlspecialchars($log['details'] ?? '') ?></span>
                            <span class="text-muted">by <?= htmlspecialchars($log['admin_name'] ?? 'System') ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
