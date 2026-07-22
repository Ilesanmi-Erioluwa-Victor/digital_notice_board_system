<div class="admin-layout">
    <?php require __DIR__ . '/../layouts/sidebar.php'; ?>
    <div>
        <div class="page-header">
            <h1>Faculties</h1>
            <button class="btn btn-primary" onclick="document.getElementById('new-form').classList.toggle('hidden')">+ Add Faculty</button>
        </div>
        <div id="new-form" class="card mb-2" style="display:none;">
            <div class="card-body">
                <form method="POST" action="/admin/faculties/create">
                    <input type="hidden" name="csrf_token" value="<?= \App\Core\Auth::getCsrfToken() ?>">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="name">Name *</label>
                            <input type="text" name="name" class="form-control" required placeholder="e.g., Faculty of Science">
                        </div>
                        <div class="form-group">
                            <label for="code">Code *</label>
                            <input type="text" name="code" class="form-control" required placeholder="e.g., SCI">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea name="description" class="form-control" placeholder="Optional description"></textarea>
                    </div>
                    <button type="submit" class="btn btn-success">Save Faculty</button>
                </form>
            </div>
        </div>
        <?php if (empty($faculties)): ?>
            <div class="card"><div class="card-body text-center text-muted"><p>No faculties yet.</p></div></div>
        <?php else: ?>
            <div class="card">
                <div class="table-wrapper">
                    <table>
                        <thead><tr><th>Code</th><th>Name</th><th>Description</th><th>Actions</th></tr></thead>
                        <tbody>
                        <?php foreach ($faculties as $f): ?>
                            <tr>
                                <td data-label="Code"><?= htmlspecialchars($f['code']) ?></td>
                                <td data-label="Name"><?= htmlspecialchars($f['name']) ?></td>
                                <td data-label="Description"><?= htmlspecialchars($f['description'] ?? '—') ?></td>
                                <td data-label="Actions" class="action-cell">
                                    <form method="POST" action="/admin/faculties/edit/<?= $f['id'] ?>" style="display:inline-flex;gap:0.25rem;">
                                        <input type="hidden" name="csrf_token" value="<?= \App\Core\Auth::getCsrfToken() ?>">
                                        <input type="text" name="name" value="<?= htmlspecialchars($f['name']) ?>" class="form-control" style="width:120px;min-height:auto;padding:0.3rem 0.5rem;font-size:0.8rem;" required>
                                        <button type="submit" class="btn btn-sm btn-secondary" title="Rename">Rename</button>
                                    </form>
                                    <form method="POST" action="/admin/faculties/delete/<?= $f['id'] ?>" style="display:inline;">
                                        <input type="hidden" name="csrf_token" value="<?= \App\Core\Auth::getCsrfToken() ?>">
                                        <button type="submit" class="btn btn-sm btn-danger" data-confirm="Delete this faculty? All departments under it will be unlinked.">Delete</button>
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
