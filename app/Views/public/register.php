<div class="auth-page">
    <div class="card auth-card">
        <div class="card-body">
            <h1>Create Student Account</h1>
            <p class="text-muted" style="margin-bottom:1.25rem;font-size:0.9rem;">Register to view notices and track your reading activity.</p>

            <?php if (!empty($_SESSION['error'])): ?>
                <div class="toast toast-error" style="margin-bottom:1rem;">
                    <?= htmlspecialchars($_SESSION['error']) ?>
                    <?php unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="/register">
                <input type="hidden" name="csrf_token" value="<?= \App\Core\Auth::getCsrfToken() ?>">

                <div class="form-group">
                    <label for="name">Full Name</label>
                    <input type="text" id="name" name="name" class="form-control" required
                           placeholder="e.g. John Doe" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" class="form-control" required
                           placeholder="you@example.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label for="student_id">Student ID</label>
                    <input type="text" id="student_id" name="student_id" class="form-control"
                           placeholder="e.g. STU2024001" value="<?= htmlspecialchars($_POST['student_id'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label for="department_id">Department</label>
                    <select id="department_id" name="department_id" class="form-control">
                        <option value="">Select Department</option>
                        <?php
                        $db = \App\Core\Database::getInstance();
                        $departments = $db->fetchAll('SELECT id, name, code FROM departments ORDER BY name');
                        foreach ($departments as $dept):
                            $sel = (isset($_POST['department_id']) && (int)$_POST['department_id'] === (int)$dept['id']) ? 'selected' : '';
                        ?>
                            <option value="<?= (int)$dept['id'] ?>" <?= $sel ?>>
                                <?= htmlspecialchars($dept['name']) ?> (<?= htmlspecialchars($dept['code']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="programme_id">Programme</label>
                    <select id="programme_id" name="programme_id" class="form-control">
                        <option value="">Select Programme</option>
                        <?php
                        $programmes = $db->fetchAll('SELECT id, name, code FROM programmes ORDER BY name');
                        foreach ($programmes as $prog):
                            $sel = (isset($_POST['programme_id']) && (int)$_POST['programme_id'] === (int)$prog['id']) ? 'selected' : '';
                        ?>
                            <option value="<?= (int)$prog['id'] ?>" <?= $sel ?>>
                                <?= htmlspecialchars($prog['name']) ?> (<?= htmlspecialchars($prog['code']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="level_id">Level</label>
                    <select id="level_id" name="level_id" class="form-control">
                        <option value="">Select Level</option>
                        <?php
                        $levels = $db->fetchAll('SELECT id, name FROM levels ORDER BY sort_order');
                        foreach ($levels as $lev):
                            $sel = (isset($_POST['level_id']) && (int)$_POST['level_id'] === (int)$lev['id']) ? 'selected' : '';
                        ?>
                            <option value="<?= (int)$lev['id'] ?>" <?= $sel ?>>
                                <?= htmlspecialchars($lev['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" class="form-control" required
                           placeholder="Create a password (min. 6 characters)" minlength="6">
                </div>

                <button type="submit" class="btn btn-primary w-full">Create Account</button>
            </form>

            <p class="text-center mt-2" style="font-size:0.85rem;">
                Already have an account? <a href="/login">Sign In</a>
            </p>
        </div>
    </div>
</div>