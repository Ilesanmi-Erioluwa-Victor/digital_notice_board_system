<div class="auth-page">
    <div class="card auth-card">
        <div class="card-body">
            <h1>Sign In</h1>

            <?php if (!empty($_SESSION['error'])): ?>
                <div class="toast toast-error" style="margin-bottom:1rem;">
                    <?= htmlspecialchars($_SESSION['error']) ?>
                    <?php unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="/login">
                <input type="hidden" name="csrf_token" value="<?= \App\Core\Auth::getCsrfToken() ?>">

                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" class="form-control" required
                           placeholder="you@example.com" autocomplete="email">
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" class="form-control" required
                           placeholder="Enter your password" autocomplete="current-password">
                </div>

                <button type="submit" class="btn btn-primary w-full">Sign In</button>
            </form>

            <p class="text-center text-muted mt-2" style="font-size:0.85rem;">
                Demo: admin@example.com / password123
            </p>
        </div>
    </div>
</div>
