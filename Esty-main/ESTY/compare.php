<?php
session_start();
require 'db.php';
include 'navbar.php';

$user_id = $_SESSION['user_id'] ?? null;
$session_id = session_id();

// Fetch compare list
if ($user_id) {
    $stmt = $conn->prepare("
        SELECT 
            p.id, p.name, p.description, p.price, p.image, p.stock, p.popularity,
            c.name as category_name, b.name as brand_name,
            COALESCE(pr.average_rating, 0) as average_rating,
            COALESCE(pr.review_count, 0) as review_count
        FROM compare_products cp
        JOIN products p ON cp.product_id = p.id
        LEFT JOIN categories c ON p.category_id = c.id
        LEFT JOIN brands b ON p.brand_id = b.id
        LEFT JOIN product_ratings pr ON p.id = pr.product_id
        WHERE cp.user_id = ?
        ORDER BY cp.created_at DESC
    ");
    $stmt->bind_param("i", $user_id);
} else {
    $stmt = $conn->prepare("
        SELECT 
            p.id, p.name, p.description, p.price, p.image, p.stock, p.popularity,
            c.name as category_name, b.name as brand_name,
            COALESCE(pr.average_rating, 0) as average_rating,
            COALESCE(pr.review_count, 0) as review_count
        FROM compare_products cp
        JOIN products p ON cp.product_id = p.id
        LEFT JOIN categories c ON p.category_id = c.id
        LEFT JOIN brands b ON p.brand_id = b.id
        LEFT JOIN product_ratings pr ON p.id = pr.product_id
        WHERE cp.session_id = ?
        ORDER BY cp.created_at DESC
    ");
    $stmt->bind_param("s", $session_id);
}

$stmt->execute();
$result = $stmt->get_result();
$products = [];

while ($row = $result->fetch_assoc()) {
    $products[] = $row;
}
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Compare Products - Esty Scents</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="style.css">
    <style>
        .compare-table { overflow-x: auto; }
        .product-col { min-width: 200px; text-align: center; padding: 15px !important; }
        .product-image { height: 150px; object-fit: cover; border-radius: 10px; margin-bottom: 15px; }
        .stars { color: rgb(251, 191, 36); }
        .feature-label { background: rgb(201, 166, 70); color: white; font-weight: 600; padding: 12px !important; }
        
        @media (max-width: 768px) {
            .compare-table {
                display: none;
            }
            .compare-cards {
                display: block;
            }
        }
        
        @media (min-width: 769px) {
            .compare-cards {
                display: none;
            }
        }
        
        .compare-card {
            border: 2px solid rgb(201, 166, 70);
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .compare-card-label {
            font-weight: 600;
            color: rgb(201, 166, 70);
            margin-bottom: 8px;
        }
        
        .compare-card-value {
            font-size: 0.95rem;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>

<main style="flex: 1;">
<section class="container my-5">
    <h2 class="mb-4">📊 Compare Products</h2>

    <?php if (empty($products)): ?>
        <div class="alert alert-info text-center py-5">
            <i class="bi bi-graph-up" style="font-size: 3rem;"></i>
            <p class="mt-3">No products to compare yet</p>
            <a href="products.php" class="btn btn-primary mt-3">Browse Products</a>
        </div>
    <?php else: ?>
        <!-- Desktop Table View -->
        <div class="compare-table d-none d-lg-block">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <!-- Product Images -->
                    <tr>
                        <td class="feature-label" style="width: 120px;"></td>
                        <?php foreach ($products as $p): ?>
                            <td class="product-col">
                                <img src="<?= htmlspecialchars($p['image']); ?>" alt="<?= htmlspecialchars($p['name']); ?>" class="product-image">
                            </td>
                        <?php endforeach; ?>
                    </tr>

                    <!-- Product Names -->
                    <tr>
                        <td class="feature-label">Product</td>
                        <?php foreach ($products as $p): ?>
                            <td class="product-col">
                                <a href="product_details.php?id=<?= $p['id']; ?>" style="text-decoration: none; color: rgb(37, 99, 235);">
                                    <strong><?= htmlspecialchars($p['name']); ?></strong>
                                </a>
                            </td>
                        <?php endforeach; ?>
                    </tr>

                    <!-- Categories -->
                    <tr>
                        <td class="feature-label">Category</td>
                        <?php foreach ($products as $p): ?>
                            <td class="product-col"><?= htmlspecialchars($p['category_name'] ?? 'N/A'); ?></td>
                        <?php endforeach; ?>
                    </tr>

                    <!-- Brands -->
                    <tr>
                        <td class="feature-label">Brand</td>
                        <?php foreach ($products as $p): ?>
                            <td class="product-col"><?= htmlspecialchars($p['brand_name'] ?? 'N/A'); ?></td>
                        <?php endforeach; ?>
                    </tr>

                    <!-- Price -->
                    <tr>
                        <td class="feature-label">Price</td>
                        <?php foreach ($products as $p): ?>
                            <td class="product-col" style="font-size: 1.2rem; color: rgb(201, 166, 70); font-weight: bold;">
                                ₱<?= number_format($p['price'], 2); ?>
                            </td>
                        <?php endforeach; ?>
                    </tr>

                    <!-- Stock -->
                    <tr>
                        <td class="feature-label">Stock</td>
                        <?php foreach ($products as $p): ?>
                            <td class="product-col">
                                <span class="badge <?= $p['stock'] > 0 ? 'bg-success' : 'bg-danger'; ?>">
                                    <?= $p['stock'] > 0 ? ('In Stock: ' . $p['stock']) : 'Out of Stock'; ?>
                                </span>
                            </td>
                        <?php endforeach; ?>
                    </tr>

                    <!-- Rating -->
                    <tr>
                        <td class="feature-label">Rating</td>
                        <?php foreach ($products as $p): ?>
                            <td class="product-col">
                                <div class="stars">
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
                                </div>
                                <small><?= number_format($rating, 1); ?>/5 (<?= $p['review_count']; ?>)</small>
                            </td>
                        <?php endforeach; ?>
                    </tr>

                    <!-- Popularity -->
                    <tr>
                        <td class="feature-label">Popularity</td>
                        <?php foreach ($products as $p): ?>
                            <td class="product-col"><?= $p['popularity']; ?> views</td>
                        <?php endforeach; ?>
                    </tr>

                    <!-- Description -->
                    <tr>
                        <td class="feature-label">Description</td>
                        <?php foreach ($products as $p): ?>
                            <td class="product-col"><small><?= htmlspecialchars(substr($p['description'], 0, 80)); ?>...</small></td>
                        <?php endforeach; ?>
                    </tr>

                    <!-- Action Buttons -->
                    <tr>
                        <td class="feature-label">Action</td>
                        <?php foreach ($products as $p): ?>
                            <td class="product-col">
                                <div class="d-flex flex-column gap-2">
                                    <form method="POST" action="index.php">
                                        <input type="hidden" name="id" value="<?= $p['id']; ?>">
                                        <input type="hidden" name="name" value="<?= htmlspecialchars($p['name']); ?>">
                                        <input type="hidden" name="price" value="<?= $p['price']; ?>">
                                        <input type="hidden" name="image" value="<?= htmlspecialchars($p['image']); ?>">
                                        <button type="submit" name="add_to_cart" class="btn btn-primary btn-sm" <?= $p['stock'] <= 0 ? 'disabled' : '' ?>>
                                            <i class="bi bi-cart-plus"></i> Cart
                                        </button>
                                    </form>
                                    <button class="btn btn-outline-danger btn-sm" onclick="removeFromCompare(<?= $p['id']; ?>)">
                                        <i class="bi bi-trash"></i> Remove
                                    </button>
                                </div>
                            </td>
                        <?php endforeach; ?>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Mobile Card View -->
        <div class="compare-cards d-lg-none">
            <?php foreach ($products as $p): ?>
                <div class="compare-card">
                    <img src="<?= htmlspecialchars($p['image']); ?>" alt="<?= htmlspecialchars($p['name']); ?>" style="width: 100%; height: 200px; object-fit: cover; border-radius: 8px; margin-bottom: 15px;">
                    
                    <div class="compare-card-label">Product</div>
                    <div class="compare-card-value">
                        <a href="product_details.php?id=<?= $p['id']; ?>" style="text-decoration: none; color: rgb(37, 99, 235); font-weight: bold;">
                            <?= htmlspecialchars($p['name']); ?>
                        </a>
                    </div>

                    <div class="compare-card-label">Category</div>
                    <div class="compare-card-value"><?= htmlspecialchars($p['category_name'] ?? 'N/A'); ?></div>

                    <div class="compare-card-label">Brand</div>
                    <div class="compare-card-value"><?= htmlspecialchars($p['brand_name'] ?? 'N/A'); ?></div>

                    <div class="compare-card-label">Price</div>
                    <div class="compare-card-value" style="font-size: 1.3rem; color: rgb(201, 166, 70); font-weight: bold;">
                        ₱<?= number_format($p['price'], 2); ?>
                    </div>

                    <div class="compare-card-label">Stock</div>
                    <div class="compare-card-value">
                        <span class="badge <?= $p['stock'] > 0 ? 'bg-success' : 'bg-danger'; ?>">
                            <?= $p['stock'] > 0 ? ('In Stock: ' . $p['stock']) : 'Out of Stock'; ?>
                        </span>
                    </div>

                    <div class="compare-card-label">Rating</div>
                    <div class="compare-card-value">
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
                        </div>
                        <small><?= number_format($rating, 1); ?>/5 (<?= $p['review_count']; ?> reviews)</small>
                    </div>

                    <div class="compare-card-label">Popularity</div>
                    <div class="compare-card-value"><?= $p['popularity']; ?> views</div>

                    <div class="compare-card-label">Description</div>
                    <div class="compare-card-value"><small><?= htmlspecialchars(substr($p['description'], 0, 100)); ?></small></div>

                    <div class="d-grid gap-2 mt-3">
                        <form method="POST" action="index.php">
                            <input type="hidden" name="id" value="<?= $p['id']; ?>">
                            <input type="hidden" name="name" value="<?= htmlspecialchars($p['name']); ?>">
                            <input type="hidden" name="price" value="<?= $p['price']; ?>">
                            <input type="hidden" name="image" value="<?= htmlspecialchars($p['image']); ?>">
                            <button type="submit" name="add_to_cart" class="btn btn-primary" <?= $p['stock'] <= 0 ? 'disabled' : '' ?>>
                                <i class="bi bi-cart-plus"></i> Add to Cart
                            </button>
                        </form>
                        <button class="btn btn-outline-danger" onclick="removeFromCompare(<?= $p['id']; ?>)">
                            <i class="bi bi-trash"></i> Remove from Comparison
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
</main>

<!-- Footer -->
<footer class="bg-light text-center py-3 mt-5">
    <p>&copy; <?= date("Y"); ?> Esty Scents. All Rights Reserved.</p>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function removeFromCompare(productId) {
    if (confirm('Remove from comparison?')) {
        fetch('process_compare.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'action=remove&product_id=' + productId
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message);
            }
        });
    }
}
</script>

<!-- Include Login/Register Modals -->
<?php include 'login_register_modals.php'; ?>

</body>
</html>
