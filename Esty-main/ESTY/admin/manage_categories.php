<?php
require_once __DIR__ . '/session_bootstrap.php';
require_once __DIR__ . '/../db.php';

requireAdminLogin();

// Add/Edit Category
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_category') {
    $id = intval($_POST['id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if (strlen($name) < 2) {
        $_SESSION['message'] = 'Category name must be at least 2 characters';
        $_SESSION['msg_type'] = 'danger';
    } else {
        if ($id > 0) {
            $stmt = $conn->prepare("UPDATE categories SET name = ?, description = ? WHERE id = ?");
            $stmt->bind_param("ssi", $name, $description, $id);
        } else {
            $stmt = $conn->prepare("INSERT INTO categories (name, description) VALUES (?, ?)");
            $stmt->bind_param("ss", $name, $description);
        }
        
        if ($stmt->execute()) {
            $_SESSION['message'] = 'Category saved successfully';
            $_SESSION['msg_type'] = 'success';
        } else {
            $_SESSION['message'] = $stmt->error;
            $_SESSION['msg_type'] = 'danger';
        }
        $stmt->close();
    }
    header('Location: manage_categories.php');
    exit;
}

// Delete Category
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_category') {
    $id = intval($_POST['id'] ?? 0);
    $stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $_SESSION['message'] = 'Category deleted successfully';
        $_SESSION['msg_type'] = 'success';
    }
    $stmt->close();
    header('Location: manage_categories.php');
    exit;
}

// Fetch categories
$categories = [];
$result = $conn->query("SELECT * FROM categories ORDER BY name");
while ($row = $result->fetch_assoc()) {
    $categories[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Manage Categories - Admin</title>
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
                <h2 class="fw-bold mb-0">Categories</h2>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#categoryModal">
                    <i class="bi bi-plus"></i> Add Category
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
                        <?php foreach ($categories as $cat): ?>
                            <tr>
                                <td><?= htmlspecialchars($cat['name']); ?></td>
                                <td><?= htmlspecialchars(substr($cat['description'] ?? '', 0, 60)); ?></td>
                                <td>
                                    <button class="btn btn-sm btn-warning" onclick="editCategory(<?= htmlspecialchars(json_encode($cat)); ?>)">
                                        <i class="bi bi-pencil"></i> Edit
                                    </button>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="action" value="delete_category">
                                        <input type="hidden" name="id" value="<?= $cat['id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this category?');">
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

<!-- Category Modal -->
<div class="modal fade" id="categoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="categoryTitle">Add Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="save_category">
                <input type="hidden" name="id" id="categoryId" value="0">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Category Name</label>
                        <input type="text" class="form-control" id="categoryName" name="name" required minlength="2">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" id="categoryDesc" name="description" rows="3"></textarea>
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
function editCategory(cat) {
    document.getElementById('categoryId').value = cat.id;
    document.getElementById('categoryName').value = cat.name;
    document.getElementById('categoryDesc').value = cat.description || '';
    document.getElementById('categoryTitle').textContent = 'Edit Category';
    new bootstrap.Modal(document.getElementById('categoryModal')).show();
}
</script>
</body>
</html>
