<?php
require_once __DIR__ . '/session_bootstrap.php';
require_once "../db.php";
include 'sidebar.php';

requireAdminLogin();

// Ensure products table has a `stock` column. Will create it if missing.
function ensureProductStockColumn(mysqli $conn): void {
    $sql = "SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'products' AND COLUMN_NAME = 'stock'";
    if ($res = $conn->query($sql)) {
        if ($res->num_rows === 0) {
            $conn->query("ALTER TABLE products ADD COLUMN stock INT NOT NULL DEFAULT 0 AFTER featured");
        }
        $res->close();
    }
}

ensureProductStockColumn($conn);

function uploadProductImage(string $fieldName): ?string
{
    if (!isset($_FILES[$fieldName]) || $_FILES[$fieldName]['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    $file = $_FILES[$fieldName];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Image upload failed. Please try again.');
    }

    if ($file['size'] > 2 * 1024 * 1024) {
        throw new RuntimeException('Image must be 2MB or smaller.');
    }

    $allowedMimeTypes = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];

    $mime = null;
    if (class_exists('finfo')) {
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);
    } elseif (function_exists('mime_content_type')) {
        $mime = mime_content_type($file['tmp_name']);
    }

    if (!$mime || !isset($allowedMimeTypes[$mime])) {
        throw new RuntimeException('Only JPG, PNG, or WEBP images are allowed.');
    }

    $uploadsDir = realpath(__DIR__ . '/../uploads');
    if ($uploadsDir === false) {
        $uploadsDir = __DIR__ . '/../uploads';
    }

    if (!is_dir($uploadsDir)) {
        if (!mkdir($uploadsDir, 0755, true) && !is_dir($uploadsDir)) {
            throw new RuntimeException('Failed to prepare upload directory.');
        }
    }

    $filename = bin2hex(random_bytes(16)) . '.' . $allowedMimeTypes[$mime];
    $destination = rtrim($uploadsDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $filename;

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        throw new RuntimeException('Failed to store uploaded image.');
    }

    return 'uploads/' . $filename;
}

function deleteProductImage(?string $relativePath): void
{
    if (empty($relativePath) || !preg_match('#^uploads/[A-Za-z0-9._-]+$#', $relativePath)) {
        return;
    }

    $uploadsRoot = realpath(__DIR__ . '/../uploads');
    $fullPath = realpath(__DIR__ . '/../' . $relativePath);

    if ($uploadsRoot && $fullPath && strpos($fullPath, $uploadsRoot) === 0 && is_file($fullPath)) {
        @unlink($fullPath);
    }
}

// Flash message
$flash = '';
$errorMessage = '';

// Handle add product
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = floatval($_POST['price']);
    $featured = isset($_POST['featured']) ? 1 : 0;
    $stock = isset($_POST['stock']) ? max(0, intval($_POST['stock'])) : 0;

    try {
        $imagePath = uploadProductImage('image');
    } catch (RuntimeException $e) {
        $errorMessage = $e->getMessage();
        $imagePath = null;
    }

    if (!$errorMessage) {
        if ($imagePath === null) {
            $errorMessage = 'Please upload an image for the product.';
        } else {
            $stmt = $conn->prepare("INSERT INTO products (name, description, price, image, featured, stock, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
            $stmt->bind_param("ssdsii", $name, $description, $price, $imagePath, $featured, $stock);
            $stmt->execute();
            $stmt->close();

            $flash = "Product '" . $name . "' added successfully!";
        }
    }
}

// Handle delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_product'])) {
    $delete_id = intval($_POST['delete_id']);
    $currentImage = null;
    $imgStmt = $conn->prepare("SELECT image FROM products WHERE id = ?");
    if ($imgStmt) {
        $imgStmt->bind_param("i", $delete_id);
        $imgStmt->execute();
        $imgStmt->bind_result($currentImage);
        $imgStmt->fetch();
        $imgStmt->close();
    }

    $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();
    $stmt->close();

    if ($currentImage) {
        deleteProductImage($currentImage);
    }

    $flash = "Product deleted successfully!";
}

// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_product'])) {
    $id = intval($_POST['edit_id']);
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = floatval($_POST['price']);
    $featured = isset($_POST['featured']) ? 1 : 0;
    $stock = isset($_POST['stock']) ? max(0, intval($_POST['stock'])) : 0;

    try {
        $imagePath = uploadProductImage('image');
    } catch (RuntimeException $e) {
        $errorMessage = $e->getMessage();
        $imagePath = null;
    }

    if (!$errorMessage) {
        if ($imagePath) {
            $currentImage = null;
            $currentStmt = $conn->prepare("SELECT image FROM products WHERE id = ?");
            if ($currentStmt) {
                $currentStmt->bind_param("i", $id);
                $currentStmt->execute();
                $currentStmt->bind_result($currentImage);
                $currentStmt->fetch();
                $currentStmt->close();
            }

            $stmt = $conn->prepare("UPDATE products SET name=?, description=?, price=?, image=?, featured=?, stock=? WHERE id=?");
            $stmt->bind_param("ssdsiii", $name, $description, $price, $imagePath, $featured, $stock, $id);
            $stmt->execute();
            $stmt->close();

            if ($currentImage) {
                deleteProductImage($currentImage);
            }
        } else {
            $stmt = $conn->prepare("UPDATE products SET name=?, description=?, price=?, featured=?, stock=? WHERE id=?");
            $stmt->bind_param("ssdiii", $name, $description, $price, $featured, $stock, $id);
            $stmt->execute();
            $stmt->close();
        }

        $flash = "Product updated successfully!";
    }
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
<?php $cssv = @filemtime(__DIR__ . '/admin-style.css') ?: time(); ?>
<link rel="stylesheet" href="admin-style.css?v=<?= $cssv ?>">
<style>
    .card-img-top { height:200px; object-fit:cover; }
    .cards-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px,1fr)); gap:20px; }
    .modal-header { background-color: #ffb6c1; color:#4B3F2F; }
    .modal-footer .btn { border-radius:25px; }
</style>
</head>
<body>


<div class="main-content">
    <div class="card mb-4">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 p-3 pb-0">
            <div>
                <h2 class="fw-bold mb-1">Products</h2>
                <p class="muted mb-0">Manage your product catalog and featured items.</p>
            </div>
            <button class="btn btn-add" data-bs-toggle="modal" data-bs-target="#addProductModal">
                <i class="bi bi-plus-circle me-1"></i> Add Product
            </button>
        </div>
        <?php if($flash): ?>
            <div class="alert alert-success mt-3 mb-0"><?= htmlspecialchars($flash); ?></div>
        <?php endif; ?>
        <?php if($errorMessage): ?>
            <div class="alert alert-danger mt-3 mb-0"><?= htmlspecialchars($errorMessage); ?></div>
        <?php endif; ?>
    </div>

    <?php if(empty($products)): ?>
        <div class="alert alert-light card">No products found.</div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach($products as $p): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100">
                        <img src="../<?= htmlspecialchars($p['image']); ?>" class="card-img-top" alt="<?= htmlspecialchars($p['name']); ?>" style="height:200px; object-fit:cover;">
                        <div class="card-body text-center">
                            <h5 class="card-title mb-1"><?= htmlspecialchars($p['name']); ?></h5>
                            <p class="card-text small mb-2"><?= htmlspecialchars($p['description']); ?></p>
                                                        <p class="fw-bold mb-2">₱<?= number_format($p['price'],2); ?></p>
                                                        <?php $__stock = isset($p['stock']) ? (int)$p['stock'] : 0; $__low = 5; ?>
                                                        <p class="mb-2">
                                                                                                                            <?php if ($__stock <= 0): ?>
                                                                                                                                <span class="badge badge-stock-out">Out of stock</span>
                                                                                                                            <?php elseif ($__stock <= $__low): ?>
                                                                                                                                <span class="badge badge-stock-low">Low stock: <?= $__stock; ?></span>
                                                                                                                            <?php else: ?>
                                                                                                                                <span class="badge badge-stock-in">In stock: <?= $__stock; ?></span>
                                                            <?php endif; ?>
                                                        </p>
                            <?php if($p['featured']): ?>
                                <span class="badge bg-pink mb-2">Featured</span>
                            <?php endif; ?>
                            <div class="d-flex justify-content-center gap-2 mt-2 flex-wrap">
                                <button class="btn btn-outline-primary btn-sm btn-edit px-3 py-1"
                                    data-id="<?= $p['id']; ?>"
                                    data-name="<?= htmlspecialchars($p['name'], ENT_QUOTES); ?>"
                                    data-description="<?= htmlspecialchars($p['description'], ENT_QUOTES); ?>"
                                    data-price="<?= $p['price']; ?>"
                                    data-stock="<?= isset($p['stock']) ? intval($p['stock']) : 0; ?>"
                                    data-featured="<?= $p['featured']; ?>"
                                    data-bs-toggle="modal" data-bs-target="#editProductModal">
                                    <i class="bi bi-pencil-square"></i> Edit
                                </button>
                                <form method="post" onsubmit="return confirm('Delete this product?');">
                                    <input type="hidden" name="delete_id" value="<?= $p['id']; ?>">
                                    <button type="submit" name="delete_product" class="btn btn-outline-danger btn-sm px-3 py-1">
                                        <i class="bi bi-trash"></i> Delete
                                    </button>
                                </form>
                            </div>
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
            <div class="mb-3"><label class="form-label">Stock</label><input type="number" min="0" name="stock" class="form-control" value="0" required></div>
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
            <div class="mb-3"><label class="form-label">Stock</label><input type="number" min="0" name="stock" id="edit_stock" class="form-control" required></div>
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
        document.getElementById('edit_stock').value = button.dataset.stock || 0;
        document.getElementById('edit_featured').checked = button.dataset.featured == "1";
    });
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
