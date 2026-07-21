<aside class="admin-sidebar">
    <div class="sidebar-heading">Admin Panel</div>
    <ul class="sidebar-nav">
        <li>
            <a href="/admin/dashboard" class="<?= strpos($_SERVER['REQUEST_URI'] ?? '', '/admin/dashboard') !== false ? 'active' : '' ?>">
                Dashboard
            </a>
        </li>
        <li>
            <a href="/admin/notices" class="<?= strpos($_SERVER['REQUEST_URI'] ?? '', '/admin/notices') !== false ? 'active' : '' ?>">
                Notices
            </a>
        </li>
        <li>
            <a href="/admin/categories" class="<?= strpos($_SERVER['REQUEST_URI'] ?? '', '/admin/categories') !== false ? 'active' : '' ?>">
                Categories
            </a>
        </li>
        <?php if (\App\Core\Auth::hasRole('super_admin')): ?>
        <li>
            <a href="/admin/users" class="<?= strpos($_SERVER['REQUEST_URI'] ?? '', '/admin/users') !== false ? 'active' : '' ?>">
                Users
            </a>
        </li>
        <?php endif; ?>
        <li>
            <a href="/admin/logs" class="<?= strpos($_SERVER['REQUEST_URI'] ?? '', '/admin/logs') !== false ? 'active' : '' ?>">
                Activity Logs
            </a>
        </li>
    </ul>
</aside>
