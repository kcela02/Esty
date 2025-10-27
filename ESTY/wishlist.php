<?php
session_start();
require 'db.php';
include 'navbar.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch wishlist items
$wishlist = [];
$stmt = $conn->prepare("
    SELECT 
        p.id, p.name, p.description, p.price, p.image, p.stock,
        c.name as category_name, b.name as brand_name,
        COALESCE(pr.average_rating, 0) as average_rating,
        COALESCE(pr.review_count, 0) as review_count
    FROM wishlists w
    JOIN products p ON w.product_id = p.id
    LEFT JOIN categories c ON p.category_id = c.id
    LEFT JOIN brands b ON p.brand_id = b.id
    LEFT JOIN product_ratings pr ON p.id = pr.product_id
    WHERE w.user_id = ?
    ORDER BY w.created_at DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $wishlist[] = $row;
}
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Wishlist - Esty Scents</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="style.css">
    <style>
        .wishlist-card { transition: all 0.3s ease; }
        .wishlist-card:hover { box-shadow: 0 8px 20px rgba(0,0,0,0.15); transform: translateY(-3px); }
        .stars { color: rgb(251, 191, 36); font-size: 0.95rem; }
        .price-tag { font-size: 1.3rem; color: rgb(201, 166, 70); font-weight: bold; }
    </style>
</head>
<body style="padding-top: 70px;">

<section class="container my-5">
    <h2 class="mb-4">❤️ My Wishlist</h2>

    <?php if (empty($wishlist)): ?>
        <div class="alert alert-info text-center py-5">
            <i class="bi bi-heart" style="font-size: 3rem;"></i>
            <p class="mt-3">Your wishlist is empty</p>
            <a href="products.php" class="btn btn-primary mt-3">Browse Products</a>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach ($wishlist as $item): ?>
                <div class="col-lg-3 col-md-6">
                    <div class="card wishlist-card shadow h-100">
                        <a href="product_details.php?id=<?= $item['id']; ?>" style="text-decoration: none; color: inherit;">
                            <img src="<?= htmlspecialchars($item['image']); ?>" class="card-img-top" alt="<?= htmlspecialchars($item['name']); ?>" style="height: 250px; object-fit: cover;">
                        </a>
                        <div class="card-body d-flex flex-column">
                            <a href="product_details.php?id=<?= $item['id']; ?>" style="text-decoration: none; color: inherit;">
                                <h6 class="card-title"><?= htmlspecialchars($item['name']); ?></h6>
                            </a>

                            <!-- Category -->
                            <?php if ($item['category_name']): ?>
                                <p class="small mb-2" style="color: rgb(107, 114, 128);">📦 <?= htmlspecialchars($item['category_name']); ?></p>
                            <?php endif; ?>

                            <!-- Rating -->
                            <div class="stars mb-2">
                                <?php
                                $rating = floatval($item['average_rating']);
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
                                <span style="font-size: 0.85rem; color: rgb(107, 114, 128);">(<?= $item['review_count']; ?>)</span>
                            </div>

                            <p class="price-tag mb-2">₱<?= number_format($item['price'], 2); ?></p>

                            <p class="mb-3">
                                <span class="badge <?= $item['stock'] > 0 ? 'bg-success' : 'bg-danger'; ?>">
                                    <?= $item['stock'] > 0 ? 'In Stock' : 'Out of Stock'; ?>
                                </span>
                            </p>

                            <div class="d-grid gap-2 mt-auto">
                                <form method="POST" action="index.php">
                                    <input type="hidden" name="id" value="<?= $item['id']; ?>">
                                    <input type="hidden" name="name" value="<?= htmlspecialchars($item['name']); ?>">
                                    <input type="hidden" name="price" value="<?= $item['price']; ?>">
                                    <input type="hidden" name="image" value="<?= htmlspecialchars($item['image']); ?>">
                                    <button type="submit" name="add_to_cart" class="btn btn-primary btn-sm" <?= $item['stock'] <= 0 ? 'disabled' : '' ?>>
                                        <i class="bi bi-cart-plus"></i> Add to Cart
                                    </button>
                                </form>
                                <button class="btn btn-outline-danger btn-sm" onclick="removeFromWishlist(<?= $item['id']; ?>)">
                                    <i class="bi bi-trash"></i> Remove
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<!-- Footer -->
<footer class="bg-light text-center py-3 mt-5">
    <p>&copy; <?= date("Y"); ?> Esty Scents. All Rights Reserved.</p>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function removeFromWishlist(productId) {
    if (confirm('Remove from wishlist?')) {
        fetch('process_wishlist.php', {
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
</body>
</html>
