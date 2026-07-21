<div class="admin-layout">
    <?php require __DIR__ . '/../layouts/sidebar.php'; ?>
    <div>
        <div class="page-header">
            <h1>Categories</h1>
            <button class="btn btn-primary" onclick="document.getElementById('new-category-form').classList.toggle('hidden')">+ New Category</button>
        </div>

        <div id="new-category-form" class="card mb-2" style="display:none;">
            <div class="card-body">
                <form id="category-form" method="POST" action="/admin/categories/create">
                    <input type="hidden" name="csrf_token" value="<?= \App\Core\Auth::getCsrfToken() ?>">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="category-name">Category Name *</label>
                            <input type="text" id="category-name" name="name" class="form-control" required placeholder="e.g., Academic">
                        </div>
                        <div class="form-group">
                            <label for="category-desc">Description</label>
                            <input type="text" id="category-desc" name="description" class="form-control" placeholder="Brief description">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-success">Save Category</button>
                </form>
            </div>
        </div>

        <?php if (empty($categories)): ?>
            <div class="card">
                <div class="card-body text-center text-muted">
                    <p>No categories yet. Create one above.</p>
                </div>
            </div>
        <?php else: ?>
            <div class="card">
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Description</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($categories as $cat): ?>
                                <tr>
                                    <td data-label="Name"><?= htmlspecialchars($cat['name']) ?></td>
                                    <td data-label="Description"><?= htmlspecialchars($cat['description'] ?? '—') ?></td>
                                    <td data-label="Actions" class="action-cell">
                                        <form method="POST" action="/admin/categories/edit/<?= $cat['id'] ?>" style="display:inline;">
                                            <input type="hidden" name="csrf_token" value="<?= \App\Core\Auth::getCsrfToken() ?>">
                                            <input type="text" name="name" value="<?= htmlspecialchars($cat['name']) ?>" required class="form-control" style="width:140px;display:inline;min-height:auto;padding:0.3rem 0.5rem;font-size:0.8rem;">
                                            <button type="submit" class="btn btn-sm btn-secondary">Rename</button>
                                        </form>
                                        <form method="POST" action="/admin/categories/delete/<?= $cat['id'] ?>" style="display:inline;">
                                            <input type="hidden" name="csrf_token" value="<?= \App\Core\Auth::getCsrfToken() ?>">
                                            <button type="submit" class="btn btn-sm btn-danger" data-confirm="Delete this category?">Delete</button>
                                        </form>
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
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Toggle new category form
    var newForm = document.getElementById('new-category-form');
    // Show form if there was a validation error
});
</script>
<style>
.hidden { display: none; }
</style>
