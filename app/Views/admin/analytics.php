<div class="admin-layout">
    <?php require __DIR__ . '/../layouts/sidebar.php'; ?>
    <div>
        <div class="page-header">
            <div>
                <h1>Analytics Dashboard</h1>
                <p class="text-muted" style="margin-top:0.25rem;">Overview of notice board activity and engagement</p>
            </div>
        </div>

        <div class="analytics-summary" id="analytics-summary">
            <div class="stat-card">
                <div class="stat-icon stat-icon-blue">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                </div>
                <div class="stat-card-body">
                    <span class="stat-value" id="stat-total">—</span>
                    <span class="stat-label">Total Notices</span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon stat-icon-green">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                </div>
                <div class="stat-card-body">
                    <span class="stat-value" id="stat-views">—</span>
                    <span class="stat-label">Total Views</span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon stat-icon-purple">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                </div>
                <div class="stat-card-body">
                    <span class="stat-value" id="stat-active">—</span>
                    <span class="stat-label">Active Users</span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon stat-icon-amber">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
                </div>
                <div class="stat-card-body">
                    <span class="stat-value" id="stat-categories">—</span>
                    <span class="stat-label">Categories</span>
                </div>
            </div>
        </div>

        <div class="analytics-grid">
            <div class="chart-card chart-card-full">
                <div class="chart-card-header">
                    <h3><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg> Monthly Notices</h3>
                </div>
                <div class="chart-card-body">
                    <div class="chart-container" id="monthly-chart"><p class="text-muted">Loading...</p></div>
                </div>
            </div>
            <div class="chart-card">
                <div class="chart-card-header">
                    <h3><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21.21 15.89A10 10 0 1 1 8 2.83"/><path d="M22 12A10 10 0 0 0 12 2v10z"/></svg> Categories</h3>
                </div>
                <div class="chart-card-body">
                    <div class="chart-container" id="category-chart"><p class="text-muted">Loading...</p></div>
                </div>
            </div>
            <div class="chart-card">
                <div class="chart-card-header">
                    <h3><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg> Status Breakdown</h3>
                </div>
                <div class="chart-card-body">
                    <div class="chart-container" id="status-chart"><p class="text-muted">Loading...</p></div>
                </div>
            </div>
        </div>

        <div class="chart-card chart-card-full mt-2">
            <div class="chart-card-header">
                <h3><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg> Most Viewed Notices</h3>
            </div>
            <div class="chart-card-body">
                <div class="chart-container" id="most-viewed-chart"><p class="text-muted">Loading...</p></div>
            </div>
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    function fetchJson(url) { return fetch(url).then(function(r){ return r.json(); }); }
    function renderBarChart(containerId, data, labelKey, valueKey, colorClass) {
        var el = document.getElementById(containerId); if (!el) return;
        if (!data || data.length === 0) { el.innerHTML = '<div class="empty-state"><svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"/><polyline points="13 2 13 9 20 9"/></svg><p>No data available</p></div>'; return; }
        var max = 0; data.forEach(function(d) { if (d[valueKey] > max) max = d[valueKey]; });
        max = max || 1;
        var html = '<div class="bar-chart">';
        data.forEach(function(d, i) {
            var pct = (d[valueKey] / max) * 100;
            var label = d[labelKey] || '—';
            html += '<div class="bar-item" style="animation-delay:' + (i * 60) + 'ms">' +
                    '<span class="bar-label" title="' + label + '">' + label + '</span>' +
                    '<div class="bar-track"><div class="bar-fill ' + (colorClass || '') + '" style="width:' + pct + '%"></div></div>' +
                    '<span class="bar-value">' + d[valueKey] + '</span></div>';
        });
        html += '</div>';
        el.innerHTML = html;
    }
    fetchJson('/api/analytics/summary').then(function(d) {
        document.getElementById('stat-total').textContent = d.totalNotices;
        document.getElementById('stat-views').textContent = d.totalViews;
        document.getElementById('stat-active').textContent = d.activeUsers;
        document.getElementById('stat-categories').textContent = d.categories;
    });
    fetchJson('/api/analytics/monthly').then(function(d) { renderBarChart('monthly-chart', d, 'month', 'total', 'fill-blue'); });
    fetchJson('/api/analytics/categories').then(function(d) { renderBarChart('category-chart', d, 'category', 'count', 'fill-green'); });
    fetchJson('/api/analytics/status-breakdown').then(function(d) { renderBarChart('status-chart', d, 'status', 'count', 'fill-purple'); });
    fetchJson('/api/analytics/most-viewed').then(function(d) { renderBarChart('most-viewed-chart', d, 'title', 'view_count', 'fill-amber'); });
});
</script>
