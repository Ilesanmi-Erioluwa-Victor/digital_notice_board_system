<div class="admin-layout">
    <?php require __DIR__ . '/../layouts/sidebar.php'; ?>
    <div>
        <div class="page-header">
            <h1><?= isset($notice) ? 'Edit Notice' : 'Create Notice' ?></h1>
        </div>

        <?php if (isset($notice)): ?>
        <div class="card mt-2 mb-2" style="border-left: 4px solid var(--color-info);">
            <div class="card-body flex flex-wrap gap-2 items-center">
                <span><strong>Status:</strong> <?= htmlspecialchars($notice['status'] ?? 'draft') ?></span>
                <?php if (!empty($notice['approval_status'])): ?>
                    <span><strong>Approval:</strong> <?= htmlspecialchars(ucfirst($notice['approval_status'])) ?></span>
                <?php endif; ?>
                <?php if (!empty($notice['approval_comment'])): ?>
                    <span><strong>Note:</strong> <?= htmlspecialchars($notice['approval_comment']) ?></span>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

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
                                <option value="low" <?= (isset($notice) && ($notice['priority'] ?? '') === 'low') ? 'selected' : '' ?>>Low</option>
                                <option value="medium" <?= (isset($notice) && ($notice['priority'] ?? '') === 'medium') ? 'selected' : '' ?>>Medium</option>
                                <option value="high" <?= (isset($notice) && ($notice['priority'] ?? '') === 'high') ? 'selected' : '' ?>>High</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <?php if (isset($notice)): ?>
                        <div class="form-group">
                            <label for="status">Status</label>
                            <select id="status" name="status" class="form-control">
                                <option value="draft" <?= ($notice['status'] ?? '') === 'draft' ? 'selected' : '' ?>>Draft</option>
                                <option value="pending" <?= ($notice['status'] ?? '') === 'pending' ? 'selected' : '' ?>>Pending</option>
                                <option value="published" <?= ($notice['status'] ?? '') === 'published' ? 'selected' : '' ?>>Published</option>
                                <option value="archived" <?= ($notice['status'] ?? '') === 'archived' ? 'selected' : '' ?>>Archived</option>
                            </select>
                        </div>
                        <?php endif; ?>
                        <div class="form-group">
                            <label>Publishing</label>
                            <div style="display:flex;gap:0.75rem;padding-top:0.35rem;">
                                <label class="checkbox-label" style="font-weight:500;">
                                    <input type="radio" name="publish_choice" value="now"
                                           <?= (!isset($notice) || empty($notice['publish_at']) || date('Y-m-d\TH:i', strtotime($notice['publish_at'])) <= date('Y-m-d\TH:i')) ? 'checked' : '' ?>
                                           onchange="document.getElementById('publish_at').disabled=true;document.getElementById('publish_at').value='';">
                                    Post Now
                                </label>
                                <label class="checkbox-label" style="font-weight:500;">
                                    <input type="radio" name="publish_choice" value="schedule"
                                           <?= (isset($notice) && !empty($notice['publish_at']) && date('Y-m-d\TH:i', strtotime($notice['publish_at'])) > date('Y-m-d\TH:i')) ? 'checked' : '' ?>
                                           onchange="document.getElementById('publish_at').disabled=false;">
                                    Schedule
                                </label>
                            </div>
                            <input type="datetime-local" id="publish_at" name="publish_at" class="form-control" style="margin-top:0.5rem;"
                                   value="<?= isset($notice['publish_at']) ? date('Y-m-d\TH:i', strtotime($notice['publish_at'])) : '' ?>"
                                   <?= (!isset($notice) || empty($notice['publish_at']) || date('Y-m-d\TH:i', strtotime($notice['publish_at'])) <= date('Y-m-d\TH:i')) ? 'disabled' : '' ?>>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="target_audience_type">Target Audience</label>
                        <select id="target_audience_type" name="target_audience_type" class="form-control">
                            <option value="everyone" <?= (isset($notice) && ($notice['target_audience_type'] ?? '') === 'everyone') ? 'selected' : '' ?>>Everyone</option>
                            <option value="faculty" <?= (isset($notice) && ($notice['target_audience_type'] ?? '') === 'faculty') ? 'selected' : '' ?>>Faculty</option>
                            <option value="department" <?= (isset($notice) && ($notice['target_audience_type'] ?? '') === 'department') ? 'selected' : '' ?>>Department</option>
                            <option value="programme" <?= (isset($notice) && ($notice['target_audience_type'] ?? '') === 'programme') ? 'selected' : '' ?>>Programme</option>
                            <option value="level" <?= (isset($notice) && ($notice['target_audience_type'] ?? '') === 'level') ? 'selected' : '' ?>>Level</option>
                            <option value="staff" <?= (isset($notice) && ($notice['target_audience_type'] ?? '') === 'staff') ? 'selected' : '' ?>>Staff</option>
                            <option value="students" <?= (isset($notice) && ($notice['target_audience_type'] ?? '') === 'students') ? 'selected' : '' ?>>Students</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="target_ids">Target IDs <span class="text-muted">(comma-separated IDs, e.g. department IDs, programme IDs)</span></label>
                        <input type="text" id="target_ids" name="target_ids" class="form-control"
                               value="<?= htmlspecialchars($notice['target_ids'] ?? '') ?>"
                               placeholder="Leave blank for all">
                    </div>

                    <div class="form-group">
                        <label class="checkbox-label">
                            <input type="checkbox" name="is_pinned" value="1" <?= (isset($notice) && !empty($notice['is_pinned'])) ? 'checked' : '' ?>>
                            Pin this notice
                        </label>
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
