<div class="admin-layout">
    <?php require __DIR__ . '/../layouts/sidebar.php'; ?>
    <div>
        <div class="page-header"><h1>Reports</h1></div>
        <div class="card report-form" style="max-width:600px;">
            <div class="card-body">
                <p class="text-muted mb-2">Generate downloadable reports for notices, users, and analytics.</p>
                <form method="POST" action="/admin/reports/generate" target="_blank">
                    <input type="hidden" name="csrf_token" value="<?= \App\Core\Auth::getCsrfToken() ?>">
                    <div class="form-group">
                        <label>Report Type</label>
                        <select name="type" class="form-control" required>
                            <option value="notices">Notice Report</option>
                            <option value="users">User Report</option>
                            <option value="analytics">Analytics Report</option>
                        </select>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>From Date</label>
                            <input type="date" name="from_date" class="form-control">
                        </div>
                        <div class="form-group">
                            <label>To Date</label>
                            <input type="date" name="to_date" class="form-control">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Format</label>
                        <div class="toggle-group">
                            <label class="toggle-btn active"><input type="radio" name="format" value="pdf" checked> PDF</label>
                            <label class="toggle-btn"><input type="radio" name="format" value="csv"> CSV</label>
                            <label class="toggle-btn"><input type="radio" name="format" value="excel"> Excel</label>
                        </div>
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">Generate Report</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
