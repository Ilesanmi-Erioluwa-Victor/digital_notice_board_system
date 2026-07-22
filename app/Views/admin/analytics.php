<div class="admin-layout">
    <?php require __DIR__ . '/../layouts/sidebar.php'; ?>
    <div>
        <div class="page-header"><h1>Analytics</h1></div>
        <div class="analytics-summary stats-grid" id="analytics-summary">
            <div class="card stat-card"><div class="stat-value" id="stat-total">—</div><div class="stat-label">Total Notices</div></div>
            <div class="card stat-card"><div class="stat-value" id="stat-views">—</div><div class="stat-label">Total Views</div></div>
            <div class="card stat-card"><div class="stat-value" id="stat-active">—</div><div class="stat-label">Active Users</div></div>
            <div class="card stat-card"><div class="stat-value" id="stat-categories">—</div><div class="stat-label">Categories</div></div>
        </div>
        <div class="card mt-2"><div class="card-body"><h3>Monthly Notices</h3><div class="chart-container" id="monthly-chart"><p class="text-muted">Loading...</p></div></div></div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-top:1rem;">
            <div class="card"><div class="card-body"><h3>Category Distribution</h3><div class="chart-container" id="category-chart"><p class="text-muted">Loading...</p></div></div></div>
            <div class="card"><div class="card-body"><h3>Status Breakdown</h3><div class="chart-container" id="status-chart"><p class="text-muted">Loading...</p></div></div></div>
        </div>
        <div class="card mt-2"><div class="card-body"><h3>Most Viewed Notices</h3><div class="chart-container" id="most-viewed-chart"><p class="text-muted">Loading...</p></div></div></div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    function fetchJson(url) { return fetch(url).then(function(r){ return r.json(); }); }
    function renderBarChart(containerId, data, labelKey, valueKey, maxWidth) {
        var el = document.getElementById(containerId); if (!el) return;
        if (!data || data.length === 0) { el.innerHTML = '<p class="text-muted">No data</p>'; return; }
        var max = 0; data.forEach(function(d) { if (d[valueKey] > max) max = d[valueKey]; });
        max = max || 1;
        var html = '<div class="bar-chart">';
        data.forEach(function(d) {
            var pct = (d[valueKey] / max) * 100;
            html += '<div class="bar-item"><span class="bar-label">' + d[labelKey] + '</span>' +
                    '<div class="bar-fill"><div class="bar-fill-inner" style="width:' + pct + '%"></div></div>' +
                    '<span class="bar-value">' + d[valueKey] + '</span></div>';
        });
        html += '</div>';
        el.innerHTML = html;
    }
    fetchJson('/api/analytics/monthly').then(function(d) { renderBarChart('monthly-chart', d, 'month', 'count'); });
    fetchJson('/api/analytics/categories').then(function(d) { renderBarChart('category-chart', d, 'name', 'count'); });
    fetchJson('/api/analytics/status-breakdown').then(function(d) { renderBarChart('status-chart', d, 'status', 'count'); });
    fetchJson('/api/analytics/most-viewed').then(function(d) { renderBarChart('most-viewed-chart', d, 'title', 'views'); });
});
</script>
