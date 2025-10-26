<?php
session_start();
require 'db.php';
include 'navbar.php';

// Ensure products table has a `stock` column so UI and cart logic can use it
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

// Add to cart
if (isset($_POST['add_to_cart'])) {
  $product_id = intval($_POST['id']);

  // Always fetch authoritative product data (name, price, stock)
  $stock = null; $product_name = null; $price = null;
  $pstmt = $conn->prepare("SELECT name, price, COALESCE(stock, 0) as stock FROM products WHERE id = ?");
  $pstmt->bind_param("i", $product_id);
  $pstmt->execute();
  $pstmt->bind_result($product_name, $price, $stock);
  $pstmt->fetch();
  $pstmt->close();

  if ($product_name === null) {
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit;
  }

    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];

        // Check if already in DB
    $stmt = $conn->prepare("SELECT quantity FROM carts WHERE user_id = ? AND product_id = ?");
        $stmt->bind_param("ii", $user_id, $product_id);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($qty);
            $stmt->fetch();
      $newQty = min($qty + 1, max(0, (int)$stock));

      if ($newQty > 0) {
        $update = $conn->prepare("UPDATE carts SET quantity = ? WHERE user_id = ? AND product_id = ?");
        $update->bind_param("iii", $newQty, $user_id, $product_id);
        $update->execute();
        if ($newQty < $qty + 1) {
          $_SESSION['flash'] = "Only $stock left in stock for $product_name.";
        }
      } else {
        $_SESSION['flash'] = "$product_name is out of stock.";
      }
        } else {
      if ($stock > 0) {
        $insert = $conn->prepare("INSERT INTO carts (user_id, product_id, quantity) VALUES (?, ?, 1)");
        $insert->bind_param("ii", $user_id, $product_id);
        $insert->execute();
      } else {
        $_SESSION['flash'] = "$product_name is out of stock.";
      }
        }

        // Update session cart immediately
        $found = false;
    if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
        foreach ($_SESSION['cart'] as &$item) {
            if ($item['id'] == $product_id) {
        $item['quantity'] = min($item['quantity'] + 1, max(0, (int)$stock));
        if ($item['quantity'] == 0) {
          $_SESSION['flash'] = "$product_name is out of stock.";
        } else if ($item['quantity'] >= $stock) {
          $_SESSION['flash'] = "Only $stock left in stock for $product_name.";
        }
                $found = true;
                break;
            }
        }
    if (!$found) {
      if ($stock > 0) {
        $_SESSION['cart'][] = [
          'id' => $product_id,
          'name' => $product_name,
          'price' => $price,
          'quantity' => 1
        ];
      } else {
        $_SESSION['flash'] = "$product_name is out of stock.";
      }
    }

    } else {
        // Guest cart
    if ($stock <= 0) {
      $_SESSION['flash'] = "$product_name is out of stock.";
    }
    $product = ['id'=>$product_id,'name'=>$product_name,'price'=>$price,'quantity'=>min(1, max(0,(int)$stock))];
        if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
        $found = false;
        foreach ($_SESSION['cart'] as &$item) {
            if ($item['id'] == $product_id) {
        $item['quantity'] = min($item['quantity'] + 1, max(0,(int)$stock));
        if ($item['quantity'] >= $stock) {
          $_SESSION['flash'] = "Only $stock left in stock for $product_name.";
        }
                $found = true;
                break;
            }
        }
    if (!$found && $product['quantity'] > 0) $_SESSION['cart'][] = $product;
    }

    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit;
}

// Fetch featured products (index)
$products = [];
$result = $conn->query("SELECT * FROM products WHERE featured = 1 ORDER BY id ASC LIMIT 4");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) $products[] = $row;
}

// Fetch all products
$all_products = [];
$result_all = $conn->query("SELECT * FROM products ORDER BY id ASC");
if ($result_all && $result_all->num_rows > 0) {
    while ($row = $result_all->fetch_assoc()) $all_products[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Esty Scents | Fragrance Shop</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<link rel="stylesheet" href="style.css">
<style>
/* Admin card style for public products */
.card.card-interactive {
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    transition: transform 0.25s ease, box-shadow 0.25s ease;
}

.card.card-interactive:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 18px rgba(0,0,0,0.08);
}

.card.card-interactive img {
    height: 250px;
    object-fit: cover;
    border-top-left-radius: 0.75rem;
    border-top-right-radius: 0.75rem;
}
</style>
</head>
<body>

<!-- Highlighted Product Banner -->
<section class="new-product-banner d-flex align-items-center justify-content-center text-center text-white">
  <div class="overlay"></div>
  <div class="content">
    <h2 class="fw-bold"> E S T Y </h2>
    <p class="lead">Discover our latest fragrance collection</p>
    <a href="products.php" class="btn btn-lg btn-light">Shop Now</a>
  </div>
</section>

<!-- Featured Products -->
<section id="products" class="container my-5">
  <h2 class="text-center mb-4">Featured Products</h2>
  <div class="row g-4 align-items-stretch">
    <?php if (!empty($products)): ?>
      <?php foreach ($products as $p): ?>
        <div class="col-md-3 d-flex">
          <div class="card card-interactive shadow h-100">
            <img src="<?= htmlspecialchars($p['image']); ?>" class="card-img-top" alt="<?= htmlspecialchars($p['name']); ?>">
            <div class="card-body text-center d-flex flex-column justify-content-between">
              <div>
                <h5 class="card-title"><?= htmlspecialchars($p['name']); ?></h5>
                    <p class="card-text small mb-1 text-muted"><?= htmlspecialchars($p['description']); ?></p>
                    <p class="card-text">₱<?= number_format($p['price'], 2); ?></p>
                    <?php $stock = isset($p['stock']) ? (int)$p['stock'] : null; ?>
                    <p class="mb-2">
                      <?php if ($stock !== null): ?>
                        <span class="badge <?= $stock > 0 ? 'bg-success' : 'bg-secondary'; ?>"><?= $stock > 0 ? ('In stock: ' . $stock) : 'Out of stock'; ?></span>
                      <?php endif; ?>
                    </p>
              </div>
              <form method="POST">
                <input type="hidden" name="id" value="<?= $p['id']; ?>">
                <input type="hidden" name="name" value="<?= htmlspecialchars($p['name']); ?>">
                <input type="hidden" name="price" value="<?= $p['price']; ?>">
                    <button type="submit" name="add_to_cart" class="btn btn-primary mt-auto" <?= ($stock !== null && $stock <= 0) ? 'disabled' : '' ?>>Add to Cart</button>
              </form>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <p class="text-center">No featured products available.</p>
    <?php endif; ?>
  </div>
  <div class="text-center mt-4">
    <a href="products.php" class="btn btn-outline-primary btn-lg">See All Products</a>
  </div>
</section>

<?php if (!empty($_SESSION['flash'])): ?>
  <div class="container mt-3">
    <div class="alert alert-warning"><?= htmlspecialchars($_SESSION['flash']); unset($_SESSION['flash']); ?></div>
  </div>
<?php endif; ?>

<!-- Newsletter -->
<section class="bg-dark text-white text-center p-5">
  <h2>Stay Updated</h2>
  <p>Subscribe for the latest promos and offers!</p>
  <form id="newsletterForm" class="d-flex justify-content-center">
    <input type="email" name="email" placeholder="Enter your email" class="form-control w-25 me-2" required>
    <button type="submit" class="btn btn-warning">Subscribe</button>
  </form>
  <p id="newsletterMessage" class="mt-3"></p>
</section>

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
    .then(response => response.json())
    .then(data => {
        messageEl.textContent = data.message;
        messageEl.className = data.status === 'success' ? 'text-success mt-3' : 'text-danger mt-3';
        if (data.status === 'success') this.reset();
    })
    .catch(err => {
        messageEl.textContent = 'Something went wrong. Please try again.';
        messageEl.className = 'text-danger mt-3';
    });
});
</script>

<!-- Footer -->
<footer class="bg-light text-center py-3">
  <p>&copy; <?= date("Y"); ?> Esty Scents. All Rights Reserved.</p>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
