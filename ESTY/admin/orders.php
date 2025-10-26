<?php
require_once __DIR__ . '/session_bootstrap.php';
require_once "../db.php";

requireAdminLogin();

$allowedStatuses = ['pending', 'processing', 'completed', 'cancelled'];
$statusFilter = isset($_GET['status']) ? strtolower(trim($_GET['status'])) : '';
$searchQuery = isset($_GET['q']) ? trim($_GET['q']) : '';
// Optional date filters applied via quick ranges
$dateFrom = isset($_GET['date_from']) ? trim($_GET['date_from']) : '';
$dateTo = isset($_GET['date_to']) ? trim($_GET['date_to']) : '';
if ($dateFrom && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateFrom)) $dateFrom = '';
if ($dateTo && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateTo)) $dateTo = '';
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$pageSize = 10;
$offset = ($page - 1) * $pageSize;
if ($statusFilter && !in_array($statusFilter, $allowedStatuses, true)) {
    $statusFilter = '';
}

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $order_id = intval($_POST['order_id'] ?? 0);
    $status = strtolower(trim($_POST['status'] ?? ''));
    $redirectQuery = $_POST['redirect_query'] ?? '';
    // sanitize redirect query to avoid header injection
    $redirectQuery = preg_replace('/[\r\n]/', '', $redirectQuery);
    if (in_array($status, $allowedStatuses, true) && $order_id > 0) {
        $update_sql = "UPDATE orders SET status = ? WHERE id = ?";
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param("si", $status, $order_id);
        if ($stmt->execute()) {
            $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Order status updated successfully.'];
        } else {
            $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Failed to update order status.'];
        }
        $stmt->close();
    }
    // Redirect back preserving filters/search/pagination
    $redirect = "orders.php";
    if (!empty($redirectQuery)) {
        $redirect .= "?" . $redirectQuery;
    }
    header("Location: $redirect");
    exit;
}

// Build filtered query with pagination
$where = [];
$params = [];
$types = '';
if ($statusFilter) {
    $where[] = 'status = ?';
    $params[] = $statusFilter;
    $types .= 's';
} else {
    // Default view shows pending and processing
    $where[] = "status IN ('pending','processing')";
}
if ($searchQuery !== '') { $where[] = '(customer_name LIKE ? OR email LIKE ?)'; $like = "%$searchQuery%"; $params[] = $like; $params[] = $like; $types .= 'ss'; }
if ($dateFrom !== '') { $where[] = 'created_at >= ?'; $params[] = $dateFrom . ' 00:00:00'; $types .= 's'; }
if ($dateTo !== '') { $where[] = 'created_at <= ?'; $params[] = $dateTo . ' 23:59:59'; $types .= 's'; }
$whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

// Total count for pagination
$countSql = "SELECT COUNT(*) AS cnt FROM orders $whereSql";
$countStmt = $conn->prepare($countSql);
if ($types) { $countStmt->bind_param($types, ...$params); }
$countStmt->execute();
$countRes = $countStmt->get_result();
$totalRows = $countRes ? (int)($countRes->fetch_assoc()['cnt'] ?? 0) : 0;
$countStmt->close();
$totalPages = max(1, (int)ceil($totalRows / $pageSize));
if ($page > $totalPages) { $page = $totalPages; $offset = ($page - 1) * $pageSize; }

// Fetch page
$fetchSql = "SELECT * FROM orders $whereSql ORDER BY created_at DESC LIMIT ? OFFSET ?";
$fetchStmt = $conn->prepare($fetchSql);
if ($types) {
    $bindTypes = $types . 'ii';
    $bindParams = array_merge($params, [$pageSize, $offset]);
    $fetchStmt->bind_param($bindTypes, ...$bindParams);
} else {
    $fetchStmt->bind_param('ii', $pageSize, $offset);
}
$fetchStmt->execute();
$orders = $fetchStmt->get_result();
$fetchStmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Orders - Esty Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="admin-style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>

<?php include 'sidebar.php'; ?>

<div class="main-content">
    <div class="card mb-4">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 p-3 pb-0">
            <div>
                <h2 class="fw-bold mb-1">Orders</h2>
                <p class="muted mb-0">Manage and review customer orders.</p>
            </div>
            <form method="GET" class="d-flex align-items-center gap-2 flex-wrap" style="min-width:280px;">
                <select name="status" class="form-select" style="max-width:180px;">
                    <option value="">All Statuses (default shows Pending+Processing)</option>
                    <?php foreach ($allowedStatuses as $s): ?>
                        <option value="<?= $s ?>" <?= $statusFilter === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                    <?php endforeach; ?>
                </select>
                <input type="text" class="form-control" name="q" placeholder="Search name or email" value="<?= htmlspecialchars($searchQuery) ?>" style="max-width:240px;">
                <input type="hidden" name="date_from" value="<?= htmlspecialchars($dateFrom) ?>">
                <input type="hidden" name="date_to" value="<?= htmlspecialchars($dateTo) ?>">
                <button type="submit" class="btn btn-add">Apply</button>
                <?php if ($statusFilter || $searchQuery || $dateFrom || $dateTo): ?>
                <a class="btn btn-outline-secondary" href="orders.php">Reset</a>
                <?php endif; ?>
            </form>
        </div>
        <div class="px-3 pb-3">
            <!-- Quick status chips -->
            <div class="d-flex gap-2 flex-wrap">
                <?php
                    // Use globally consistent badge colors
                    $chips = ['pending' => 'pending', 'processing' => 'processing', 'completed' => 'completed', 'cancelled' => 'cancelled'];
                    foreach ($chips as $label => $color):
                        $active = ($statusFilter === $label);
                        $qs = http_build_query(array_filter(['status' => $label, 'q' => $searchQuery, 'date_from' => $dateFrom, 'date_to' => $dateTo]));
                ?>
                    <a href="orders.php?<?= $qs ?>" class="badge bg-<?= $color ?> text-decoration-none py-2 px-3" style="border-radius:24px;<?= $active ? 'box-shadow:0 0 0 2px #e91e63 inset;' : '' ?>"><?= ucfirst($label) ?></a>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="px-3 pb-3">
            <!-- Quick date ranges -->
            <div class="d-flex gap-2 flex-wrap align-items-center">
                <span class="text-muted small">Quick ranges:</span>
                <?php
                  $base = [];
                  if ($statusFilter) $base['status'] = $statusFilter;
                  if ($searchQuery !== '') $base['q'] = $searchQuery;
                  $today = date('Y-m-d');
                  $from7 = date('Y-m-d', strtotime('-6 days'));
                  $from30 = date('Y-m-d', strtotime('-29 days'));
                  $firstMonth = date('Y-m-01');
                  $qrToday = http_build_query($base + ['date_from' => $today, 'date_to' => $today]);
                  $qr7 = http_build_query($base + ['date_from' => $from7, 'date_to' => $today]);
                  $qr30 = http_build_query($base + ['date_from' => $from30, 'date_to' => $today]);
                  $qrMonth = http_build_query($base + ['date_from' => $firstMonth, 'date_to' => $today]);
                ?>
                <a class="badge bg-pink text-decoration-none py-2 px-3" style="border-radius:24px;" href="orders.php?<?= $qrToday ?>">Today</a>
                <a class="badge bg-pink text-decoration-none py-2 px-3" style="border-radius:24px;" href="orders.php?<?= $qr7 ?>">Last 7 days</a>
                <a class="badge bg-pink text-decoration-none py-2 px-3" style="border-radius:24px;" href="orders.php?<?= $qr30 ?>">Last 30 days</a>
                <a class="badge bg-pink text-decoration-none py-2 px-3" style="border-radius:24px;" href="orders.php?<?= $qrMonth ?>">This month</a>
            </div>
        </div>
    </div>

    <div class="card">
        <?php if (!empty($_SESSION['flash'])): $f=$_SESSION['flash']; unset($_SESSION['flash']); ?>
          <div class="alert alert-<?= htmlspecialchars($f['type']) ?> mx-3 mt-3"><?= htmlspecialchars($f['msg']) ?></div>
        <?php endif; ?>
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Customer</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th>Total</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($orders && $orders->num_rows > 0): ?>
                        <?php while ($row = $orders->fetch_assoc()): ?>
                            <tr>
                                <td><?= (int)$row['id'] ?></td>
                                <td><?= htmlspecialchars($row['customer_name'] ?? '') ?></td>
                                <td><?= htmlspecialchars($row['email'] ?? '') ?></td>
                                <td>
                                    <?php
                                        $statusClass = 'bg-' . htmlspecialchars(strtolower($row['status']));
                                    ?>
                                    <span class="badge <?= $statusClass ?> px-3 py-2">
                                        <?= ucfirst(htmlspecialchars($row['status'])) ?>
                                    </span>
                                </td>
                                <td>₱<?= number_format((float)($row['total'] ?? 0), 2) ?></td>
                                <td><?= date("M j, Y", strtotime($row['created_at'])) ?></td>
                                <td class="d-flex gap-2 flex-wrap">
                                    <button type="button" class="btn btn-outline-primary btn-view px-3 py-1" onclick="viewOrder(<?= (int)$row['id'] ?>)">
                                        <i class="bi bi-eye"></i> View
                                    </button>
                                    <form method="POST" class="d-flex gap-1 align-items-center" onsubmit="return confirm('Update order status?')">
                                        <input type="hidden" name="order_id" value="<?= (int)$row['id'] ?>">
                                        <input type="hidden" name="redirect_query" value="<?= htmlspecialchars($_SERVER['QUERY_STRING'] ?? '') ?>">
                                        <select name="status" class="form-select form-select-sm" style="width:auto; min-width:110px;">
                                            <?php foreach ($allowedStatuses as $s): ?>
                                                <option value="<?= $s ?>" <?= strtolower($row['status']) === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <button type="submit" name="update_status" class="btn btn-add btn-sm px-3 py-1">Update</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="7" class="text-center text-muted py-4">No orders found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
        <nav class="px-3 py-3">
          <ul class="pagination mb-0">
            <?php
              $baseParams = [];
              if ($statusFilter) $baseParams['status'] = $statusFilter;
              if ($searchQuery !== '') $baseParams['q'] = $searchQuery;
              if ($dateFrom) $baseParams['date_from'] = $dateFrom;
              if ($dateTo) $baseParams['date_to'] = $dateTo;
              $prev = max(1, $page - 1);
              $next = min($totalPages, $page + 1);
            ?>
            <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
              <a class="page-link" href="orders.php?<?= http_build_query($baseParams + ['page' => $prev]) ?>">Prev</a>
            </li>
            <?php for ($p = 1; $p <= $totalPages; $p++): ?>
            <li class="page-item <?= $p === $page ? 'active' : '' ?>">
              <a class="page-link" href="orders.php?<?= http_build_query($baseParams + ['page' => $p]) ?>"><?= $p ?></a>
            </li>
            <?php endfor; ?>
            <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
              <a class="page-link" href="orders.php?<?= http_build_query($baseParams + ['page' => $next]) ?>">Next</a>
            </li>
          </ul>
        </nav>
        <?php endif; ?>
    </div>
</div>

<!-- Modal for viewing order details -->
<div class="modal fade" id="orderModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content p-3" style="border-radius: 16px;">
      <div class="modal-header border-0">
        <h5 class="modal-title fw-bold">Order Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="orderDetailsContent">
        <div class="text-center text-muted">Loading...</div>
      </div>
    </div>
  </div>
</div>

<script>
function viewOrder(orderId) {
  fetch(`get_order_details.php?id=${orderId}`)
    .then(res => res.text())
    .then(html => {
      document.getElementById('orderDetailsContent').innerHTML = html;
      new bootstrap.Modal(document.getElementById('orderModal')).show();
    })
    .catch(err => {
      document.getElementById('orderDetailsContent').innerHTML =
        "<div class='text-danger text-center'>Error loading order details.</div>";
    });
}
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
