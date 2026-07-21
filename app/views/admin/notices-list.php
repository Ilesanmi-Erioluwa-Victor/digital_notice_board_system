<div class="admin-layout">
    <?php require __DIR__ . '/../layouts/sidebar.php'; ?>
    <div>
        <div class="page-header">
            <h1>Notices</h1>
            <a href="/admin/notices/create" class="btn btn-primary">+ New Notice</a>
        </div>

        <div class="filter-bar">
            <form method="GET" action="/admin/notices" class="flex flex-wrap gap-1 items-center">
                <select name="status" class="form-control">
                    <option value="">All Statuses</option>
                    <option value="draft" <?= ($_GET['status'] ?? '') === 'draft' ? 'selected' : '' ?>>Draft</option>
                    <option value="published" <?= ($_GET['status'] ?? '') === 'published' ? 'selected' : '' ?>>Published</option>
                    <option value="archived" <?= ($_GET['status'] ?? '') === 'archived' ? 'selected' : '' ?>>Archived</option>
                </select>
                <select name="category" class="form-control">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= (isset($_GET['category']) && (int)$_GET['category'] === $cat['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn btn-secondary">Filter</button>
            </form>
        </div>

        <?php if (empty($notices)): ?>
            <div class="card">
                <div class="card-body text-center text-muted">
                    <p>No notices found.</p>
                </div>
            </div>
        <?php else: ?>
            <div class="card notice-table">
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Category</th>
                                <th>Priority</th>
                                <th>Status</th>
                                <th>Posted</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($notices as $notice): ?>
                                <tr>
                                    <td data-label="Title">
                                        <a href="/notice/<?= $notice['id'] ?>"><?= htmlspecialchars($notice['title']) ?></a>
                                    </td>
                                    <td data-label="Category"><?= htmlspecialchars($notice['category_name'] ?? '—') ?></td>
                                    <td data-label="Priority">
                                        <span class="badge <?= $notice['priority'] === 'urgent' ? 'badge-urgent' : 'badge-normal' ?>">
                                            <?= $notice['priority'] ?>
                                        </span>
                                    </td>
                                    <td data-label="Status">
                                        <span class="badge badge-<?= $notice['status'] ?>">
                                            <?= $notice['status'] ?>
                                        </span>
                                    </td>
                                    <td data-label="Posted"><?= date('M j, Y', strtotime($notice['created_at'])) ?></td>
                                    <td data-label="Actions" class="action-cell">
                                        <a href="/admin/notices/edit/<?= $notice['id'] ?>" class="btn btn-sm btn-secondary">Edit</a>
                                        <form method="POST" action="/admin/notices/delete/<?= $notice['id'] ?>" style="display:inline;">
                                            <input type="hidden" name="csrf_token" value="<?= \App\Core\Auth::getCsrfToken() ?>">
                                            <button type="submit" class="btn btn-sm btn-danger" data-confirm="Delete this notice permanently?">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <?php if ($pages > 1): ?>
                <div class="flex items-center gap-1 mt-2">
                    <?php for ($i = 1; $i <= $pages; $i++): ?>
                        <a href="/admin/notices?page=<?= $i ?>&status=<?= htmlspecialchars($_GET['status'] ?? '') ?>&category=<?= htmlspecialchars($_GET['category'] ?? '') ?>"
                           class="btn btn-sm <?= $i === $page ? 'btn-primary' : 'btn-secondary' ?>">
                            <?= $i ?>
                        </a>
                    <?php endfor; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>
