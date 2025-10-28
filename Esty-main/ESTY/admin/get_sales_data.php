<?php
require_once __DIR__ . '/session_bootstrap.php';
require_once "../db.php";

requireAdminLogin();

$month = isset($_GET['month']) ? (int) $_GET['month'] : (int) date('n');
$year = isset($_GET['year']) ? (int) $_GET['year'] : (int) date('Y');
$metric = $_GET['metric'] ?? 'sales'; // sales | count | avg

$month = max(1, min(12, $month));
$year = max(2000, min(2100, $year));

$previousMonth = $month === 1 ? 12 : $month - 1;
$previousYear = $month === 1 ? $year - 1 : $year;

$aggregates = [
    'sales' => 'SUM(total)',
    'count' => 'COUNT(*)',
    'avg'   => 'AVG(total)',
];

$aggregate = $aggregates[$metric] ?? $aggregates['sales'];

$query = "
    SELECT DAY(created_at) AS day, {$aggregate} AS value
    FROM orders
    WHERE status = 'completed' AND MONTH(created_at) = ? AND YEAR(created_at) = ?
    GROUP BY DAY(created_at)
    ORDER BY DAY(created_at)
";

$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $month, $year);
$stmt->execute();
$currentData = $stmt->get_result();
$stmt->close();

$sumQuery = "SELECT {$aggregate} AS total FROM orders WHERE status = 'completed' AND MONTH(created_at) = ? AND YEAR(created_at) = ?";

$sumStmt = $conn->prepare($sumQuery);
$sumStmt->bind_param("ii", $month, $year);
$sumStmt->execute();
$sumResult = $sumStmt->get_result();
$currentTotal = $sumResult ? ($sumResult->fetch_assoc()['total'] ?? 0) : 0;
$sumStmt->close();

$prevStmt = $conn->prepare($sumQuery);
$prevStmt->bind_param("ii", $previousMonth, $previousYear);
$prevStmt->execute();
$prevResult = $prevStmt->get_result();
$previousTotal = $prevResult ? ($prevResult->fetch_assoc()['total'] ?? 0) : 0;
$prevStmt->close();

$days = [];
$values = [];
if ($currentData) {
    while ($row = $currentData->fetch_assoc()) {
        $days[] = (int) $row['day'];
        $values[] = (float) $row['value'];
    }
}

echo json_encode([
    'days' => $days,
    'values' => $values,
    'metric' => $metric,
    'currentMonth' => date('F', mktime(0, 0, 0, $month, 1)),
    'previousMonth' => date('F', mktime(0, 0, 0, $previousMonth, 1)),
    'currentTotal' => round((float) $currentTotal, 2),
    'previousTotal' => round((float) $previousTotal, 2),
]);
?>
