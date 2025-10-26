<?php
require_once __DIR__ . '/session_bootstrap.php';
require_once '../db.php';
require_once __DIR__ . '/admin_helpers.php';

requireAdminLogin();
ensureActivityLogTable($conn);

$tab = $_GET['tab'] ?? 'admins';
if (!in_array($tab, ['admins', 'customers'], true)) {
    $tab = 'admins';
}

$adminSearch = trim($_GET['admin_search'] ?? '');
$customerSearch = trim($_GET['customer_search'] ?? '');

function getAdminAccounts(mysqli $conn, ?string $search = null): array
{
    $sql = "SELECT au.id,
                   au.username,
                   au.created_at,
                   (SELECT action FROM admin_activity_logs WHERE admin_username = au.username ORDER BY created_at DESC LIMIT 1) AS last_action,
                   (SELECT created_at FROM admin_activity_logs WHERE admin_username = au.username ORDER BY created_at DESC LIMIT 1) AS last_action_at,
                   (SELECT MAX(created_at) FROM admin_activity_logs WHERE admin_username = au.username AND action = 'Login') AS last_login,
                   (SELECT COUNT(*) FROM admin_activity_logs WHERE admin_username = au.username) AS activity_count
            FROM admin_users au";

    $rows = [];
    $hasSearch = ($search !== null && $search !== '');

    if ($hasSearch) {
        $sql .= " WHERE au.username LIKE ?";
    }

    $sql .= " ORDER BY au.created_at DESC";

    if ($stmt = $conn->prepare($sql)) {
        if ($hasSearch) {
            $like = '%' . $search . '%';
            $stmt->bind_param('s', $like);
        }

        if ($stmt->execute()) {
            $res = $stmt->get_result();
            if ($res) {
                while ($row = $res->fetch_assoc()) {
                    $rows[] = $row;
                }
            }
        }

        $stmt->close();
    }

    return $rows;
}

$adminAccounts = getAdminAccounts($conn, $adminSearch);
$customerAccounts = ($tab === 'customers') ? fetchCustomersWithMetrics($conn, $customerSearch) : [];

$filteredActionVolume = 0;
foreach ($adminAccounts as $row) {
    $filteredActionVolume += (int) ($row['activity_count'] ?? 0);
}

if (isset($_GET['export'])) {
    $export = $_GET['export'];

    if ($export === 'admins') {
        $exportData = getAdminAccounts($conn, $adminSearch);

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="admin_accounts.csv"');

        $out = fopen('php://output', 'w');
        fputcsv($out, ['ID', 'Username', 'Created At', 'Last Activity', 'Last Activity Date', 'Last Login', 'Activity Count']);

        foreach ($exportData as $row) {
            fputcsv($out, [
                $row['id'],
                $row['username'],
                $row['created_at'],
                $row['last_action'] ?? '',
                $row['last_action_at'] ?? '',
                $row['last_login'] ?? '',
                $row['activity_count'] ?? 0,
            ]);
        }

        fclose($out);
        exit;
    }

    if ($export === 'customers') {
        $exportData = fetchCustomersWithMetrics($conn, $customerSearch);

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="customer_accounts.csv"');

        $out = fopen('php://output', 'w');
        fputcsv($out, ['User ID', 'Username', 'Email', 'Created At', 'Orders', 'Total Spent', 'Last Order']);

        foreach ($exportData as $row) {
            fputcsv($out, [
                $row['id'],
                $row['username'],
                $row['email'],
                $row['created_at'],
                $row['order_count'],
                number_format((float) ($row['total_spent'] ?? 0), 2, '.', ''),
                $row['last_order_at'] ?? '',
            ]);
        }

        fclose($out);
        exit;
    }
}

$adminSummary = [
    'total' => 0,
    'recent' => 0,
    'active' => 0,
    'latest_username' => null,
    'latest_created_at' => null,
];

if ($res = $conn->query("SELECT COUNT(*) AS total_admins, SUM(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 ELSE 0 END) AS new_admins FROM admin_users")) {
    $row = $res->fetch_assoc();
    $adminSummary['total'] = (int) ($row['total_admins'] ?? 0);
    $adminSummary['recent'] = (int) ($row['new_admins'] ?? 0);
}

if ($res = $conn->query("SELECT COUNT(DISTINCT admin_username) AS active_admins FROM admin_activity_logs WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)")) {
    $row = $res->fetch_assoc();
    $adminSummary['active'] = (int) ($row['active_admins'] ?? 0);
}

if ($res = $conn->query("SELECT username, created_at FROM admin_users ORDER BY created_at DESC LIMIT 1")) {
    $row = $res->fetch_assoc();
    if ($row) {
        $adminSummary['latest_username'] = $row['username'];
        $adminSummary['latest_created_at'] = $row['created_at'];
    }
}

$customerSummary = [
    'total' => 0,
    'recent' => 0,
    'avg_orders' => 0,
    'top_customer' => null,
    'top_customer_total' => 0.0,
];

if ($res = $conn->query("SELECT COUNT(*) AS total_customers, SUM(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 ELSE 0 END) AS new_customers FROM users")) {
    $row = $res->fetch_assoc();
    $customerSummary['total'] = (int) ($row['total_customers'] ?? 0);
    $customerSummary['recent'] = (int) ($row['new_customers'] ?? 0);
}

if ($res = $conn->query("SELECT AVG(order_count) AS avg_orders FROM (SELECT COUNT(*) AS order_count FROM orders GROUP BY email) oc")) {
    $row = $res->fetch_assoc();
    if ($row && $row['avg_orders'] !== null) {
        $customerSummary['avg_orders'] = (float) $row['avg_orders'];
    }
}

if ($res = $conn->query("SELECT email, SUM(total) AS total_spent FROM orders WHERE status = 'completed' GROUP BY email ORDER BY total_spent DESC LIMIT 1")) {
    $row = $res->fetch_assoc();
    if ($row) {
        $customerSummary['top_customer'] = $row['email'];
        $customerSummary['top_customer_total'] = (float) ($row['total_spent'] ?? 0);
    }
}

include 'sidebar.php';
?>
<!DOCTYPE html>
<html lang='en'>
<head>
  <meta charset='UTF-8'>
  <title>Account Reports - Esty Admin</title>
  <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css' rel='stylesheet'>
  <link rel='stylesheet' href='admin-style.css'>
  <link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css'>
  <style>
    .metric-card {
      border-radius: 18px;
      padding: 1.5rem;
      background: linear-gradient(160deg, rgba(255, 182, 193, 0.28) 0%, rgba(255, 230, 236, 0.55) 100%);
      box-shadow: 0 6px 18px rgba(231, 84, 128, 0.12);
      border: 1px solid rgba(231, 84, 128, 0.25);
      color: #4B3F2F;
    }
    .metric-card .metric-title {
      font-size: 0.85rem;
      letter-spacing: 0.05em;
      text-transform: uppercase;
      color: rgba(75, 63, 47, 0.65);
    }
    .metric-card .metric-value {
      font-size: 2rem;
      font-weight: 700;
    }
    .metric-card .metric-subtext {
      font-size: 0.95rem;
      color: rgba(75, 63, 47, 0.75);
    }
    .nav-pills .nav-link {
      border-radius: 999px;
      font-weight: 600;
    }
    .nav-pills .nav-link.active {
      background-color: #e75480;
    }
    .table thead th {
      text-transform: uppercase;
      font-size: 0.75rem;
      letter-spacing: 0.08em;
      color: rgba(75, 63, 47, 0.7);
    }
  </style>
</head>
<body>
<div class='main-content'>
  <div class='card mb-4'>
    <div class='d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 p-3 pb-0'>
      <div>
        <h2 class='fw-bold mb-1'>Account Intelligence</h2>
        <p class='muted mb-0'>Monitor admin and customer accounts, activity, and engagement.</p>
      </div>
      <div class='text-md-end'>
        <span class='badge bg-light text-dark border'>Updated <?= date('M d, Y H:i') ?></span>
      </div>
    </div>
  </div>

  <div class='row g-3 mb-4'>
    <div class='col-md-3'>
      <div class='metric-card h-100'>
        <div class='metric-title'><i class='bi bi-shield-lock me-2'></i>Total Admins</div>
        <div class='metric-value'><?= number_format($adminSummary['total']) ?></div>
        <div class='metric-subtext'><?= number_format($adminSummary['recent']) ?> added in the last 30 days</div>
      </div>
    </div>
    <div class='col-md-3'>
      <div class='metric-card h-100'>
        <div class='metric-title'><i class='bi bi-lightning-charge me-2'></i>Active Admins</div>
        <div class='metric-value'><?= number_format($adminSummary['active']) ?></div>
        <div class='metric-subtext'>Active within the last 30 days</div>
      </div>
    </div>
    <div class='col-md-3'>
      <div class='metric-card h-100'>
        <div class='metric-title'><i class='bi bi-person-plus me-2'></i>Latest Admin</div>
        <div class='metric-value'>
          <?= $adminSummary['latest_username'] ? htmlspecialchars($adminSummary['latest_username']) : '—' ?>
        </div>
        <div class='metric-subtext'>
          <?= $adminSummary['latest_created_at'] ? date('M d, Y', strtotime($adminSummary['latest_created_at'])) : 'No admins yet' ?>
        </div>
      </div>
    </div>
    <div class='col-md-3'>
      <div class='metric-card h-100'>
        <div class='metric-title'><i class='bi bi-people me-2'></i>Total Customers</div>
        <div class='metric-value'><?= number_format($customerSummary['total']) ?></div>
        <div class='metric-subtext'><?= number_format($customerSummary['recent']) ?> joined in the last 30 days</div>
      </div>
    </div>
  </div>

  <div class='row g-3 mb-4'>
    <div class='col-md-4'>
      <div class='metric-card h-100'>
        <div class='metric-title'><i class='bi bi-diagram-3 me-2'></i>Avg. Orders / Customer</div>
        <div class='metric-value'><?= number_format($customerSummary['avg_orders'], 2) ?></div>
        <div class='metric-subtext'>Across all customers with at least one order</div>
      </div>
    </div>
    <div class='col-md-4'>
      <div class='metric-card h-100'>
        <div class='metric-title'><i class='bi bi-trophy me-2'></i>Top Customer</div>
        <div class='metric-value' style='font-size: 1.4rem;'>
          <?= $customerSummary['top_customer'] ? htmlspecialchars($customerSummary['top_customer']) : '—' ?>
        </div>
        <div class='metric-subtext'>
          <?= $customerSummary['top_customer'] ? '₱' . number_format($customerSummary['top_customer_total'], 2) . ' total spent' : 'No completed orders yet' ?>
        </div>
      </div>
    </div>
    <div class='col-md-4'>
      <div class='metric-card h-100'>
        <div class='metric-title'><i class='bi bi-graph-up-arrow me-2'></i>Action Volume</div>
        <div class='metric-value'><?= number_format($filteredActionVolume) ?></div>
        <div class='metric-subtext'>Actions recorded in admin logs (filtered set)</div>
      </div>
    </div>
  </div>

  <div class='card'>
    <div class='card-body'>
      <ul class='nav nav-pills nav-fill mb-4'>
        <li class='nav-item'>
          <a class='nav-link <?= $tab === 'admins' ? 'active' : '' ?>' href='account_report.php?tab=admins'>Admin Accounts</a>
        </li>
        <li class='nav-item'>
          <a class='nav-link <?= $tab === 'customers' ? 'active' : '' ?>' href='account_report.php?tab=customers'>Customer Accounts</a>
        </li>
      </ul>

      <?php if ($tab === 'admins'): ?>
        <form class='row row-cols-1 row-cols-md-auto g-3 align-items-end mb-4' method='get'>
          <input type='hidden' name='tab' value='admins'>
          <div class='col'>
            <label class='form-label mb-1 fw-semibold text-secondary'>Search Username</label>
            <input type='text' name='admin_search' class='form-control shadow-sm' placeholder='e.g. manager' value='<?= htmlspecialchars($adminSearch) ?>'>
          </div>
          <div class='col'>
            <button type='submit' class='btn btn-primary px-4'><i class='bi bi-funnel'></i> Apply</button>
            <a href='account_report.php?tab=admins' class='btn btn-outline-secondary ms-1'>Reset</a>
          </div>
          <div class='col ms-md-auto'>
            <a class='btn btn-outline-secondary' href='account_report.php?tab=admins&export=admins&admin_search=<?= urlencode($adminSearch) ?>'>
              <i class='bi bi-download'></i> Export CSV
            </a>
          </div>
        </form>

        <div class='table-responsive'>
          <table class='table table-hover align-middle mb-0'>
            <thead>
              <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Created</th>
                <th>Last Activity</th>
                <th>Last Login</th>
                <th class='text-center'>Activity Count</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($adminAccounts)): ?>
                <tr><td colspan='6' class='text-center text-muted py-4'>No admin accounts match your filters.</td></tr>
              <?php else: foreach ($adminAccounts as $admin): ?>
                <tr>
                  <td><?= (int) $admin['id'] ?></td>
                  <td>
                    <?= htmlspecialchars($admin['username']) ?>
                    <?php if (!empty($_SESSION['admin']) && $_SESSION['admin'] === $admin['username']): ?>
                      <span class='badge bg-secondary ms-1'>You</span>
                    <?php endif; ?>
                  </td>
                  <td><?= $admin['created_at'] ? date('M d, Y', strtotime($admin['created_at'])) : '—' ?></td>
                  <td>
                    <?php if (!empty($admin['last_action'])): ?>
                      <div class='fw-semibold'><?= htmlspecialchars($admin['last_action']) ?></div>
                      <?php if (!empty($admin['last_action_at'])): ?>
                        <small class='text-muted'><?= date('M d, Y H:i', strtotime($admin['last_action_at'])) ?></small>
                      <?php endif; ?>
                    <?php else: ?>
                      <span class='text-muted'>No activity recorded</span>
                    <?php endif; ?>
                  </td>
                  <td>
                    <?php if (!empty($admin['last_login'])): ?>
                      <?= date('M d, Y H:i', strtotime($admin['last_login'])) ?>
                    <?php else: ?>
                      <span class='text-muted'>No login recorded</span>
                    <?php endif; ?>
                  </td>
                  <td class='text-center fw-semibold'><?= number_format((int) ($admin['activity_count'] ?? 0)) ?></td>
                </tr>
              <?php endforeach; endif; ?>
            </tbody>
          </table>
        </div>
      <?php else: ?>
        <?php if ($tab === 'customers' && empty($customerAccounts) && $customerSearch !== ''): ?>
          <div class='alert alert-info'>No customers matched "<?= htmlspecialchars($customerSearch) ?>".</div>
        <?php endif; ?>
        <form class='row row-cols-1 row-cols-md-auto g-3 align-items-end mb-4' method='get'>
          <input type='hidden' name='tab' value='customers'>
          <div class='col'>
            <label class='form-label mb-1 fw-semibold text-secondary'>Search Name or Email</label>
            <input type='text' name='customer_search' class='form-control shadow-sm' placeholder='e.g. jane@esty.com' value='<?= htmlspecialchars($customerSearch) ?>'>
          </div>
          <div class='col'>
            <button type='submit' class='btn btn-primary px-4'><i class='bi bi-funnel'></i> Apply</button>
            <a href='account_report.php?tab=customers' class='btn btn-outline-secondary ms-1'>Reset</a>
          </div>
          <div class='col ms-md-auto'>
            <a class='btn btn-outline-secondary' href='account_report.php?tab=customers&export=customers&customer_search=<?= urlencode($customerSearch) ?>'>
              <i class='bi bi-download'></i> Export CSV
            </a>
          </div>
        </form>

        <div class='table-responsive'>
          <table class='table table-hover align-middle mb-0'>
            <thead>
              <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Email</th>
                <th>Joined</th>
                <th class='text-center'>Orders</th>
                <th>Total Spent</th>
                <th>Last Order</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($customerAccounts)): ?>
                <tr><td colspan='7' class='text-center text-muted py-4'>No customer accounts found.</td></tr>
              <?php else: foreach ($customerAccounts as $customer): ?>
                <tr>
                  <td><?= (int) $customer['id'] ?></td>
                  <td><?= htmlspecialchars($customer['username']) ?></td>
                  <td><?= htmlspecialchars($customer['email']) ?></td>
                  <td><?= $customer['created_at'] ? date('M d, Y', strtotime($customer['created_at'])) : '—' ?></td>
                  <td class='text-center fw-semibold'><?= number_format((int) ($customer['order_count'] ?? 0)) ?></td>
                  <td>₱<?= number_format((float) ($customer['total_spent'] ?? 0), 2) ?></td>
                  <td>
                    <?php if (!empty($customer['last_order_at'])): ?>
                      <?= date('M d, Y H:i', strtotime($customer['last_order_at'])) ?>
                    <?php else: ?>
                      <span class='text-muted'>No orders</span>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; endif; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>
</body>
</html>
