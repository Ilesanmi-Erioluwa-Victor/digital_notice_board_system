<div class="admin-layout">
    <?php require __DIR__ . '/../layouts/sidebar.php'; ?>
    <div>
        <div class="page-header">
            <h1>Users</h1>
        </div>

        <?php if (empty($users)): ?>
            <div class="card">
                <div class="card-body text-center text-muted">
                    <p>No users found.</p>
                </div>
            </div>
        <?php else: ?>
            <div class="card">
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Joined</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td data-label="Name"><?= htmlspecialchars($user['name']) ?></td>
                                    <td data-label="Email"><?= htmlspecialchars($user['email']) ?></td>
                                    <td data-label="Role">
                                        <span class="role-badge role-<?= str_replace('_', '-', $user['role']) ?>">
                                            <?= htmlspecialchars($user['role']) ?>
                                        </span>
                                    </td>
                                    <td data-label="Joined"><?= date('M j, Y', strtotime($user['created_at'])) ?></td>
                                    <td data-label="Actions" class="action-cell">
                                        <form method="POST" action="/admin/users/role/<?= $user['id'] ?>" style="display:inline;">
                                            <input type="hidden" name="csrf_token" value="<?= \App\Core\Auth::getCsrfToken() ?>">
                                            <select name="role" class="form-control role-select" data-original-role="<?= $user['role'] ?>" style="width:auto;display:inline;min-height:auto;padding:0.3rem 0.5rem;font-size:0.8rem;">
                                                <option value="viewer" <?= $user['role'] === 'viewer' ? 'selected' : '' ?>>Viewer</option>
                                                <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                                                <option value="super_admin" <?= $user['role'] === 'super_admin' ? 'selected' : '' ?>>Super Admin</option>
                                            </select>
                                            <button type="submit" class="btn btn-sm btn-secondary">Change</button>
                                        </form>
                                        <?php if ($user['role'] !== 'super_admin'): ?>
                                        <form method="POST" action="/admin/users/delete/<?= $user['id'] ?>" style="display:inline;">
                                            <input type="hidden" name="csrf_token" value="<?= \App\Core\Auth::getCsrfToken() ?>">
                                            <button type="submit" class="btn btn-sm btn-danger" data-confirm="Delete this user permanently?">Delete</button>
                                        </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
