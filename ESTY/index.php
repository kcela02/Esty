<?php
session_start();
require 'db.php';

//add to cart
if (isset($_POST['add_to_cart'])) {
    $product = [
        'id' => $_POST['id'],
        'name' => $_POST['name'],
        'price' => $_POST['price'],
        'quantity' => 1
    ];

    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    $found = false;
    foreach ($_SESSION['cart'] as &$item) {
        if ($item['id'] == $product['id']) {
            $item['quantity']++;
            $found = true;
            break;
        }
    }
    if (!$found) {
        $_SESSION['cart'][] = $product;
    }

    header("Location: cart.php");
    exit;
}

// =============================
// Newsletter Subscription
// =============================
$message = "";
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['subscribe'])) {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $stmt = $conn->prepare("INSERT INTO subscribers (email) VALUES (?)");
        $stmt->bind_param("s", $email);
        if ($stmt->execute()) {
            $message = "Thank you for subscribing, " . htmlspecialchars($email) . "!";
        } else {
            $message = "You are already subscribed!";
        }
        $stmt->close();
    } else {
        $message = "Please enter a valid email address.";
    }
}

// =============================
// Fetch Products from Database
// =============================
$products = [];
$result = $conn->query("SELECT * FROM products WHERE featured = 1 ORDER BY id ASC LIMIT 3");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
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
</head>
<body>
  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm py-3 px-4 fixed-top">
    <div class="container-fluid d-flex justify-content-between align-items-center">

      <!-- Left: Logo -->
      <a class="navbar-brand d-flex align-items-center" href="index.php">
        <img src="images/logo.jpg" alt="Esty Scents Logo" class="logo me-2">
        <span class="fw-bold">Esty Scents</span>
      </a>

      <!-- Right: Search + Icons -->
      <div class="d-flex align-items-center gap-3">

        <!-- Search Bar -->
        <form class="d-flex" role="search">
          <input class="form-control search-bar me-2" type="search" placeholder="Search scents..." aria-label="Search">
          <button class="btn btn" type="submit">Search</button>
        </form>

        <!-- User Account -->
        <a href="login.php" class="text-dark fs-5">
          <i class="bi bi-person"></i>
        </a>

        <!-- Cart -->
        <a href="cart.php" class="text-dark fs-5 position-relative">
          <i class="bi bi-cart"></i>
          <?php if (!empty($_SESSION['cart'])): ?>
            <span class="badge bg-danger position-absolute top-0 start-100 translate-middle">
              <?= array_sum(array_column($_SESSION['cart'], 'quantity')); ?>
            </span>
          <?php endif; ?>
        </a>
      </div>
    </div>
  </nav>

  <!-- Highlighted Product -->
<section class="new-product-banner d-flex align-items-center justify-content-center text-center text-white">
  <div class="overlay"></div>
  <div class="content">
    <h2 class="fw-bold"> E S T Y </h2>
    <p class="lead">Discover our latest fragrance collection</p>
    <a href="products.php" class="btn btn-lg btn-light">Shop Now</a>
  </div>
</section>
  <!-- Product Showcase -->
  <section id="products" class="container my-5">
    <h2 class="text-center mb-4">Featured Products</h2>
    <div class="row g-4">
      <?php if (!empty($products)): ?>
        <?php foreach ($products as $p): ?>
          <div class="col-md-4">
            <div class="card shadow">
              <img src="<?= htmlspecialchars($p['image']); ?>" class="card-img-top" alt="<?= htmlspecialchars($p['name']); ?>">
              <div class="card-body text-center">
                <h5 class="card-title"><?= htmlspecialchars($p['name']); ?></h5>
                <p class="card-text">₱<?= number_format($p['price'], 2); ?></p>
                <form method="POST">
                  <input type="hidden" name="id" value="<?= $p['id']; ?>">
                  <input type="hidden" name="name" value="<?= htmlspecialchars($p['name']); ?>">
                  <input type="hidden" name="price" value="<?= $p['price']; ?>">
                  <button type="submit" name="add_to_cart" class="btn btn-primary">Add to Cart</button>
                </form>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <p class="text-center">No products available right now.</p>
      <?php endif; ?>
    </div>

    <!-- See All Products Button -->
  <div class="text-center mt-4">
    <a href="products.php" class="btn btn-outline-primary btn-lg">
      See All Products
    </a>
  </div>
  </section>

  <!-- Newsletter -->
  <section class="bg-dark text-white text-center p-5">
    <h2>Stay Updated</h2>
    <p>Subscribe for the latest promos and offers!</p>
    <form method="POST" class="d-flex justify-content-center">
      <input type="email" name="email" placeholder="Enter your email" class="form-control w-25 me-2" required>
      <button type="submit" name="subscribe" class="btn btn-warning">Subscribe</button>
    </form>
    <?php if ($message): ?>
      <p class="mt-3"><?= $message; ?></p>
    <?php endif; ?>
  </section>

  <!-- Footer -->
  <footer class="bg-light text-center py-3">
    <p>&copy; <?= date("Y"); ?> Esty Scents. All Rights Reserved.</p>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
