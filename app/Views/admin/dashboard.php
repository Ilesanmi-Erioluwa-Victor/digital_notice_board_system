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
                <div class="stat-label">Active Notices</div>
            </div>
            <div class="card stat-card">
                <div class="stat-value" style="color:var(--color-warning);"><?= $pendingNotices ?></div>
                <div class="stat-label">Pending Approval</div>
            </div>
            <div class="card stat-card">
                <div class="stat-value" style="color:var(--color-urgent);"><?= $expiredNotices ?></div>
                <div class="stat-label">Expired Notices</div>
            </div>
            <div class="card stat-card">
                <div class="stat-value"><?= $totalUsers ?></div>
                <div class="stat-label">Total Users</div>
            </div>
            <div class="card stat-card">
                <div class="stat-value"><?= $totalViews ?></div>
                <div class="stat-label">Total Views</div>
            </div>
        </div>

        <?php if (!empty($pendingNotices) && $pendingNotices > 0): ?>
        <div class="card mt-3" style="border-left: 4px solid var(--color-warning);">
            <div class="card-body flex items-center justify-between">
                <div>
                    <strong>Pending Approval:</strong> <?= $pendingNotices ?> notice(s) awaiting review.
                </div>
                <a href="/admin/notices/pending" class="btn btn-warning btn-sm">Review Now</a>
            </div>
        </div>
        <?php endif; ?>

        <div class="flex flex-wrap gap-1 mt-3">
            <a href="/admin/notices/create" class="btn btn-primary">Create Notice</a>
            <a href="/admin/notices/pending" class="btn btn-warning">Pending Approvals</a>
            <a href="/admin/reports" class="btn btn-secondary">View Reports</a>
            <a href="/admin/analytics" class="btn btn-secondary">Analytics</a>
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
