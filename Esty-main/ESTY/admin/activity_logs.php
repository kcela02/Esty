<?php
require_once __DIR__ . '/session_bootstrap.php';
require_once '../db.php';
require_once __DIR__ . '/admin_helpers.php';

requireAdminLogin();

// Fetch activity logs (example: login/logout, order status changes, etc.)
$logs = [];

// Filtering
$admin_filter = $_GET['admin'] ?? '';
$action_filter = $_GET['action'] ?? '';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';

$where = [];
$params = [];
if ($admin_filter !== '') {
  $where[] = 'l.admin_username = ?';
  $params[] = $admin_filter;
}
if ($action_filter !== '') {
  $where[] = 'l.action LIKE ?';
  $params[] = "%$action_filter%";
}
if ($date_from !== '') {
  $where[] = 'l.created_at >= ?';
  $params[] = $date_from . ' 00:00:00';
}
if ($date_to !== '') {
  $where[] = 'l.created_at <= ?';
  $params[] = $date_to . ' 23:59:59';
}
$sql = "SELECT l.id, l.admin_username, l.action, l.details, l.ip_address, l.created_at FROM admin_activity_logs l";
if ($where) $sql .= " WHERE " . implode(' AND ', $where);
$sql .= " ORDER BY l.created_at DESC LIMIT 100";
$stmt = $conn->prepare($sql);
if ($params) $stmt->bind_param(str_repeat('s', count($params)), ...$params);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) $logs[] = $row;

// Fetch admins for filter dropdown
$admins_list = [];
$ares = $conn->query("SELECT username FROM admin_users ORDER BY username");
while ($arow = $ares->fetch_assoc()) $admins_list[] = $arow;

// CSV export
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
  header('Content-Type: text/csv');
  header('Content-Disposition: attachment; filename="activity_logs.csv"');
  $out = fopen('php://output', 'w');
  fputcsv($out, ['ID', 'Admin', 'Action', 'Details', 'IP', 'Date/Time']);
  foreach ($logs as $l) {
    fputcsv($out, [
      $l['id'],
      $l['admin_username'] ?? 'Unknown',
      $l['action'],
      $l['details'] ?? '',
      $l['ip_address'] ?? '',
      $l['created_at']
    ]);
  }
  fclose($out);
  exit;
}

include 'sidebar.php';

?>
<!DOCTYPE html>
<html lang='en'>
<head>
  <meta charset='UTF-8'>
  <title>Activity Logs - Esty Admin</title>
  <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css' rel='stylesheet'>
  <link rel='stylesheet' href='admin-style.css'>
</head>
<body>
<div class='main-content'>
  <div class='card mb-4'>
    <div class='d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 p-3 pb-0'>
      <div>
        <h2 class='fw-bold mb-1'>Activity Logs</h2>
        <p class='muted mb-0'>Recent admin activities (last 100 entries). Use filters to refine results.</p>
      </div>
    </div>
  <form class='row row-cols-1 row-cols-md-auto g-3 align-items-end px-3 pb-3' method='get' style='background: #fff6fa; border-radius: 1rem; box-shadow: 0 2px 8px rgba(231,84,128,0.07); margin-top: 1rem;'>
      <div class='col'>
        <label class='form-label mb-1 fw-semibold text-secondary'>Admin</label>
        <select name='admin' class='form-select shadow-sm'>
          <option value=''>All</option>
          <?php foreach ($admins_list as $a): ?>
            <option value='<?= htmlspecialchars($a['username']) ?>' <?= ($admin_filter == $a['username']) ? 'selected' : '' ?>><?= htmlspecialchars($a['username']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class='col'>
        <label class='form-label mb-1 fw-semibold text-secondary'>Action</label>
        <input type='text' name='action' class='form-control shadow-sm' value='<?= htmlspecialchars($action_filter) ?>' placeholder='Action type'>
      </div>
      <div class='col'>
        <label class='form-label mb-1 fw-semibold text-secondary'>From</label>
        <input type='date' name='date_from' class='form-control shadow-sm' value='<?= htmlspecialchars($date_from) ?>'>
      </div>
      <div class='col'>
        <label class='form-label mb-1 fw-semibold text-secondary'>To</label>
        <input type='date' name='date_to' class='form-control shadow-sm' value='<?= htmlspecialchars($date_to) ?>'>
      </div>
      <div class='col'>
        <button type='submit' class='btn btn-primary px-4'><i class='bi bi-funnel'></i> Filter</button>
        <a href='activity_logs.php' class='btn btn-outline-secondary ms-1'>Reset</a>
      </div>
      <div class='col ms-md-auto'>
        <a class='btn btn-outline-secondary' href='activity_logs.php?admin=<?= urlencode($admin_filter) ?>&action=<?= urlencode($action_filter) ?>&date_from=<?= urlencode($date_from) ?>&date_to=<?= urlencode($date_to) ?>&export=csv'>Export CSV</a>
      </div>
    </form>
  </div>
  <div class='card'>
    <div class='card-body'>
      <h5 class='mb-3'><i class='bi bi-clock-history me-2'></i>Recent Activities</h5>
      <div class='table-responsive'>
        <table class='table table-striped align-middle mb-0'>
          <thead>
            <tr>
              <th>ID</th>
              <th>Admin</th>
              <th>Action</th>
              <th>Details</th>
              <th>IP</th>
              <th>Date/Time</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($logs)): ?>
              <tr><td colspan='6' class='text-center text-muted py-4'>No activity logs found.</td></tr>
            <?php else: foreach ($logs as $log): ?>
              <tr>
                <td><?= (int)$log['id'] ?></td>
                <td><?= htmlspecialchars($log['admin_username'] ?? 'Unknown') ?></td>
                <td><?= htmlspecialchars($log['action']) ?></td>
                <td><?= htmlspecialchars($log['details'] ?? '') ?></td>
                <td><?= htmlspecialchars($log['ip_address'] ?? '') ?></td>
                <td><?= date('M d, Y H:i', strtotime($log['created_at'])) ?></td>
              </tr>
            <?php endforeach; endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
</body>
</html>
