<div class="admin-layout">
    <?php require __DIR__ . '/../layouts/sidebar.php'; ?>
    <div>
        <div class="page-header">
            <h1><?= isset($notice) ? 'Edit Notice' : 'Create Notice' ?></h1>
        </div>

        <div class="card notice-form-wrapper">
            <div class="card-body">
                <form id="notice-form" method="POST" action="<?= isset($notice) ? '/admin/notices/edit/' . $notice['id'] : '/admin/notices/create' ?>" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?= \App\Core\Auth::getCsrfToken() ?>">

                    <div class="form-group">
                        <label for="notice-title">Title *</label>
                        <input type="text" id="notice-title" name="title" class="form-control" required
                               value="<?= htmlspecialchars($notice['title'] ?? '') ?>"
                               placeholder="Enter notice title">
                    </div>

                    <div class="form-group">
                        <label for="notice-body">Body *</label>
                        <textarea id="notice-body" name="body" class="form-control" required
                                  placeholder="Enter notice content..."><?= htmlspecialchars($notice['body'] ?? '') ?></textarea>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="category_id">Category</label>
                            <select id="category_id" name="category_id" class="form-control">
                                <option value="">Select Category</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>" <?= (isset($notice) && (int)($notice['category_id'] ?? 0) === $cat['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($cat['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="priority">Priority</label>
                            <select id="priority" name="priority" class="form-control">
                                <option value="normal" <?= (isset($notice) && ($notice['priority'] ?? '') === 'normal') ? 'selected' : '' ?>>Normal</option>
                                <option value="urgent" <?= (isset($notice) && ($notice['priority'] ?? '') === 'urgent') ? 'selected' : '' ?>>Urgent</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="status">Status</label>
                            <select id="status" name="status" class="form-control">
                                <option value="draft" <?= (isset($notice) && ($notice['status'] ?? '') === 'draft') ? 'selected' : '' ?>>Draft</option>
                                <option value="published" <?= (isset($notice) && ($notice['status'] ?? '') === 'published') ? 'selected' : '' ?>>Published</option>
                                <option value="archived" <?= (isset($notice) && ($notice['status'] ?? '') === 'archived') ? 'selected' : '' ?>>Archived</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="publish_at">Publish At</label>
                            <input type="datetime-local" id="publish_at" name="publish_at" class="form-control"
                                   value="<?= isset($notice['publish_at']) ? date('Y-m-d\TH:i', strtotime($notice['publish_at'])) : '' ?>">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="expires_at">Expires At</label>
                            <input type="datetime-local" id="expires_at" name="expires_at" class="form-control"
                                   value="<?= isset($notice['expires_at']) ? date('Y-m-d\TH:i', strtotime($notice['expires_at'])) : '' ?>">
                        </div>
                        <div class="form-group">
                            <label for="attachment">Attachment (PDF, JPG, PNG — max 5MB)</label>
                            <input type="file" id="attachment" name="attachment" class="form-control"
                                   accept=".pdf,.jpg,.png"
                                   data-file-validate="true"
                                   data-max-size="<?= UPLOAD_MAX_SIZE ?>"
                                   data-allowed-types="pdf,jpg,png">
                            <?php if (!empty($attachments)): ?>
                                <p class="text-muted mt-1" style="font-size:0.85rem;">
                                    Current: <?= htmlspecialchars($attachments[0]['file_path']) ?>
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <?= isset($notice) ? 'Update Notice' : 'Create Notice' ?>
                        </button>
                        <a href="/admin/notices" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
