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

</main>

<!-- Include modals -->
<?php include 'login_register_modals.php'; ?>

<!-- Footer -->
<footer class="site-footer mt-5">
    <div class="container footer-top">
        <div class="row g-4">
            <div class="col-12 col-md-3">
                <h5 class="footer-heading">Esty Scents</h5>
                <p class="footer-text">Curating luxurious fragrances inspired by timeless elegance. Discover signature scents crafted to leave a lasting impression.</p>
                <p class="footer-text small">Customer Care: <a href="tel:+639123456789" class="footer-link">+63 912 345 6789</a></p>
            </div>
            <div class="col-6 col-md-3">
                <h5 class="footer-heading">Store Locator</h5>
                <ul class="footer-list">
                    <li><a href="products.php" class="footer-link">Find a Boutique</a></li>
                    <li><a href="#" class="footer-link">Book an Appointment</a></li>
                    <li><a href="products.php" class="footer-link">New Arrivals</a></li>
                    <li><a href="products.php" class="footer-link">Gift Sets</a></li>
                </ul>
            </div>
            <div class="col-6 col-md-3">
                <h5 class="footer-heading">Client Service</h5>
                <ul class="footer-list">
                    <li><a href="my_orders.php" class="footer-link">Orders &amp; Shipping</a></li>
                    <li><a href="return.php" class="footer-link">Returns &amp; Exchanges</a></li>
                    <li><a href="track.php" class="footer-link">Track Your Order</a></li>
                    <li><a href="#" class="footer-link">Help &amp; FAQs</a></li>
                </ul>
            </div>
            <div class="col-12 col-md-3">
                <h5 class="footer-heading">Connect With Us</h5>
                <p class="footer-text small">Follow Esty Scents on social platforms for exclusive previews and scent rituals.</p>
                <div class="footer-social">
                    <a href="#" aria-label="Instagram"><i class="bi bi-instagram"></i></a>
                    <a href="#" aria-label="Facebook"><i class="bi bi-facebook"></i></a>
                    <a href="#" aria-label="Pinterest"><i class="bi bi-pinterest"></i></a>
                    <a href="#" aria-label="YouTube"><i class="bi bi-youtube"></i></a>
                    <a href="#" aria-label="TikTok"><i class="bi bi-tiktok"></i></a>
                </div>
            </div>
        </div>
    </div>
    <div class="footer-bottom">
        <div class="container d-flex flex-column flex-md-row align-items-center justify-content-between gap-3">
            <p class="mb-0 small">&copy; <?= date('Y'); ?> Esty Scents. All rights reserved.</p>
            <div class="footer-bottom-links">
                <a href="#" class="footer-link">Privacy Policy</a>
                <span class="footer-divider">|</span>
                <a href="#" class="footer-link">Terms &amp; Conditions</a>
                <span class="footer-divider">|</span>
                <a href="#" class="footer-link">Contact</a>
            </div>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function showSuccessNotification(data) {
    // Safely convert numeric values
    var cartTotal = parseFloat(data.cart_total) || 0;
    var cartQty = parseInt(data.cart_qty) || 0;
    var lastQty = parseInt(data.last_product_quantity) || 1;
    
    // Build and show success notification dynamically
    var notifHtml = `
    <div id="successNotification" class="position-fixed" style="top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 9999; width: 90%; max-width: 500px;">
        <div class="card" style="border: none; border-radius: 20px; box-shadow: 0 15px 50px rgba(0, 0, 0, 0.25);">
            <button type="button" class="btn-close position-absolute" style="top: 15px; right: 15px; z-index: 10;" onclick="closeNotification()" aria-label="Close"></button>
            <div style="background: linear-gradient(135deg, rgba(16, 185, 129, 1) 0%, rgba(5, 150, 105, 1) 100%); padding: 20px; border-radius: 20px 20px 0 0; color: white; text-align: center;">
                <div style="font-size: 24px; margin-bottom: 8px;">
                    <i class="bi bi-check-circle-fill"></i>
                </div>
                <h5 style="margin: 0; font-weight: 700; font-size: 16px;">Product successfully added to your Shopping Cart</h5>
            </div>
            <div style="padding: 25px; border-bottom: 1px solid rgb(229, 231, 235);">
                <div style="display: flex; gap: 15px; align-items: flex-start;">
                    <div style="flex-shrink: 0;">
                        <img src="${data.product_image || 'images/no-image.png'}" alt="${data.product_name}" style="width: 80px; height: 80px; object-fit: cover; border-radius: 10px; border: 1px solid rgb(229, 231, 235);">
                    </div>
                    <div style="flex: 1;">
                        <h6 style="color: rgb(37, 99, 235); font-weight: 700; margin-bottom: 8px; font-size: 14px;">
                            ${data.product_name}
                        </h6>
                        <p style="margin: 0; font-size: 14px; color: rgb(102, 102, 102);">
                            Quantity: <strong>${lastQty}</strong>
                        </p>
                        <p style="margin: 5px 0 0 0; font-size: 14px; color: rgb(102, 102, 102);">
                            Cart Total: <strong style="color: rgb(17, 17, 17);">₱${cartTotal.toFixed(2)}</strong>
                        </p>
                    </div>
                </div>
            </div>
            <div style="padding: 20px; background-color: rgb(249, 250, 251); border-radius: 0 0 20px 20px;">
                <p style="margin: 0 0 15px 0; font-size: 14px; color: rgb(102, 102, 102);">
                    There are <strong>${cartQty}</strong> item${cartQty !== 1 ? 's' : ''} in your cart.
                </p>
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; padding: 12px 0; border-top: 1px solid rgb(229, 231, 235); border-bottom: 1px solid rgb(229, 231, 235);">
                    <span style="font-weight: 700; color: rgb(17, 17, 17);">Cart Total:</span>
                    <span style="font-weight: 700; font-size: 18px; color: rgb(5, 150, 105);">₱${cartTotal.toFixed(2)}</span>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <button type="button" class="btn" onclick="closeNotification()" style="background-color: rgb(229, 231, 235); color: rgb(17, 17, 17); font-weight: 700; border: none; border-radius: 25px; padding: 12px 20px;">
                        Continue Shopping
                    </button>
                    <a href="cart.php" class="btn" style="background-color: rgb(251, 191, 36); color: rgb(17, 17, 17); font-weight: 700; border: none; border-radius: 25px; padding: 12px 20px; text-decoration: none; display: inline-block; text-align: center;">
                        Proceed to Checkout
                    </a>
                </div>
            </div>
        </div>
    </div>
    `;
    
    // Remove any existing notification
    var existing = document.getElementById('successNotification');
    if (existing) existing.remove();
    
    // Insert new notification
    document.body.insertAdjacentHTML('beforeend', notifHtml);
    
    // Auto-close after 8 seconds
    setTimeout(() => { closeNotification(); }, 8000);
}

function closeNotification() {
    const notification = document.getElementById('successNotification');
    if (notification) {
        notification.style.display = 'none';
    }
}

// Add to cart via AJAX
function addToCartAjax(productId, productName, price, image) {
    console.log('Adding to cart:', productId, productName);
    
    // Disable button to prevent double-click
    var btn = event.target;
    if (btn.tagName !== 'BUTTON') btn = btn.closest('button');
    if (btn) { btn.disabled = true; btn.textContent = 'Adding...'; }
    
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
            try {
                var count = parseInt(data.cart_count) || 0;
                console.log('Updating badge to item count:', count);
                var cartLink = document.getElementById('cartIconLink');
                if (cartLink) {
                    var badge = cartLink.querySelector('.badge');
                    if (!badge) {
                        // Create badge if it doesn't exist
                        badge = document.createElement('span');
                        badge.className = 'badge bg-danger position-absolute top-0 start-100 translate-middle';
                        badge.style.width = '18px';
                        badge.style.height = '18px';
                        badge.style.alignItems = 'center';
                        badge.style.justifyContent = 'center';
                        badge.style.fontSize = '0.6rem';
                        badge.style.borderRadius = '50%';
                        badge.style.display = 'flex';
                        cartLink.appendChild(badge);
                    }
                    // Always update the badge text and visibility
                    badge.textContent = count > 0 ? count : '';
                    badge.style.display = count > 0 ? 'flex' : 'none';
                    console.log('Badge updated to:', count);
                } else {
                    console.warn('cartIconLink not found!');
                }
            } catch(e) { console.error('Badge update error:', e); }
            
            // Show success notification
            showSuccessNotification(data);
        } else {
            alert(data.message || 'Error adding to cart');
        }
    })
    .catch(err => {
        console.error('Error:', err);
        alert('Error adding to cart: ' + err);
    })
    .finally(() => {
        // Re-enable button
        if (btn) { btn.disabled = false; btn.textContent = 'Add to Cart'; }
    });
}
</script>
</body>
</html>
