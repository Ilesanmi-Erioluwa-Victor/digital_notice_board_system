<!DOCTYPE html>
<html lang="en">
<head>
<script>
(function(){if(localStorage.getItem('dark-mode')==='true')document.documentElement.classList.add('dark-mode')})();
</script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= htmlspecialchars(\App\Core\Auth::getCsrfToken()) ?>">
    <title>Digital Notice Board</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<?php
$docRoot = $_SERVER['DOCUMENT_ROOT'] ?? __DIR__ . '/../../../public';
$cssVersion = file_exists($docRoot . '/assets/css/style.css') ? filemtime($docRoot . '/assets/css/style.css') : time();
$adminCssVersion = file_exists($docRoot . '/assets/css/admin.css') ? filemtime($docRoot . '/assets/css/admin.css') : time();
?>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/style.css?v=<?= $cssVersion ?>">
    <?php if (strpos($_SERVER['REQUEST_URI'] ?? '', '/admin') === 0 || strpos($_SERVER['REQUEST_URI'] ?? '', '/profile') === 0): ?>
    <link rel="stylesheet" href="/assets/css/admin.css?v=<?= $adminCssVersion ?>">
    <?php endif; ?>
    <style>
        :root{--color-background-dark:#0F172A;--color-surface-dark:#1E293B;--color-text-primary-dark:#F1F5F9;--color-text-secondary-dark:#94A3B8;--color-border-dark:#334155;--color-primary-dark:#3B82F6}html.dark-mode{--color-background:var(--color-background-dark);--color-surface:var(--color-surface-dark);--color-text-primary:var(--color-text-primary-dark);--color-text-secondary:var(--color-text-secondary-dark);--color-border:var(--color-border-dark)}html.dark-mode .site-header{background:var(--color-primary-dark)}html.dark-mode .card,html.dark-mode .form-control{background:var(--color-surface-dark);border-color:var(--color-border-dark);color:var(--color-text-primary-dark)}html.dark-mode .card-footer{background:var(--color-background-dark);border-color:var(--color-border-dark)}html.dark-mode .badge-normal{background:#1e3a5f;color:#93c5fd}html.dark-mode .badge-urgent{background:#3b1a1a;color:#fca5a5}html.dark-mode .badge-expiring{background:#3b2f1a;color:#fcd34d}html.dark-mode .badge-published{background:#1a3b2a;color:#86efac}html.dark-mode .badge-draft{background:#1e293b;color:#94a3b8}html.dark-mode .badge-archived{background:#1e293b;color:#94a3b8}html.dark-mode table th{background:var(--color-surface-dark)}html.dark-mode table tr:hover td{background:#334155}html.dark-mode .toast-success{background:#1a3b2a;color:#86efac;border-color:#166534}html.dark-mode .toast-error{background:#3b1a1a;color:#fca5a5;border-color:#7f1d1d}html.dark-mode .toast-info{background:#1e3a5f;color:#93c5fd;border-color:#1e40af}#dark-mode-toggle{background:none;border:1px solid rgba(255,255,255,0.3);border-radius:50%;width:36px;height:36px;display:inline-flex;align-items:center;justify-content:center;cursor:pointer;color:#fff;font-size:1.1rem;line-height:1;padding:0;transition:background .2s;min-width:36px;min-height:36px}#dark-mode-toggle:hover{background:rgba(255,255,255,0.15)}
    </style>
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
                    <a href="/profile" style="color:rgba(255,255,255,0.9);font-size:0.9rem;"><?= htmlspecialchars($currentUser['name']) ?></a>
                    <?php if (\App\Core\Auth::hasAnyRole(['admin', 'staff'])): ?>
                        <a href="/admin/dashboard">Admin</a>
                        <a href="/admin/notices">My Notices</a>
                        <a href="/admin/notices/create">Submit Notice</a>
                    <?php endif; ?>
                    <?php if (\App\Core\Auth::isLoggedIn()): ?>
                        <a href="/bookmarks">Bookmarks</a>
                        <a href="/archived">Archived</a>
                    <?php endif; ?>
                    <button id="dark-mode-toggle" aria-label="Toggle dark mode">&#9790;</button>
                    <form method="POST" action="/logout" style="display:inline;">
                        <input type="hidden" name="csrf_token" value="<?= \App\Core\Auth::getCsrfToken() ?>">
                        <button type="submit" class="btn btn-sm" style="background:rgba(255,255,255,0.15);color:#fff;border:none;">Logout</button>
                    </form>
                <?php else: ?>
                    <button id="dark-mode-toggle" aria-label="Toggle dark mode">&#9790;</button>
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
<script>
(function(){function s(v){localStorage.setItem('dark-mode',v);document.documentElement.classList.toggle('dark-mode',v==='true')}var b=document.getElementById('dark-mode-toggle');if(b){var d=localStorage.getItem('dark-mode');b.innerHTML=d==='true'?'&#9728;':'&#9790;';b.addEventListener('click',function(){var n=localStorage.getItem('dark-mode')==='true'?'false':'true';s(n);this.innerHTML=n==='true'?'&#9728;':'&#9790;'})}})();
</script>
