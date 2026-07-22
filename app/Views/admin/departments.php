<div class="admin-layout">
    <?php require __DIR__ . '/../layouts/sidebar.php'; ?>
    <div>
        <div class="page-header">
            <h1>Departments</h1>
            <button class="btn btn-primary" onclick="document.getElementById('new-form').classList.toggle('hidden')">+ Add Department</button>
        </div>
        <div id="new-form" class="card mb-2" style="display:none;">
            <div class="card-body">
                <form method="POST" action="/admin/departments/create">
                    <input type="hidden" name="csrf_token" value="<?= \App\Core\Auth::getCsrfToken() ?>">
                    <div class="form-row">
                        <div class="form-group">
                            <label>Name *</label>
                            <input type="text" name="name" class="form-control" required placeholder="e.g., Computer Science">
                        </div>
                        <div class="form-group">
                            <label>Code *</label>
                            <input type="text" name="code" class="form-control" required placeholder="e.g., CSC">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Faculty</label>
                        <select name="faculty_id" class="form-control">
                            <option value="">Select Faculty</option>
                            <?php foreach ($faculties as $f): ?>
                                <option value="<?= $f['id'] ?>"><?= htmlspecialchars($f['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" class="form-control" placeholder="Optional"></textarea>
                    </div>
                    <button type="submit" class="btn btn-success">Save Department</button>
                </form>
            </div>
        </div>
        <?php if (empty($departments)): ?>
            <div class="card"><div class="card-body text-center text-muted"><p>No departments yet.</p></div></div>
        <?php else: ?>
            <div class="card">
                <div class="table-wrapper">
                    <table>
                        <thead><tr><th>Code</th><th>Name</th><th>Faculty</th><th>Description</th><th>Actions</th></tr></thead>
                        <tbody>
                        <?php foreach ($departments as $d): ?>
                            <tr>
                                <td data-label="Code"><?= htmlspecialchars($d['code']) ?></td>
                                <td data-label="Name"><?= htmlspecialchars($d['name']) ?></td>
                                <td data-label="Faculty"><?= htmlspecialchars($d['faculty_name'] ?? '—') ?></td>
                                <td data-label="Description"><?= htmlspecialchars($d['description'] ?? '—') ?></td>
                                <td data-label="Actions" class="action-cell">
                                    <form method="POST" action="/admin/departments/edit/<?= $d['id'] ?>" style="display:inline-flex;gap:0.25rem;">
                                        <input type="hidden" name="csrf_token" value="<?= \App\Core\Auth::getCsrfToken() ?>">
                                        <input type="text" name="name" value="<?= htmlspecialchars($d['name']) ?>" class="form-control" style="width:120px;min-height:auto;padding:0.3rem 0.5rem;font-size:0.8rem;" required>
                                        <button type="submit" class="btn btn-sm btn-secondary">Rename</button>
                                    </form>
                                    <form method="POST" action="/admin/departments/delete/<?= $d['id'] ?>" style="display:inline;">
                                        <input type="hidden" name="csrf_token" value="<?= \App\Core\Auth::getCsrfToken() ?>">
                                        <button type="submit" class="btn btn-sm btn-danger" data-confirm="Delete this department?">Delete</button>
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
