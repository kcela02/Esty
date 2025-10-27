<?php
session_start();
require 'db.php';
include 'navbar.php';

// Ensure products table has rating columns
if (!function_exists('ensureProductStockColumn')) {
  function ensureProductStockColumn(mysqli $conn): void {
    $sql = "SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'products' AND COLUMN_NAME = 'stock'";
    if ($res = $conn->query($sql)) {
      if ($res->num_rows === 0) {
        @$conn->query("ALTER TABLE products ADD COLUMN stock INT NOT NULL DEFAULT 0 AFTER featured");
      }
      $res->close();
    }
  }
}
ensureProductStockColumn($conn);

// Get filter parameters
$category = isset($_GET['category']) ? intval($_GET['category']) : 0;
$brand = isset($_GET['brand']) ? intval($_GET['brand']) : 0;
$min_price = isset($_GET['min_price']) ? floatval($_GET['min_price']) : 0;
$max_price = isset($_GET['max_price']) ? floatval($_GET['max_price']) : 100000;
$min_rating = isset($_GET['min_rating']) ? intval($_GET['min_rating']) : 0;
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Build query
$where = "WHERE 1=1";
$params = [];
$types = "";

if ($search) {
    $where .= " AND (p.name LIKE ? OR p.description LIKE ?)";
    $search_term = "%" . $search . "%";
    $params = [$search_term, $search_term];
    $types = "ss";
}

if ($category > 0) {
    $where .= " AND p.category_id = ?";
    $params[] = $category;
    $types .= "i";
}

if ($brand > 0) {
    $where .= " AND p.brand_id = ?";
    $params[] = $brand;
    $types .= "i";
}

if ($min_price > 0) {
    $where .= " AND p.price >= ?";
    $params[] = $min_price;
    $types .= "d";
}

if ($max_price < 100000) {
    $where .= " AND p.price <= ?";
    $params[] = $max_price;
    $types .= "d";
}

if ($min_rating > 0) {
    $where .= " AND COALESCE(pr.average_rating, 0) >= ?";
    $params[] = $min_rating;
    $types .= "i";
}

// Sort options
$order = "p.created_at DESC";
if ($sort === 'price_low') {
    $order = "p.price ASC";
} elseif ($sort === 'price_high') {
    $order = "p.price DESC";
} elseif ($sort === 'rating') {
    $order = "COALESCE(pr.average_rating, 0) DESC, pr.review_count DESC";
} elseif ($sort === 'popularity') {
    $order = "p.popularity DESC";
}

// Fetch products
$query = "
    SELECT 
        p.id, p.name, p.description, p.price, p.image, p.stock, p.popularity,
        c.name as category_name, b.name as brand_name,
        COALESCE(pr.average_rating, 0) as average_rating,
        COALESCE(pr.review_count, 0) as review_count
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    LEFT JOIN brands b ON p.brand_id = b.id
    LEFT JOIN product_ratings pr ON p.id = pr.product_id
    $where
    ORDER BY $order
";

$stmt = $conn->prepare($query);
if (!empty($types)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

$products = [];
while ($row = $result->fetch_assoc()) {
    $products[] = $row;
}
$stmt->close();

// Fetch all categories for filter
$categories = [];
$result = $conn->query("SELECT id, name FROM categories ORDER BY name");
while ($row = $result->fetch_assoc()) {
    $categories[] = $row;
}

// Fetch all brands for filter
$brands = [];
$result = $conn->query("SELECT id, name FROM brands ORDER BY name");
while ($row = $result->fetch_assoc()) {
    $brands[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>All Products - Esty Scents</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  <link rel="stylesheet" href="style.css">
  <style>
    .filter-card { border-left: 4px solid rgb(201, 166, 70); }
    .stars { color: rgb(251, 191, 36); font-size: 0.95rem; }
    .product-card { transition: transform 0.25s, box-shadow 0.25s; }
    .product-card:hover { transform: translateY(-5px); box-shadow: 0 8px 18px rgba(0,0,0,0.1); }
    .sidebar { background: rgb(249, 250, 251); padding: 20px; border-radius: 10px; }
  </style>
</head>
<body style="padding-top: 70px;">

<!-- Success Notification -->
<?php include 'success_notification.php'; ?>

<main style="flex: 1;">
<section class="container my-5">
    <h2 class="text-center mb-4">All Products</h2>

    <div class="row">
        <!-- Sidebar Filters -->
        <div class="col-lg-3">
            <div class="sidebar">
                <h5 class="mb-3">Filters</h5>

                <form method="GET" action="products.php" id="filterForm">
                    <!-- Search -->
                    <div class="mb-3">
                        <label class="form-label"><strong>Search</strong></label>
                        <input type="text" class="form-control" name="search" value="<?= htmlspecialchars($search); ?>" placeholder="Product name...">
                    </div>

                    <!-- Category -->
                    <div class="mb-3">
                        <label class="form-label"><strong>Category</strong></label>
                        <select class="form-select" name="category" onchange="document.getElementById('filterForm').submit();">
                            <option value="0">All Categories</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id']; ?>" <?= $category === $cat['id'] ? 'selected' : ''; ?>>
                                    <?= htmlspecialchars($cat['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Brand -->
                    <div class="mb-3">
                        <label class="form-label"><strong>Brand</strong></label>
                        <select class="form-select" name="brand" onchange="document.getElementById('filterForm').submit();">
                            <option value="0">All Brands</option>
                            <?php foreach ($brands as $br): ?>
                                <option value="<?= $br['id']; ?>" <?= $brand === $br['id'] ? 'selected' : ''; ?>>
                                    <?= htmlspecialchars($br['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Price Range -->
                    <div class="mb-3">
                        <label class="form-label"><strong>Price Range</strong></label>
                        <div class="d-flex gap-2 mb-2">
                            <input type="number" class="form-control" name="min_price" value="<?= $min_price; ?>" placeholder="Min" min="0">
                            <input type="number" class="form-control" name="max_price" value="<?= $max_price; ?>" placeholder="Max">
                        </div>
                    </div>

                    <!-- Rating -->
                    <div class="mb-3">
                        <label class="form-label"><strong>Min Rating</strong></label>
                        <select class="form-select" name="min_rating" onchange="document.getElementById('filterForm').submit();">
                            <option value="0" <?= $min_rating === 0 ? 'selected' : ''; ?>>Any Rating</option>
                            <option value="1" <?= $min_rating === 1 ? 'selected' : ''; ?>>★ 1 & Up</option>
                            <option value="2" <?= $min_rating === 2 ? 'selected' : ''; ?>>★★ 2 & Up</option>
                            <option value="3" <?= $min_rating === 3 ? 'selected' : ''; ?>>★★★ 3 & Up</option>
                            <option value="4" <?= $min_rating === 4 ? 'selected' : ''; ?>>★★★★ 4 & Up</option>
                            <option value="5" <?= $min_rating === 5 ? 'selected' : ''; ?>>★★★★★ 5 Stars</option>
                        </select>
                    </div>

                    <!-- Sort -->
                    <div class="mb-3">
                        <label class="form-label"><strong>Sort By</strong></label>
                        <select class="form-select" name="sort" onchange="document.getElementById('filterForm').submit();">
                            <option value="newest" <?= $sort === 'newest' ? 'selected' : ''; ?>>Newest</option>
                            <option value="price_low" <?= $sort === 'price_low' ? 'selected' : ''; ?>>Price: Low to High</option>
                            <option value="price_high" <?= $sort === 'price_high' ? 'selected' : ''; ?>>Price: High to Low</option>
                            <option value="rating" <?= $sort === 'rating' ? 'selected' : ''; ?>>Highest Rated</option>
                            <option value="popularity" <?= $sort === 'popularity' ? 'selected' : ''; ?>>Most Popular</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">Apply Filters</button>
                    <a href="products.php" class="btn btn-secondary w-100 mt-2">Clear Filters</a>
                </form>
            </div>
        </div>

        <!-- Products Grid -->
        <div class="col-lg-9">
            <?php if (!empty($products)): ?>
                <div class="row g-4">
                    <?php foreach ($products as $p): ?>
                        <div class="col-md-4">
                            <div class="card product-card shadow h-100">
                                <a href="product_details.php?id=<?= $p['id']; ?>" style="text-decoration: none; color: inherit;">
                                    <img src="<?= htmlspecialchars($p['image']); ?>" class="card-img-top" alt="<?= htmlspecialchars($p['name']); ?>" style="height: 250px; object-fit: cover;">
                                </a>
                                <div class="card-body text-center d-flex flex-column">
                                    <a href="product_details.php?id=<?= $p['id']; ?>" style="text-decoration: none; color: inherit;">
                                        <h6 class="card-title"><?= htmlspecialchars($p['name']); ?></h6>
                                    </a>
                                    
                                    <p class="small mb-2 text-muted"><?= htmlspecialchars(substr($p['description'], 0, 80)); ?>...</p>
                                    
                                    <!-- Rating -->
                                    <div class="stars mb-2">
                                        <?php
                                        $rating = floatval($p['average_rating']);
                                        for ($i = 1; $i <= 5; $i++) {
                                            if ($i <= floor($rating)) {
                                                echo '<i class="bi bi-star-fill"></i>';
                                            } elseif ($i - $rating < 1) {
                                                echo '<i class="bi bi-star-half"></i>';
                                            } else {
                                                echo '<i class="bi bi-star"></i>';
                                            }
                                        }
                                        ?>
                                        <span style="font-size: 0.85rem; color: rgb(107, 114, 128);">(<?= $p['review_count']; ?>)</span>
                                    </div>

                                    <p class="fw-bold mb-1">₱<?= number_format($p['price'], 2); ?></p>
                                    
                                    <?php if ($p['category_name']): ?>
                                        <p class="small mb-2" style="color: rgb(107, 114, 128);">📦 <?= htmlspecialchars($p['category_name']); ?></p>
                                    <?php endif; ?>
                                    
                                    <div class="mt-auto">
                                        <p class="mb-2">
                                            <span class="badge <?= $p['stock'] > 0 ? 'bg-success' : 'bg-danger'; ?>">
                                                <?= $p['stock'] > 0 ? 'In Stock' : 'Out of Stock'; ?>
                                            </span>
                                        </p>
                                        <button type="button" class="btn btn-primary btn-sm w-100" 
                                                onclick="addToCartAjax(<?= $p['id']; ?>, '<?= htmlspecialchars($p['name']); ?>', <?= $p['price']; ?>, '<?= htmlspecialchars($p['image']); ?>')"
                                                <?= $p['stock'] <= 0 ? 'disabled' : '' ?>>
                                            Add to Cart
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="alert alert-info text-center">
                    <i class="bi bi-info-circle"></i> No products found. Try adjusting your filters.
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Newsletter -->
<section class="bg-dark text-white text-center p-5 mt-5">
    <h2>Stay Updated</h2>
    <p>Subscribe for the latest promos and offers!</p>
    <form id="newsletterForm" class="d-flex justify-content-center">
        <input type="email" name="email" placeholder="Enter your email" class="form-control w-25 me-2" required>
        <button type="submit" class="btn btn-warning">Subscribe</button>
    </form>
    <p id="newsletterMessage" class="mt-3"></p>
</section>
</main>

<!-- Include modals -->
<?php include 'login_register_modals.php'; ?>

<!-- Footer -->
<footer class="bg-light text-center py-3">
  <p>&copy; <?= date("Y"); ?> Esty Scents. All Rights Reserved.</p>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.getElementById('newsletterForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const email = this.email.value;
    const messageEl = document.getElementById('newsletterMessage');

    fetch('subscribe.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'email=' + encodeURIComponent(email)
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            messageEl.innerHTML = '<span class="text-success">✓ ' + data.message + '</span>';
            this.reset();
        } else {
            messageEl.innerHTML = '<span class="text-warning">⚠ ' + data.message + '</span>';
        }
    })
    .catch(err => {
        messageEl.innerHTML = '<span class="text-danger">Error subscribing</span>';
    });
});

// Add to cart via AJAX
function addToCartAjax(productId, productName, price, image) {
    console.log('Adding to cart:', productId, productName);
    
    const formData = new FormData();
    formData.append('id', productId);
    formData.append('name', productName);
    formData.append('price', price);
    formData.append('image', image);
    formData.append('add_to_cart', 'true');
    formData.append('ajax', 'true');

    fetch('index.php', {
        method: 'POST',
        body: formData
    })
    .then(res => {
        console.log('Response status:', res.status);
        return res.json();
    })
    .then(data => {
        console.log('Response data:', data);
        if (data.success) {
            // Reload to show notification
            location.reload();
        } else {
            alert(data.message || 'Error adding to cart');
        }
    })
    .catch(err => {
        console.error('Error:', err);
        alert('Error adding to cart: ' + err);
    });
}
</script>
</body>
</html>
