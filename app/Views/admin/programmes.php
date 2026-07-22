<div class="admin-layout">
    <?php require __DIR__ . '/../layouts/sidebar.php'; ?>
    <div>
        <div class="page-header">
            <h1>Programmes</h1>
            <button class="btn btn-primary" onclick="document.getElementById('new-form').classList.toggle('hidden')">+ Add Programme</button>
        </div>
        <div id="new-form" class="card mb-2" style="display:none;">
            <div class="card-body">
                <form method="POST" action="/admin/programmes/create">
                    <input type="hidden" name="csrf_token" value="<?= \App\Core\Auth::getCsrfToken() ?>">
                    <div class="form-row">
                        <div class="form-group">
                            <label>Name *</label>
                            <input type="text" name="name" class="form-control" required placeholder="e.g., B.Sc. Computer Science">
                        </div>
                        <div class="form-group">
                            <label>Code *</label>
                            <input type="text" name="code" class="form-control" required placeholder="e.g., BSC-CSC">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Department</label>
                            <select name="department_id" class="form-control">
                                <option value="">Select Department</option>
                                <?php foreach ($departments as $d): ?>
                                    <option value="<?= $d['id'] ?>"><?= htmlspecialchars($d['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Duration (years)</label>
                            <input type="number" name="duration_years" class="form-control" value="4" min="1" max="7">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-success">Save Programme</button>
                </form>
            </div>
        </div>
        <?php if (empty($programmes)): ?>
            <div class="card"><div class="card-body text-center text-muted"><p>No programmes yet.</p></div></div>
        <?php else: ?>
            <div class="card">
                <div class="table-wrapper">
                    <table>
                        <thead><tr><th>Code</th><th>Name</th><th>Department</th><th>Duration</th><th>Actions</th></tr></thead>
                        <tbody>
                        <?php foreach ($programmes as $p): ?>
                            <tr>
                                <td data-label="Code"><?= htmlspecialchars($p['code']) ?></td>
                                <td data-label="Name"><?= htmlspecialchars($p['name']) ?></td>
                                <td data-label="Department"><?= htmlspecialchars($p['department_name'] ?? '—') ?></td>
                                <td data-label="Duration"><?= (int)($p['duration_years'] ?? 0) ?> yrs</td>
                                <td data-label="Actions" class="action-cell">
                                    <form method="POST" action="/admin/programmes/edit/<?= $p['id'] ?>" style="display:inline-flex;gap:0.25rem;">
                                        <input type="hidden" name="csrf_token" value="<?= \App\Core\Auth::getCsrfToken() ?>">
                                        <input type="text" name="name" value="<?= htmlspecialchars($p['name']) ?>" class="form-control" style="width:140px;min-height:auto;padding:0.3rem 0.5rem;font-size:0.8rem;" required>
                                        <button type="submit" class="btn btn-sm btn-secondary">Rename</button>
                                    </form>
                                    <form method="POST" action="/admin/programmes/delete/<?= $p['id'] ?>" style="display:inline;">
                                        <input type="hidden" name="csrf_token" value="<?= \App\Core\Auth::getCsrfToken() ?>">
                                        <button type="submit" class="btn btn-sm btn-danger" data-confirm="Delete this programme?">Delete</button>
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
