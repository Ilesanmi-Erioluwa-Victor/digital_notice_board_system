<div class="admin-layout">
    <?php require __DIR__ . '/../layouts/sidebar.php'; ?>
    <div>
        <div class="page-header">
            <h1>My Profile</h1>
        </div>
        <div class="card profile-edit-wrapper">
            <div class="card-body">
                <div class="profile-section">
                    <h3>Profile Information</h3>
                    <form method="POST" action="/profile" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?= \App\Core\Auth::getCsrfToken() ?>">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="name">Full Name</label>
                                <input type="text" id="name" name="name" class="form-control"
                                       value="<?= htmlspecialchars($user['name'] ?? '') ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" id="email" class="form-control"
                                       value="<?= htmlspecialchars($user['email'] ?? '') ?>" readonly disabled>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="phone">Phone</label>
                                <input type="text" id="phone" name="phone" class="form-control"
                                       value="<?= htmlspecialchars($user['phone'] ?? '') ?>"
                                       placeholder="+234 XXX XXX XXXX">
                            </div>
                            <div class="form-group">
                                <label>Role</label>
                                <input type="text" class="form-control"
                                       value="<?= htmlspecialchars(ucfirst($user['role'] ?? '')) ?>" readonly disabled>
                            </div>
                        </div>
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">Update Profile</button>
                        </div>
                    </form>
                </div>
                <hr style="margin:2rem 0;border-color:var(--color-border);">
                <div class="profile-section">
                    <h3>Change Password</h3>
                    <form method="POST" action="/profile">
                        <input type="hidden" name="csrf_token" value="<?= \App\Core\Auth::getCsrfToken() ?>">
                        <input type="hidden" name="change_password" value="1">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="current_password">Current Password</label>
                                <input type="password" id="current_password" name="current_password" class="form-control">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="new_password">New Password</label>
                                <input type="password" id="new_password" name="new_password" class="form-control" minlength="8">
                            </div>
                            <div class="form-group">
                                <label for="new_password_confirm">Confirm New Password</label>
                                <input type="password" id="new_password_confirm" name="new_password_confirm" class="form-control" minlength="8">
                            </div>
                        </div>
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">Change Password</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
