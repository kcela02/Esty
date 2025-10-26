<?php
session_start();
require 'db.php';
include 'navbar.php';

// Ensure products table has a `stock` column for showing stock badges
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


// Fetch All Products
$products = [];
$result = $conn->query("SELECT * FROM products ORDER BY id ASC");
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
  <title>All Products - Esty Scents</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  <link rel="stylesheet" href="style.css">
</head>
<body>


  <!-- Product List -->
  <section class="container my-5 pt-5">
    <h2 class="text-center mb-4">All Products</h2>
    <div class="row g-4">
      <?php if (!empty($products)): ?>
        <?php foreach ($products as $p): ?>
          <div class="col-md-3">
            <div class="card card-interactive shadow h-100">
              <img src="<?= htmlspecialchars($p['image']); ?>" class="card-img-top" alt="<?= htmlspecialchars($p['name']); ?>" style="height: 250px; object-fit: cover;">
              <div class="card-body text-center">
                <h5 class="card-title"><?= htmlspecialchars($p['name']); ?></h5>
                <p class="card-text small mb-1 text-muted"><?= htmlspecialchars($p['description']); ?></p>
                <p class="card-text">₱<?= number_format($p['price'], 2); ?></p>
                <?php $stock = isset($p['stock']) ? (int)$p['stock'] : null; ?>
                <p class="mb-2">
                  <?php if ($stock !== null): ?>
                    <span class="badge <?= $stock > 0 ? 'bg-success' : 'bg-secondary'; ?>"><?= $stock > 0 ? ('In stock: ' . $stock) : 'Out of stock'; ?></span>
                  <?php endif; ?>
                </p>
                <form method="POST" action="index.php">
                  <input type="hidden" name="id" value="<?= $p['id']; ?>">
                  <input type="hidden" name="name" value="<?= htmlspecialchars($p['name']); ?>">
                  <input type="hidden" name="price" value="<?= $p['price']; ?>">
                  <button type="submit" name="add_to_cart" class="btn btn-primary" <?= ($stock !== null && $stock <= 0) ? 'disabled' : '' ?>>Add to Cart</button>
                </form>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <p class="text-center">No products available right now.</p>
      <?php endif; ?>
    </div>
  </section>
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

<?php if (!empty($_SESSION['flash'])): ?>
  <div class="container mt-3">
    <div class="alert alert-warning"><?= htmlspecialchars($_SESSION['flash']); unset($_SESSION['flash']); ?></div>
  </div>
<?php endif; ?>

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


  </section>

  <!-- Footer -->
  <footer class="bg-light text-center py-3">
    <p>&copy; <?= date("Y"); ?> Esty Scents. All Rights Reserved.</p>
  </footer>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
