<?php
require_once __DIR__ . '/session_bootstrap.php';
require_once __DIR__ . '/../db.php';

requireAdminLogin();

// Add/Edit Brand
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_brand') {
    $id = intval($_POST['id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if (strlen($name) < 2) {
        $_SESSION['message'] = 'Brand name must be at least 2 characters';
        $_SESSION['msg_type'] = 'danger';
    } else {
        if ($id > 0) {
            $stmt = $conn->prepare("UPDATE brands SET name = ?, description = ? WHERE id = ?");
            $stmt->bind_param("ssi", $name, $description, $id);
        } else {
            $stmt = $conn->prepare("INSERT INTO brands (name, description) VALUES (?, ?)");
            $stmt->bind_param("ss", $name, $description);
        }
        
        if ($stmt->execute()) {
            $_SESSION['message'] = 'Brand saved successfully';
            $_SESSION['msg_type'] = 'success';
        } else {
            $_SESSION['message'] = $stmt->error;
            $_SESSION['msg_type'] = 'danger';
        }
        $stmt->close();
    }
    header('Location: manage_brands.php');
    exit;
}

// Delete Brand
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_brand') {
    $id = intval($_POST['id'] ?? 0);
    $stmt = $conn->prepare("DELETE FROM brands WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $_SESSION['message'] = 'Brand deleted successfully';
        $_SESSION['msg_type'] = 'success';
    }
    $stmt->close();
    header('Location: manage_brands.php');
    exit;
}

// Fetch brands
$brands = [];
$result = $conn->query("SELECT * FROM brands ORDER BY name");
while ($row = $result->fetch_assoc()) {
    $brands[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Manage Brands - Admin</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
        <link rel="stylesheet" href="admin-style.css">
</head>
<body>
<?php include 'sidebar.php'; ?>

<div class="main-content">
    <div class="card mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2 class="fw-bold mb-0">Brands</h2>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#brandModal">
                    <i class="bi bi-plus"></i> Add Brand
                </button>
            </div>

            <?php if (!empty($_SESSION['message'])): ?>
                <div class="alert alert-<?= $_SESSION['msg_type']; ?>">
                    <?= $_SESSION['message']; ?>
                </div>
                <?php unset($_SESSION['message'], $_SESSION['msg_type']); ?>
            <?php endif; ?>

            <div class="table-responsive">
                <table class="table table-striped align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($brands as $brand): ?>
                            <tr>
                                <td><?= htmlspecialchars($brand['name']); ?></td>
                                <td><?= htmlspecialchars(substr($brand['description'] ?? '', 0, 60)); ?></td>
                                <td>
                                    <button class="btn btn-sm btn-warning" onclick="editBrand(<?= htmlspecialchars(json_encode($brand)); ?>)">
                                        <i class="bi bi-pencil"></i> Edit
                                    </button>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="action" value="delete_brand">
                                        <input type="hidden" name="id" value="<?= $brand['id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this brand?');">
                                            <i class="bi bi-trash"></i> Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Brand Modal -->
<div class="modal fade" id="brandModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="brandTitle">Add Brand</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="save_brand">
                <input type="hidden" name="id" id="brandId" value="0">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Brand Name</label>
                        <input type="text" class="form-control" id="brandName" name="name" required minlength="2">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" id="brandDesc" name="description" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function editBrand(brand) {
    document.getElementById('brandId').value = brand.id;
    document.getElementById('brandName').value = brand.name;
    document.getElementById('brandDesc').value = brand.description || '';
    document.getElementById('brandTitle').textContent = 'Edit Brand';
    new bootstrap.Modal(document.getElementById('brandModal')).show();
}
</script>
</body>
</html>
