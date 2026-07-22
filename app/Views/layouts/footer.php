        </div>
    </main>
    <footer class="site-footer">
        <div class="container">
            <p>&copy; <?= date('Y') ?> Digital Notice Board System &mdash; All Rights Reserved</p>
        </div>
    </footer>
<?php
$jsVersion = file_exists(__DIR__ . '/../../../public/assets/js/main.js') ? filemtime(__DIR__ . '/../../../public/assets/js/main.js') : time();
$adminJsVersion = file_exists(__DIR__ . '/../../../public/assets/js/admin.js') ? filemtime(__DIR__ . '/../../../public/assets/js/admin.js') : time();
?>
    <script src="/assets/js/main.js?v=<?= $jsVersion ?>"></script>
    <?php if (strpos($_SERVER['REQUEST_URI'] ?? '', '/admin') === 0): ?>
    <script src="/assets/js/admin.js?v=<?= $adminJsVersion ?>"></script>
    <?php endif; ?>
</body>
</html>
