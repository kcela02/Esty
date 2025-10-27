<?php
session_start();
require 'db.php';
include 'navbar.php';

$product_id = intval($_GET['id'] ?? 0);

if ($product_id <= 0) {
    header('Location: products.php');
    exit;
}

// Fetch product with category, brand, and rating info
$stmt = $conn->prepare("
    SELECT 
        p.id, p.name, p.description, p.price, p.image, p.stock,
        c.name as category_name, b.name as brand_name,
        COALESCE(pr.average_rating, 0) as average_rating,
        COALESCE(pr.review_count, 0) as review_count
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    LEFT JOIN brands b ON p.brand_id = b.id
    LEFT JOIN product_ratings pr ON p.id = pr.product_id
    WHERE p.id = ?
");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: products.php');
    exit;
}

$product = $result->fetch_assoc();
$stmt->close();

// Fetch reviews
$reviews = [];
$stmt = $conn->prepare("
    SELECT r.id, r.rating, r.title, r.comment, r.created_at, u.username
    FROM product_reviews r
    JOIN users u ON r.user_id = u.id
    WHERE r.product_id = ?
    ORDER BY r.created_at DESC
");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $reviews[] = $row;
}
$stmt->close();

// Check if user already reviewed
$user_already_reviewed = false;
$user_has_purchased = false;

if (isset($_SESSION['user_id'])) {
    $stmt = $conn->prepare("SELECT id FROM product_reviews WHERE product_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $product_id, $_SESSION['user_id']);
    $stmt->execute();
    $stmt->store_result();
    $user_already_reviewed = $stmt->num_rows > 0;
    $stmt->close();
    
    // For now, allow all logged-in users to review (no purchase check needed)
    // To enable purchase verification, add user_id column to orders table
    $user_has_purchased = true;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($product['name']); ?> - Esty Scents</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="style.css">
    <style>
        .product-image { max-height: 400px; object-fit: cover; border-radius: 10px; }
        .stars { color: rgb(251, 191, 36); font-size: 1.1rem; }
        .review-card { border-left: 4px solid rgb(201, 166, 70); padding: 15px; margin: 15px 0; background: rgb(249, 250, 251); border-radius: 5px; }
        .rating-input { font-size: 2rem; cursor: pointer; }
        .rating-input i { color: rgb(200, 200, 200); transition: 0.2s; }
        .rating-input i.active { color: rgb(251, 191, 36); }
    </style>
</head>
<body style="padding-top: 70px;">

<!-- Success Notification -->
<?php include 'success_notification.php'; ?>

<main style="flex: 1;">
<section class="container my-5">
    <div class="row">
        <!-- Product Image -->
        <div class="col-md-5">
            <img src="<?= htmlspecialchars($product['image']); ?>" alt="<?= htmlspecialchars($product['name']); ?>" class="product-image w-100">
        </div>

        <!-- Product Details -->
        <div class="col-md-7">
            <h2><?= htmlspecialchars($product['name']); ?></h2>
            
            <!-- Category & Brand -->
            <?php if ($product['category_name']): ?>
                <p><small class="text-muted">📦 Category: <strong><?= htmlspecialchars($product['category_name']); ?></strong></small></p>
            <?php endif; ?>
            <?php if ($product['brand_name']): ?>
                <p><small class="text-muted">🏷️ Brand: <strong><?= htmlspecialchars($product['brand_name']); ?></strong></small></p>
            <?php endif; ?>

            <!-- Rating -->
            <div class="mb-3">
                <div class="stars">
                    <?php
                    $rating = floatval($product['average_rating']);
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
                </div>
                <p class="mb-0"><strong><?= number_format($rating, 1); ?>/5</strong> (<?= $product['review_count']; ?> reviews)</p>
            </div>

            <!-- Price & Stock -->
            <h3 class="mb-2" style="color: rgb(201, 166, 70);">₱<?= number_format($product['price'], 2); ?></h3>
            <p>
                <?php if ($product['stock'] > 0): ?>
                    <span class="badge bg-success">In Stock (<?= $product['stock']; ?>)</span>
                <?php else: ?>
                    <span class="badge bg-danger">Out of Stock</span>
                <?php endif; ?>
            </p>

            <!-- Description -->
            <p class="my-3"><?= htmlspecialchars($product['description']); ?></p>

            <!-- Add to Cart -->
            <div class="mb-4">
                <div class="d-grid gap-2">
                    <button type="button" onclick="addToCartAjax(<?= $product['id']; ?>, '<?= htmlspecialchars($product['name']); ?>', <?= $product['price']; ?>, '<?= htmlspecialchars($product['image']); ?>')" 
                            class="btn btn-primary btn-lg" <?= $product['stock'] <= 0 ? 'disabled' : '' ?>>
                        <i class="bi bi-cart-plus"></i> Add to Cart
                    </button>
                </div>
            </div>

            <!-- Wishlist & Compare Buttons -->
            <div class="row g-2">
                <div class="col-6">
                    <button class="btn btn-outline-danger w-100" id="wishlistBtn" onclick="toggleWishlist(<?= $product['id']; ?>)">
                        <i class="bi bi-heart"></i> <span id="wishlistText">Add to Wishlist</span>
                    </button>
                </div>
                <div class="col-6">
                    <button class="btn btn-outline-info w-100" id="compareBtn" onclick="toggleCompare(<?= $product['id']; ?>)">
                        <i class="bi bi-graph-up"></i> <span id="compareText">Compare</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Reviews Section -->
    <hr class="my-5">
    <div class="row">
        <div class="col-md-8">
            <h4 class="mb-4">Customer Reviews</h4>

            <?php if (empty($reviews)): ?>
                <p class="text-muted">No reviews yet. Be the first to review this product!</p>
            <?php else: ?>
                <?php foreach ($reviews as $review): ?>
                    <div class="review-card">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <h6 class="mb-0"><?= htmlspecialchars($review['title']); ?></h6>
                                <small class="text-muted">by <?= htmlspecialchars($review['username']); ?> • <?= date('M d, Y', strtotime($review['created_at'])); ?></small>
                            </div>
                            <div class="stars">
                                <?php
                                for ($i = 1; $i <= 5; $i++) {
                                    echo $i <= $review['rating'] ? '<i class="bi bi-star-fill"></i>' : '<i class="bi bi-star"></i>';
                                }
                                ?>
                            </div>
                        </div>
                        <p><?= htmlspecialchars($review['comment']); ?></p>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Review Form -->
        <div class="col-md-4">
            <div class="card" style="border: 2px solid rgb(201, 166, 70);">
                <div class="card-body">
                    <h5 class="card-title mb-3">Write a Review</h5>

                    <?php if (!isset($_SESSION['user_id'])): ?>
                        <p class="text-muted">Please <a href="#" data-bs-toggle="modal" data-bs-target="#loginModal">login</a> to write a review.</p>
                    <?php elseif (!$user_has_purchased): ?>
                        <p class="text-muted">🛒 You must purchase this product to write a review.</p>
                    <?php elseif ($user_already_reviewed): ?>
                        <p class="text-muted">✓ You already reviewed this product.</p>
                    <?php else: ?>
                        <form id="reviewForm" class="gap-3 d-flex flex-column">
                            <!-- Star Rating -->
                            <div>
                                <label class="form-label">Rating</label>
                                <div class="rating-input d-flex gap-2" id="ratingInput">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="bi bi-star" data-value="<?= $i; ?>"></i>
                                    <?php endfor; ?>
                                </div>
                                <input type="hidden" id="ratingValue" name="rating" value="0">
                            </div>

                            <!-- Title -->
                            <div>
                                <label for="reviewTitle" class="form-label">Title</label>
                                <input type="text" class="form-control" id="reviewTitle" name="title" placeholder="Sum up your experience" required minlength="5">
                            </div>

                            <!-- Comment -->
                            <div>
                                <label for="reviewComment" class="form-label">Comment</label>
                                <textarea class="form-control" id="reviewComment" name="comment" rows="4" placeholder="Share your thoughts..." required minlength="20"></textarea>
                            </div>

                            <button type="submit" class="btn btn-primary">Submit Review</button>
                            <div id="reviewMessage" class="mt-2"></div>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>
</main>

<!-- Include modals for login if needed -->
<?php if (!isset($_SESSION['user_id'])): ?>
    <?php include 'login_register_modals.php'; ?>
<?php endif; ?>

<!-- Footer -->
<footer class="bg-light text-center py-3 mt-5">
    <p>&copy; <?= date("Y"); ?> Esty Scents. All Rights Reserved.</p>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Star rating selection
document.querySelectorAll('.rating-input i').forEach(star => {
    star.addEventListener('click', function() {
        const value = this.dataset.value;
        document.getElementById('ratingValue').value = value;
        
        // Update visual
        document.querySelectorAll('.rating-input i').forEach(s => s.classList.remove('active'));
        document.querySelectorAll('.rating-input i').forEach((s, idx) => {
            if (idx < value) s.classList.add('active');
        });
    });

    star.addEventListener('mouseenter', function() {
        const value = this.dataset.value;
        document.querySelectorAll('.rating-input i').forEach((s, idx) => {
            s.classList.toggle('active', idx < value);
        });
    });
});

document.querySelector('.rating-input').addEventListener('mouseleave', function() {
    const value = document.getElementById('ratingValue').value;
    document.querySelectorAll('.rating-input i').forEach((s, idx) => {
        s.classList.toggle('active', idx < value);
    });
});

// Submit review
document.getElementById('reviewForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const rating = document.getElementById('ratingValue').value;
    if (rating === '0') {
        alert('Please select a rating');
        return;
    }

    const formData = new FormData();
    formData.append('product_id', <?= $product_id; ?>);
    formData.append('rating', rating);
    formData.append('title', document.getElementById('reviewTitle').value);
    formData.append('comment', document.getElementById('reviewComment').value);

    fetch('process_review.php', {
        method: 'POST',
        body: formData
    })
    .then(res => {
        if (!res.ok) throw new Error('Network response error');
        return res.json();
    })
    .then(data => {
        const msgEl = document.getElementById('reviewMessage');
        if (data.success) {
            msgEl.innerHTML = '<div class="alert alert-success">' + data.message + '</div>';
            setTimeout(() => location.reload(), 1500);
        } else {
            msgEl.innerHTML = '<div class="alert alert-danger">' + data.message + '</div>';
        }
    })
    .catch(err => {
        console.error('Error:', err);
        document.getElementById('reviewMessage').innerHTML = '<div class="alert alert-danger">Error submitting review: ' + err.message + '</div>';
    });
});

// Wishlist functionality
function toggleWishlist(productId) {
    const wishlistBtn = document.getElementById('wishlistBtn');
    const wishlistText = document.getElementById('wishlistText');
    const isAdded = wishlistBtn.classList.contains('added');
    const action = isAdded ? 'remove' : 'add';

    fetch('process_wishlist.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'action=' + action + '&product_id=' + productId
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            wishlistBtn.classList.toggle('added');
            wishlistText.textContent = isAdded ? 'Add to Wishlist' : 'Added to Wishlist';
            wishlistBtn.classList.toggle('btn-outline-danger', !isAdded);
            wishlistBtn.classList.toggle('btn-danger', isAdded);
            alert(data.message);
        } else if (data.login_required) {
            alert('Please login to add items to wishlist');
            new bootstrap.Modal(document.getElementById('loginModal')).show();
        } else {
            alert(data.message);
        }
    })
    .catch(err => alert('Error: ' + err));
}

// Compare functionality
function toggleCompare(productId) {
    const compareBtn = document.getElementById('compareBtn');
    const compareText = document.getElementById('compareText');
    const isAdded = compareBtn.classList.contains('added');
    const action = isAdded ? 'remove' : 'add';

    fetch('process_compare.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'action=' + action + '&product_id=' + productId
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            compareBtn.classList.toggle('added');
            compareText.textContent = isAdded ? 'Compare' : '📊 Added to Compare';
            compareBtn.classList.toggle('btn-outline-info', !isAdded);
            compareBtn.classList.toggle('btn-info', isAdded);
            alert(data.message);
            
            if (!isAdded) {
                setTimeout(() => {
                    if (confirm('View comparison?')) {
                        window.location.href = 'compare.php';
                    }
                }, 500);
            }
        } else {
            alert(data.message);
        }
    })
    .catch(err => alert('Error: ' + err));
}

// Check wishlist and compare status on page load
window.addEventListener('load', function() {
    const productId = <?= $product_id; ?>;
    
    // Check wishlist
    <?php if (isset($_SESSION['user_id'])): ?>
    fetch('process_wishlist.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'action=check&product_id=' + productId
    })
    .then(res => res.json())
    .then(data => {
        if (data.in_wishlist) {
            const btn = document.getElementById('wishlistBtn');
            btn.classList.add('added', 'btn-danger');
            btn.classList.remove('btn-outline-danger');
            document.getElementById('wishlistText').textContent = 'Added to Wishlist';
        }
    });
    <?php endif; ?>
    
    // Check compare
    fetch('process_compare.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'action=check&product_id=' + productId
    })
    .then(res => res.json())
    .then(data => {
        if (data.in_compare) {
            const btn = document.getElementById('compareBtn');
            btn.classList.add('added', 'btn-info');
            btn.classList.remove('btn-outline-info');
            document.getElementById('compareText').textContent = '📊 Added to Compare';
        }
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
