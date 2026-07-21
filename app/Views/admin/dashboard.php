<div class="admin-layout">
    <?php require __DIR__ . '/../layouts/sidebar.php'; ?>
    <div>
        <div class="page-header">
            <h1>Dashboard</h1>
        </div>

        <div class="stats-grid">
            <div class="card stat-card">
                <div class="stat-value"><?= $totalNotices ?></div>
                <div class="stat-label">Total Notices</div>
            </div>
            <div class="card stat-card">
                <div class="stat-value" style="color:var(--color-success);"><?= $activeNotices ?></div>
                <div class="stat-label">Active</div>
            </div>
            <div class="card stat-card">
                <div class="stat-value" style="color:var(--color-urgent);"><?= $expiredNotices ?></div>
                <div class="stat-label">Expired</div>
            </div>
            <div class="card stat-card">
                <div class="stat-value" style="color:var(--color-warning);"><?= $draftNotices ?></div>
                <div class="stat-label">Drafts</div>
            </div>
            <div class="card stat-card">
                <div class="stat-value"><?= $totalCategories ?></div>
                <div class="stat-label">Categories</div>
            </div>
            <div class="card stat-card">
                <div class="stat-value"><?= $totalUsers ?></div>
                <div class="stat-label">Users</div>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-body">
                <h3>Recent Activity</h3>
            </div>
            <?php if (!empty($recentLogs)): ?>
                <div class="card-body" style="padding-top:0;">
                    <?php foreach ($recentLogs as $log): ?>
                        <div class="log-entry">
                            <span class="log-time"><?= date('M j, g:i A', strtotime($log['timestamp'])) ?></span>
                            <span class="log-action"><?= htmlspecialchars($log['action']) ?></span>
                            <span class="log-details"><?= htmlspecialchars($log['details'] ?? '') ?></span>
                            <span class="text-muted">by <?= htmlspecialchars($log['admin_name'] ?? 'System') ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="card-body">
                    <p class="text-muted">No activity recorded yet.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
