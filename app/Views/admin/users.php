<div class="admin-layout">
    <?php require __DIR__ . '/../layouts/sidebar.php'; ?>
    <div>
        <div class="page-header">
            <h1>Users</h1>
            <button type="button" class="btn btn-primary" onclick="document.getElementById('add-user-form').classList.toggle('hidden')">+ Add User</button>
        </div>

        <div id="add-user-form" class="card mt-2 <?= !empty($errors) ? '' : 'hidden' ?>">
            <div class="card-body">
                <h3>Add New User</h3>
                <form method="POST" action="/admin/users/create">
                    <input type="hidden" name="csrf_token" value="<?= \App\Core\Auth::getCsrfToken() ?>">

                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <?php foreach ($errors as $error): ?>
                                <p><?= htmlspecialchars($error) ?></p>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="add-name">Name *</label>
                            <input type="text" id="add-name" name="name" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="add-email">Email *</label>
                            <input type="email" id="add-email" name="email" class="form-control" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="add-password">Password *</label>
                            <input type="password" id="add-password" name="password" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="add-role">Role *</label>
                            <select id="add-role" name="role" class="form-control" required>
                                <option value="student">Student</option>
                                <option value="staff">Staff</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="add-department">Department</label>
                            <select id="add-department" name="department_id" class="form-control">
                                <option value="">Select Department</option>
                                <?php foreach ($departments ?? [] as $dept): ?>
                                    <option value="<?= $dept['id'] ?>"><?= htmlspecialchars($dept['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="add-staff-id">Staff / Student ID</label>
                            <input type="text" id="add-staff-id" name="staff_id" class="form-control" placeholder="Staff or Student ID">
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">Add User</button>
                        <button type="button" class="btn btn-secondary" onclick="document.getElementById('add-user-form').classList.add('hidden')">Cancel</button>
                    </div>
                </form>
            </div>
        </div>

        <?php if (empty($users)): ?>
            <div class="card mt-2">
                <div class="card-body text-center text-muted">
                    <p>No users found.</p>
                </div>
            </div>
        <?php else: ?>
            <div class="card mt-2">
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Department</th>
                                <th>ID</th>
                                <th>Active</th>
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
                                            <?= htmlspecialchars(ucfirst($user['role'])) ?>
                                        </span>
                                    </td>
                                    <td data-label="Department"><?= htmlspecialchars($user['department_name'] ?? '—') ?></td>
                                    <td data-label="ID"><?= htmlspecialchars($user['staff_id'] ?? $user['student_id'] ?? '—') ?></td>
                                    <td data-label="Active">
                                        <?php if (!empty($user['is_active'])): ?>
                                            <span class="badge badge-success">Active</span>
                                        <?php else: ?>
                                            <span class="badge badge-urgent">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td data-label="Joined"><?= date('M j, Y', strtotime($user['created_at'])) ?></td>
                                    <td data-label="Actions" class="action-cell">
                                        <form method="POST" action="/admin/users/toggle-active/<?= $user['id'] ?>" style="display:inline;">
                                            <input type="hidden" name="csrf_token" value="<?= \App\Core\Auth::getCsrfToken() ?>">
                                            <button type="submit" class="btn btn-sm <?= !empty($user['is_active']) ? 'btn-warning' : 'btn-success' ?>">
                                                <?= !empty($user['is_active']) ? 'Deactivate' : 'Activate' ?>
                                            </button>
                                        </form>
                                        <form method="POST" action="/admin/users/role/<?= $user['id'] ?>" style="display:inline;">
                                            <input type="hidden" name="csrf_token" value="<?= \App\Core\Auth::getCsrfToken() ?>">
                                            <select name="role" class="form-control role-select" data-original-role="<?= $user['role'] ?>" style="width:auto;display:inline;min-height:auto;padding:0.3rem 0.5rem;font-size:0.8rem;">
                                                <option value="student" <?= $user['role'] === 'student' ? 'selected' : '' ?>>Student</option>
                                                <option value="staff" <?= $user['role'] === 'staff' ? 'selected' : '' ?>>Staff</option>
                                                <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                                            </select>
                                            <button type="submit" class="btn btn-sm btn-secondary">Change</button>
                                        </form>
                                        <?php if ($user['role'] !== 'admin'): ?>
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
