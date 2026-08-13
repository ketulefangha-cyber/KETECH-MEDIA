<?php
/**
 * Referrals Admin Dashboard
 * Manage and track all referral submissions
 */

@include_once __DIR__ . '/../config.php';

// Check authorization
$provided_key = isset($_GET['admin_key']) ? $_GET['admin_key'] : '';
$authorized = defined('REFERRAL_ADMIN_KEY') && !empty(REFERRAL_ADMIN_KEY) && $provided_key === REFERRAL_ADMIN_KEY;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Referrals Admin Dashboard</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif; background: #f5f5f5; color: #333; }
        .admin-container { max-width: 1200px; margin: 0 auto; padding: 20px; }
        header { background: #fff; padding: 20px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        h1 { font-size: 28px; margin-bottom: 10px; }
        .status-bar { font-size: 14px; color: #666; padding: 10px 0; }
        .status-bar.authorized { color: #27ae60; }
        .status-bar.unauthorized { color: #e74c3c; }

        .auth-form { background: #fff; padding: 30px; border-radius: 8px; margin: 20px 0; box-shadow: 0 2px 8px rgba(0,0,0,0.1); text-align: center; }
        .auth-form input { padding: 10px 15px; border: 1px solid #ddd; border-radius: 4px; width: 300px; max-width: 100%; margin-right: 10px; }
        .auth-form button { padding: 10px 20px; background: #ff6600; color: #fff; border: none; border-radius: 4px; cursor: pointer; font-weight: bold; }
        .auth-form button:hover { background: #e85a00; }
        .auth-form p { margin-top: 15px; color: #666; }

        .stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 20px; }
        .stat-card { background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .stat-card h3 { font-size: 12px; text-transform: uppercase; color: #666; margin-bottom: 8px; }
        .stat-card .number { font-size: 32px; font-weight: bold; color: #ff6600; }

        .filters { background: #fff; padding: 15px; border-radius: 8px; margin-bottom: 20px; display: flex; gap: 10px; flex-wrap: wrap; }
        .filters select { padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; }

        .table-container { background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        table { width: 100%; border-collapse: collapse; }
        th { background: #f9f9f9; padding: 15px; text-align: left; font-weight: 600; border-bottom: 2px solid #eee; font-size: 13px; text-transform: uppercase; color: #666; }
        td { padding: 12px 15px; border-bottom: 1px solid #eee; }
        tr:hover { background: #fafafa; }

        .id { font-size: 11px; color: #999; font-family: monospace; }
        .email-small { font-size: 12px; color: #666; }
        .date-small { font-size: 12px; color: #999; }

        .status { display: inline-block; padding: 4px 8px; border-radius: 3px; font-size: 11px; font-weight: 600; text-transform: uppercase; }
        .status.pending { background: #ffeaa7; color: #d35400; }
        .status.approved { background: #55efc4; color: #00b894; }
        .status.rejected { background: #fab1a0; color: #d63031; }
        .status.completed { background: #81ecec; color: #0984e3; }

        .actions { display: flex; gap: 5px; }
        select { padding: 6px 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 12px; cursor: pointer; }
        .btn { padding: 6px 12px; border: none; border-radius: 4px; cursor: pointer; font-size: 12px; font-weight: 600; }
        .btn-delete { background: #e74c3c; color: #fff; }
        .btn-delete:hover { background: #c0392b; }
        .btn-primary { background: #ff6600; color: #fff; }
        .btn-primary:hover { background: #e85a00; }

        .message-box { padding: 15px; border-radius: 4px; margin-bottom: 15px; }
        .message-box.error { background: #fadbd8; color: #c0392b; border: 1px solid #rsquo#e74c3c; }
        .message-box.success { background: #d5f4e6; color: #27ae60; border: 1px solid #27ae60; }
        .message-box.info { background: #d6eaf8; color: #2874a6; border: 1px solid #2874a6; }

        .loading { text-align: center; padding: 40px; }
        .spinner { border: 3px solid #f3f3f3; border-top: 3px solid #ff6600; border-radius: 50%; width: 40px; height: 40px; animation: spin 1s linear infinite; margin: 0 auto; }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }

        .no-data { text-align: center; padding: 40px; color: #999; }

        @media (max-width: 768px) {
            table { font-size: 12px; }
            th, td { padding: 8px 10px; }
            .actions { flex-direction: column; }
            select { width: 100%; }
        }
    </style>
</head>
<body>
<div class="admin-container">
    <header>
        <h1>Referrals Dashboard</h1>
        <?php if ($authorized): ?>
            <div class="status-bar authorized">✓ You are authenticated as admin</div>
        <?php else: ?>
            <div class="status-bar unauthorized">✗ Not authenticated</div>
        <?php endif; ?>
    </header>

    <?php if (!$authorized): ?>
        <div class="auth-form">
            <h2>Admin Authentication</h2>
            <p>Enter your admin key to access the referrals dashboard.</p>
            <form method="get" style="padding: 20px 0;">
                <input type="password" name="admin_key" placeholder="Enter admin key" required autofocus />
                <button type="submit" class="btn btn-primary">Authenticate</button>
            </form>
            <p>If you don't have an admin key, check your <code>config.php</code> file for <code>REFERRAL_ADMIN_KEY</code>.</p>
        </div>
    <?php else: ?>
        <div id="messageBox"></div>

        <div class="stats" id="statsContainer">
            <div class="stat-card">
                <h3>Total Referrals</h3>
                <div class="number" id="totalCount">0</div>
            </div>
            <div class="stat-card">
                <h3>Pending</h3>
                <div class="number" id="pendingCount">0</div>
            </div>
            <div class="stat-card">
                <h3>Approved</h3>
                <div class="number" id="approvedCount">0</div>
            </div>
            <div class="stat-card">
                <h3>Rejected</h3>
                <div class="number" id="rejectedCount">0</div>
            </div>
        </div>

        <div class="filters">
            <select id="filterStatus" onchange="updateFilter()">
                <option value="">All Statuses</option>
                <option value="pending">Pending</option>
                <option value="approved">Approved</option>
                <option value="rejected">Rejected</option>
                <option value="completed">Completed</option>
            </select>
            <button class="btn btn-primary" onclick="loadReferrals()">Refresh</button>
        </div>

        <div class="table-container">
            <div id="loadingSpinner" class="loading">
                <div class="spinner"></div>
                <p>Loading referrals...</p>
            </div>
            <table id="referralsTable" style="display: none;">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Referrer</th>
                        <th>Referred Contact</th>
                        <th>Message</th>
                        <th>Submitted</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="referralsBody"></tbody>
            </table>
            <div id="noData" class="no-data" style="display: none;">No referrals found</div>
        </div>
    <?php endif; ?>
</div>

<?php if ($authorized): ?>
<script>
const ADMIN_KEY = <?php echo json_encode($provided_key); ?>;
let allReferrals = [];
let displayedReferrals = [];

async function loadReferrals() {
    const loadingSpinner = document.getElementById('loadingSpinner');
    const table = document.getElementById('referralsTable');
    const noData = document.getElementById('noData');

    loadingSpinner.style.display = 'block';
    table.style.display = 'none';
    noData.style.display = 'none';

    try {
        const response = await fetch('/api/referral_admin.php?admin_key=' + encodeURIComponent(ADMIN_KEY));
        const data = await response.json();

        if (!data.success) {
            showMessage(data.message || 'Failed to load referrals', 'error');
            loadingSpinner.style.display = 'none';
            return;
        }

        allReferrals = data.records || [];
        updateStats();
        updateFilter();
        loadingSpinner.style.display = 'none';

        if (allReferrals.length === 0) {
            noData.style.display = 'block';
        } else {
            table.style.display = 'table';
        }
    } catch (error) {
        showMessage('Network error: ' + error.message, 'error');
        loadingSpinner.style.display = 'none';
    }
}

function updateStats() {
    const total = allReferrals.length;
    const pending = allReferrals.filter(r => r.status === 'pending').length;
    const approved = allReferrals.filter(r => r.status === 'approved').length;
    const rejected = allReferrals.filter(r => r.status === 'rejected').length;

    document.getElementById('totalCount').textContent = total;
    document.getElementById('pendingCount').textContent = pending;
    document.getElementById('approvedCount').textContent = approved;
    document.getElementById('rejectedCount').textContent = rejected;
}

function updateFilter() {
    const filterStatus = document.getElementById('filterStatus').value;
    const noData = document.getElementById('noData');
    const table = document.getElementById('referralsTable');

    displayedReferrals = filterStatus 
        ? allReferrals.filter(r => r.status === filterStatus)
        : allReferrals;

    const tbody = document.getElementById('referralsBody');
    tbody.innerHTML = '';

    if (displayedReferrals.length === 0) {
        noData.style.display = 'block';
        table.style.display = 'none';
        return;
    }

    table.style.display = 'table';
    noData.style.display = 'none';

    displayedReferrals.reverse().forEach(r => {
        const tr = document.createElement('tr');
        const createdDate = new Date(r.created_at).toLocaleString();
        const messagePreview = (r.message || '').substring(0, 50) + (r.message && r.message.length > 50 ? '...' : '');

        tr.innerHTML = `
            <td><span class="id">${r.id}</span></td>
            <td>
                ${r.referrer_name || 'N/A'}<br/>
                <span class="email-small">${r.referrer_email || ''}</span>
            </td>
            <td>
                ${r.referee_name || 'N/A'}<br/>
                <span class="email-small">${r.referee_email || ''}</span>
            </td>
            <td>${messagePreview || '—'}</td>
            <td><span class="date-small">${createdDate}</span></td>
            <td>
                <select class="status-select" data-id="${r.id}" data-current="${r.status}">
                    <option value="pending" ${r.status === 'pending' ? 'selected' : ''}>Pending</option>
                    <option value="approved" ${r.status === 'approved' ? 'selected' : ''}>Approved</option>
                    <option value="rejected" ${r.status === 'rejected' ? 'selected' : ''}>Rejected</option>
                    <option value="completed" ${r.status === 'completed' ? 'selected' : ''}>Completed</option>
                </select>
            </td>
            <td class="actions">
                <button class="btn btn-delete" onclick="deleteReferral('${r.id}')">Delete</button>
            </td>
        `;
        tbody.appendChild(tr);
    });

    document.querySelectorAll('.status-select').forEach(select => {
        select.addEventListener('change', (e) => {
            const id = e.target.dataset.id;
            const newStatus = e.target.value;
            updateReferralStatus(id, newStatus);
        });
    });
}

async function updateReferralStatus(id, newStatus) {
    const fd = new FormData();
    fd.append('action', 'update');
    fd.append('id', id);
    fd.append('status', newStatus);
    fd.append('admin_key', ADMIN_KEY);

    try {
        const response = await fetch('/api/referral_admin.php', {
            method: 'POST',
            body: fd
        });
        const data = await response.json();

        if (data.success) {
            showMessage('Referral status updated', 'success');
            loadReferrals();
        } else {
            showMessage(data.message || 'Failed to update status', 'error');
        }
    } catch (error) {
        showMessage('Network error: ' + error.message, 'error');
    }
}

async function deleteReferral(id) {
    if (!confirm('Are you sure you want to delete this referral? This cannot be undone.')) {
        return;
    }

    const fd = new FormData();
    fd.append('action', 'delete');
    fd.append('id', id);
    fd.append('admin_key', ADMIN_KEY);

    try {
        const response = await fetch('/api/referral_admin.php', {
            method: 'POST',
            body: fd
        });
        const data = await response.json();

        if (data.success) {
            showMessage('Referral deleted successfully', 'success');
            loadReferrals();
        } else {
            showMessage(data.message || 'Failed to delete referral', 'error');
        }
    } catch (error) {
        showMessage('Network error: ' + error.message, 'error');
    }
}

function showMessage(message, type = 'info') {
    const messageBox = document.getElementById('messageBox');
    messageBox.innerHTML = `<div class="message-box ${type}">${message}</div>`;
    setTimeout(() => {
        messageBox.innerHTML = '';
    }, 5000);
}

// Load referrals on page load
window.addEventListener('load', loadReferrals);
</script>
<?php endif; ?>
</body>
</html>
