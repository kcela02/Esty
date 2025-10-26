<?php
session_start();
require_once "../db.php";
include 'sidebar.php';

// require admin login
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}

// Flash message
$flash = '';

// Handle add product
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = floatval($_POST['price']);
    $featured = isset($_POST['featured']) ? 1 : 0;

    // handle image upload
    $image_name = '';
    if (!empty($_FILES['image']['name'])) {
        $target_dir = "../uploads/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0755, true);
        $image_name = "images/" . time() . "_" . basename($_FILES['image']['name']);
        move_uploaded_file($_FILES['image']['tmp_name'], "../" . $image_name);
    }

    $stmt = $conn->prepare("INSERT INTO products (name, description, price, image, featured, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param("ssdsi", $name, $description, $price, $image_name, $featured);
    $stmt->execute();
    $stmt->close();

    $flash = "Product '$name' added successfully!";
}

// Handle delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_product'])) {
    $delete_id = intval($_POST['delete_id']);
    $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();
    $stmt->close();
    $flash = "Product deleted successfully!";
}

// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_product'])) {
    $id = intval($_POST['edit_id']);
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = floatval($_POST['price']);
    $featured = isset($_POST['featured']) ? 1 : 0;

    $image_name = '';
    if (!empty($_FILES['image']['name'])) {
        $target_dir = "../uploads/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0755, true);
        $image_name = "images/" . time() . "_" . basename($_FILES['image']['name']);
        move_uploaded_file($_FILES['image']['tmp_name'], "../" . $image_name);
    }

    if ($image_name) {
        $stmt = $conn->prepare("UPDATE products SET name=?, description=?, price=?, image=?, featured=? WHERE id=?");
        $stmt->bind_param("ssdiii", $name, $description, $price, $image_name, $featured, $id);
    } else {
        $stmt = $conn->prepare("UPDATE products SET name=?, description=?, price=?, featured=? WHERE id=?");
        $stmt->bind_param("ssdii", $name, $description, $price, $featured, $id);
    }
    $stmt->execute();
    $stmt->close();
    $flash = "Product updated successfully!";
}

// Fetch products
$products = [];
$result = $conn->query("SELECT * FROM products ORDER BY created_at DESC");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) $products[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin - Products | Esty Scents</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<link rel="stylesheet" href="admin-style.css">
<style>
    .card-img-top { height:200px; object-fit:cover; }
    .cards-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px,1fr)); gap:20px; }
    .modal-header { background-color: #ffb6c1; color:#4B3F2F; }
    .modal-footer .btn { border-radius:25px; }
</style>
</head>
<body>

<div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Products</h2>
        <button class="btn btn-add" data-bs-toggle="modal" data-bs-target="#addProductModal">
            <i class="bi bi-plus-circle me-1"></i> Add Product
        </button>
    </div>

    <?php if($flash): ?>
        <div class="alert alert-success"><?= htmlspecialchars($flash); ?></div>
    <?php endif; ?>

    <?php if(empty($products)): ?>
        <div class="alert alert-light">No products found.</div>
    <?php else: ?>
        <div class="cards-row">
        <?php foreach($products as $p): ?>
            <div class="card">
                <img src="../<?= htmlspecialchars($p['image']); ?>" class="card-img-top" alt="<?= htmlspecialchars($p['name']); ?>">
                <div class="card-body text-center">
                    <h5 class="card-title"><?= htmlspecialchars($p['name']); ?></h5>
                    <p class="card-text"><?= htmlspecialchars($p['description']); ?></p>
                    <p class="fw-bold">₱<?= number_format($p['price'],2); ?></p>
                    <?php if($p['featured']): ?>
                        <span class="badge bg-pink mb-2">Featured</span>
                    <?php endif; ?>
                    <div class="d-flex justify-content-center gap-2 mt-2">
                        <button class="btn btn-sm btn-outline-primary btn-edit"
                            data-id="<?= $p['id']; ?>"
                            data-name="<?= htmlspecialchars($p['name'], ENT_QUOTES); ?>"
                            data-description="<?= htmlspecialchars($p['description'], ENT_QUOTES); ?>"
                            data-price="<?= $p['price']; ?>"
                            data-featured="<?= $p['featured']; ?>"
                            data-bs-toggle="modal" data-bs-target="#editProductModal">
                            <i class="bi bi-pencil-square"></i> Edit
                        </button>
                        <form method="post" onsubmit="return confirm('Delete this product?');">
                            <input type="hidden" name="delete_id" value="<?= $p['id']; ?>">
                            <button type="submit" name="delete_product" class="btn btn-sm btn-outline-danger">
                                <i class="bi bi-trash"></i> Delete
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Add Product Modal -->
<div class="modal fade" id="addProductModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <form method="post" enctype="multipart/form-data">
        <div class="modal-header">
            <h5 class="modal-title">Add New Product</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
            <div class="mb-3"><label class="form-label">Product Name</label><input type="text" name="name" class="form-control" required></div>
            <div class="mb-3"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="3" required></textarea></div>
            <div class="mb-3"><label class="form-label">Price</label><input type="number" step="0.01" name="price" class="form-control" required></div>
            <div class="mb-3"><label class="form-label">Image</label><input type="file" name="image" class="form-control" accept="image/*" required></div>
            <div class="form-check">
                <input type="checkbox" name="featured" class="form-check-input" id="featuredCheck">
                <label class="form-check-label" for="featuredCheck">Featured</label>
            </div>
        </div>
        <div class="modal-footer">
            <button type="submit" name="add_product" class="btn btn-add">Add Product</button>
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Edit Product Modal -->
<div class="modal fade" id="editProductModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <form method="post" enctype="multipart/form-data">
        <div class="modal-header">
            <h5 class="modal-title">Edit Product</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
            <input type="hidden" name="edit_id" id="edit_id">
            <div class="mb-3"><label class="form-label">Product Name</label><input type="text" name="name" id="edit_name" class="form-control" required></div>
            <div class="mb-3"><label class="form-label">Description</label><textarea name="description" id="edit_description" class="form-control" rows="3" required></textarea></div>
            <div class="mb-3"><label class="form-label">Price</label><input type="number" step="0.01" name="price" id="edit_price" class="form-control" required></div>
            <div class="mb-3"><label class="form-label">Image</label><input type="file" name="image" id="edit_image" class="form-control" accept="image/*"><small class="text-muted">Leave blank to keep current image.</small></div>
            <div class="form-check">
                <input type="checkbox" name="featured" id="edit_featured" class="form-check-input">
                <label class="form-check-label" for="edit_featured">Featured</label>
            </div>
        </div>
        <div class="modal-footer">
            <button type="submit" name="update_product" class="btn btn-add">Save Changes</button>
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
document.querySelectorAll('.btn-edit').forEach(button => {
    button.addEventListener('click', () => {
        document.getElementById('edit_id').value = button.dataset.id;
        document.getElementById('edit_name').value = button.dataset.name;
        document.getElementById('edit_description').value = button.dataset.description;
        document.getElementById('edit_price').value = button.dataset.price;
        document.getElementById('edit_featured').checked = button.dataset.featured == "1";
    });
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
