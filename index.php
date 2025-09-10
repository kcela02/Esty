<?php
session_start();

// Handle Add to Cart
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

// Newsletter form
$message = "";
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['subscribe'])) {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Thank you for subscribing, " . htmlspecialchars($email) . "!";
    } else {
        $message = "Please enter a valid email address.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Esty Scents - E-commerce Landing</title>
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

  <!-- Hero Section -->
  <section class="bg-dark text-white text-center p-5">
    <h1>Welcome to Esty Scents</h1>
    <p class="lead">Discover premium fragrances made just for you.</p>
    <a href="#products" class="btn btn-pink btn-lg me-2" style="background:#ff4081;color:#fff;">Shop Now</a>
    <a href="cart.php" class="btn btn-success btn-lg">View Cart</a>
  </section>

  <!-- Product Showcase -->
  <section id="products" class="container my-5">
    <h2 class="text-center mb-4">Featured Products</h2>
    <div class="row g-4">
      <!-- Product 1 -->
      <div class="col-md-4">
        <div class="card shadow">
          <img src="images/product1.jpg" class="card-img-top" alt="Perfume 1">
          <div class="card-body text-center">
            <h5 class="card-title">Rose Bliss</h5>
            <p class="card-text">₱599</p>
            <form method="POST">
              <input type="hidden" name="id" value="1">
              <input type="hidden" name="name" value="Rose Bliss">
              <input type="hidden" name="price" value="599">
              <button type="submit" name="add_to_cart" class="btn btn-primary">Add to Cart</button>
            </form>
          </div>
        </div>
      </div>
      <!-- Product 2 -->
      <div class="col-md-4">
        <div class="card shadow">
          <img src="images/product2.jpg" class="card-img-top" alt="Perfume 2">
          <div class="card-body text-center">
            <h5 class="card-title">Ocean Mist</h5>
            <p class="card-text">₱699</p>
            <form method="POST">
              <input type="hidden" name="id" value="2">
              <input type="hidden" name="name" value="Ocean Mist">
              <input type="hidden" name="price" value="699">
              <button type="submit" name="add_to_cart" class="btn btn-primary">Add to Cart</button>
            </form>
          </div>
        </div>
      </div>
      <!-- Product 3 -->
      <div class="col-md-4">
        <div class="card shadow">
          <img src="images/product3.jpg" class="card-img-top" alt="Perfume 3">
          <div class="card-body text-center">
            <h5 class="card-title">Vanilla Dream</h5>
            <p class="card-text">₱799</p>
            <form method="POST">
              <input type="hidden" name="id" value="3">
              <input type="hidden" name="name" value="Vanilla Dream">
              <input type="hidden" name="price" value="799">
              <button type="submit" name="add_to_cart" class="btn btn-primary">Add to Cart</button>
            </form>
          </div>
        </div>
      </div>
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

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
