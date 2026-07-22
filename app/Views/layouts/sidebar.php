<aside class="admin-sidebar">
    <div class="sidebar-heading">Admin Panel</div>
    <?php $uri = $_SERVER['REQUEST_URI'] ?? ''; ?>
    <ul class="sidebar-nav">
        <li>
            <a href="/admin/dashboard" class="<?= $uri === '/admin/dashboard' ? 'active' : '' ?>">
                Dashboard
            </a>
        </li>
        <li>
            <?php $isNotices = strpos($uri, '/admin/notices') === 0 && strpos($uri, '/admin/notices/pending') !== 0; ?>
            <a href="/admin/notices" class="<?= $isNotices ? 'active' : '' ?>">
                Notices
                <?php if (isset($pendingNotices) && $pendingNotices > 0): ?>
                    <span class="badge badge-warning" style="font-size:0.65rem;margin-left:0.25rem;"><?= $pendingNotices ?></span>
                <?php endif; ?>
            </a>
        </li>
        <?php if (\App\Core\Auth::hasRole('admin')): ?>
        <li>
            <a href="/admin/notices/pending" class="<?= strpos($uri, '/admin/notices/pending') === 0 ? 'active' : '' ?>">
                Pending Approvals
                <?php
                $pc = \App\Core\Database::getInstance()->fetchOne(
                    "SELECT COUNT(*) AS count FROM notices WHERE approval_status = 'pending'"
                );
                $pc = (int)($pc['count'] ?? 0);
                if ($pc > 0): ?>
                    <span class="badge badge-urgent" style="font-size:0.65rem;margin-left:0.25rem;"><?= $pc ?></span>
                <?php endif; ?>
            </a>
        </li>
        <?php endif; ?>
        <li>
            <a href="/admin/categories" class="<?= strpos($uri, '/admin/categories') === 0 ? 'active' : '' ?>">
                Categories
            </a>
        </li>
        <li>
            <a href="/admin/faculties" class="<?= strpos($uri, '/admin/faculties') === 0 ? 'active' : '' ?>">
                Faculties
            </a>
        </li>
        <li>
            <a href="/admin/departments" class="<?= strpos($uri, '/admin/departments') === 0 ? 'active' : '' ?>">
                Departments
            </a>
        </li>
        <li>
            <a href="/admin/programmes" class="<?= strpos($uri, '/admin/programmes') === 0 ? 'active' : '' ?>">
                Programmes
            </a>
        </li>
        <li>
            <a href="/admin/levels" class="<?= strpos($uri, '/admin/levels') === 0 ? 'active' : '' ?>">
                Levels
            </a>
        </li>
        <?php if (\App\Core\Auth::hasRole('admin')): ?>
        <li>
            <a href="/admin/users" class="<?= strpos($uri, '/admin/users') === 0 ? 'active' : '' ?>">
                Users
            </a>
        </li>
        <?php endif; ?>
        <li>
            <a href="/admin/activity-log" class="<?= strpos($uri, '/admin/activity-log') === 0 ? 'active' : '' ?>">
                Activity Logs
            </a>
        </li>
        <li>
            <a href="/admin/analytics" class="<?= strpos($uri, '/admin/analytics') === 0 ? 'active' : '' ?>">
                Analytics
            </a>
        </li>
        <li>
            <a href="/admin/reports" class="<?= strpos($uri, '/admin/reports') === 0 ? 'active' : '' ?>">
                Reports
            </a>
        </li>
    </ul>
</aside>
