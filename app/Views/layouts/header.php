<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= htmlspecialchars(\App\Core\Auth::getCsrfToken()) ?>">
    <title>Digital Notice Board</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/style.css">
    <?php if (strpos($_SERVER['REQUEST_URI'] ?? '', '/admin') === 0): ?>
    <link rel="stylesheet" href="/assets/css/admin.css">
    <?php endif; ?>
</head>
<body>
    <header class="site-header">
        <div class="container">
            <a href="/" class="logo">Digital Notice Board</a>
            <button id="hamburger" class="hamburger" aria-expanded="false" aria-label="Toggle navigation">&#9776;</button>
            <nav id="main-nav" class="main-nav">
                <a href="/">Home <span id="unread-badge" class="badge badge-urgent" style="display:none;font-size:0.7rem;padding:0.1rem 0.4rem;vertical-align:middle;">0</span></a>
                <a href="/kiosk">Kiosk</a>
                <?php if (\App\Core\Auth::isLoggedIn()): ?>
                    <?php $currentUser = \App\Core\Auth::currentUser(); ?>
                    <?php if ($currentUser['role'] !== 'viewer'): ?>
                        <a href="/admin/dashboard">Admin</a>
                    <?php endif; ?>
                    <span style="color:rgba(255,255,255,0.7); font-size:0.85rem;">
                        <?= htmlspecialchars($currentUser['name']) ?>
                    </span>
                    <form method="POST" action="/logout" style="display:inline;">
                        <input type="hidden" name="csrf_token" value="<?= \App\Core\Auth::getCsrfToken() ?>">
                        <button type="submit" class="btn btn-sm" style="background:rgba(255,255,255,0.15);color:#fff;border:none;">Logout</button>
                    </form>
                <?php else: ?>
                    <a href="/login">Login</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>
    <main class="main-content">
        <?php if (!empty($_SESSION['success'])): ?>
            <div class="container">
                <div class="flash-message toast toast-success" style="margin-bottom:1rem;">
                    <?= htmlspecialchars($_SESSION['success']) ?>
                </div>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>
        <?php if (!empty($_SESSION['error'])): ?>
            <div class="container">
                <div class="flash-message toast toast-error" style="margin-bottom:1rem;">
                    <?= htmlspecialchars($_SESSION['error']) ?>
                </div>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
        <div id="toast-container" class="toast-container"></div>
        <div class="container">
