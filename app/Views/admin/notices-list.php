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
                    <option value="pending" <?= ($_GET['status'] ?? '') === 'pending' ? 'selected' : '' ?>>Pending</option>
                    <option value="approved" <?= ($_GET['status'] ?? '') === 'approved' ? 'selected' : '' ?>>Approved</option>
                    <option value="rejected" <?= ($_GET['status'] ?? '') === 'rejected' ? 'selected' : '' ?>>Rejected</option>
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
                                <th>Approval</th>
                                <th>Target</th>
                                <th>Posted</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($notices as $notice): ?>
                                <tr>
                                    <td data-label="Title">
                                        <a href="/notice/<?= $notice['id'] ?>"><?= htmlspecialchars($notice['title']) ?></a>
                                        <?php if (!empty($notice['is_pinned'])): ?>
                                            <span class="badge badge-published">Pinned</span>
                                        <?php endif; ?>
                                    </td>
                                    <td data-label="Category"><?= htmlspecialchars($notice['category_name'] ?? '—') ?></td>
                                    <td data-label="Priority">
                                        <span class="badge <?= $notice['priority'] === 'high' ? 'badge-urgent' : ($notice['priority'] === 'medium' ? 'badge-warning' : 'badge-normal') ?>">
                                            <?= htmlspecialchars($notice['priority']) ?>
                                        </span>
                                    </td>
                                    <td data-label="Status">
                                        <?php $status = $notice['status'] ?? ''; ?>
                                        <?php if ($status === 'draft'): ?>
                                            <span class="badge badge-draft">Draft</span>
                                        <?php elseif ($status === 'pending'): ?>
                                            <span class="badge badge-warning">Pending</span>
                                        <?php elseif ($status === 'approved'): ?>
                                            <span class="badge badge-success">Approved</span>
                                        <?php elseif ($status === 'rejected'): ?>
                                            <span class="badge badge-urgent">Rejected</span>
                                        <?php elseif ($status === 'published'): ?>
                                            <span class="badge badge-published">Published</span>
                                        <?php elseif ($status === 'archived'): ?>
                                            <span class="badge badge-archived">Archived</span>
                                        <?php else: ?>
                                            <span class="badge"><?= htmlspecialchars($status) ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td data-label="Approval">
                                        <?php $approval = $notice['approval_status'] ?? ''; ?>
                                        <?php if ($approval): ?>
                                            <span class="badge <?= $approval === 'approved' ? 'badge-success' : ($approval === 'rejected' ? 'badge-urgent' : 'badge-warning') ?>">
                                                <?= htmlspecialchars(ucfirst($approval)) ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td data-label="Target"><?= htmlspecialchars($notice['target_audience_type'] ?? 'Everyone') ?></td>
                                    <td data-label="Posted"><?= date('M j, Y', strtotime($notice['created_at'])) ?></td>
                                    <td data-label="Actions" class="action-cell">
                                        <a href="/admin/notices/edit/<?= $notice['id'] ?>" class="btn btn-sm btn-secondary">Edit</a>
                                        <?php if (($notice['status'] ?? '') === 'pending'): ?>
                                            <form method="POST" action="/admin/notices/approve/<?= $notice['id'] ?>" style="display:inline;">
                                                <input type="hidden" name="csrf_token" value="<?= \App\Core\Auth::getCsrfToken() ?>">
                                                <button type="submit" class="btn btn-sm btn-success">Approve</button>
                                            </form>
                                            <form method="POST" action="/admin/notices/reject/<?= $notice['id'] ?>" style="display:inline;">
                                                <input type="hidden" name="csrf_token" value="<?= \App\Core\Auth::getCsrfToken() ?>">
                                                <button type="submit" class="btn btn-sm btn-danger">Reject</button>
                                            </form>
                                        <?php endif; ?>
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
