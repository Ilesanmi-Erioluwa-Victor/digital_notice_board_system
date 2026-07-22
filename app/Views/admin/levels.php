<div class="admin-layout">
    <?php require __DIR__ . '/../layouts/sidebar.php'; ?>
    <div>
        <div class="page-header">
            <h1>Academic Levels</h1>
            <button class="btn btn-primary" onclick="document.getElementById('new-form').classList.toggle('hidden')">+ Add Level</button>
        </div>
        <div id="new-form" class="card mb-2" style="display:none;">
            <div class="card-body">
                <form method="POST" action="/admin/levels/create">
                    <input type="hidden" name="csrf_token" value="<?= \App\Core\Auth::getCsrfToken() ?>">
                    <div class="form-row">
                        <div class="form-group">
                            <label>Name *</label>
                            <input type="text" name="name" class="form-control" required placeholder="e.g., 100 Level">
                        </div>
                        <div class="form-group">
                            <label>Sort Order</label>
                            <input type="number" name="sort_order" class="form-control" value="0" min="0">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-success">Save Level</button>
                </form>
            </div>
        </div>
        <?php if (empty($levels)): ?>
            <div class="card"><div class="card-body text-center text-muted"><p>No levels yet.</p></div></div>
        <?php else: ?>
            <div class="card">
                <div class="table-wrapper">
                    <table>
                        <thead><tr><th>Name</th><th>Sort Order</th><th>Actions</th></tr></thead>
                        <tbody>
                        <?php foreach ($levels as $l): ?>
                            <tr>
                                <td data-label="Name"><?= htmlspecialchars($l['name']) ?></td>
                                <td data-label="Order"><?= (int)($l['sort_order'] ?? 0) ?></td>
                                <td data-label="Actions" class="action-cell">
                                    <form method="POST" action="/admin/levels/edit/<?= $l['id'] ?>" style="display:inline-flex;gap:0.25rem;">
                                        <input type="hidden" name="csrf_token" value="<?= \App\Core\Auth::getCsrfToken() ?>">
                                        <input type="text" name="name" value="<?= htmlspecialchars($l['name']) ?>" class="form-control" style="width:100px;min-height:auto;padding:0.3rem 0.5rem;font-size:0.8rem;" required>
                                        <button type="submit" class="btn btn-sm btn-secondary">Rename</button>
                                    </form>
                                    <form method="POST" action="/admin/levels/delete/<?= $l['id'] ?>" style="display:inline;">
                                        <input type="hidden" name="csrf_token" value="<?= \App\Core\Auth::getCsrfToken() ?>">
                                        <button type="submit" class="btn btn-sm btn-danger" data-confirm="Delete this level?">Delete</button>
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
<style>.hidden{display:none;}</style>
