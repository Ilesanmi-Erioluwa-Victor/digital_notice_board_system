        </div>
    </main>
    <footer class="site-footer">
        <div class="container">
            <p>&copy; <?= date('Y') ?> Digital Notice Board System &mdash; All Rights Reserved</p>
        </div>
    </footer>
    <script src="/assets/js/main.js"></script>
    <?php if (strpos($_SERVER['REQUEST_URI'] ?? '', '/admin') === 0): ?>
    <script src="/assets/js/admin.js"></script>
    <?php endif; ?>
</body>
</html>
