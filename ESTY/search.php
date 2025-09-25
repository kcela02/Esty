<?php
session_start();
require 'db.php';

// Get search query
$query = isset($_GET['q']) ? trim($_GET['q']) : '';
$products = [];

if ($query !== '') {
    $like = "%" . $conn->real_escape_string($query) . "%";
    $stmt = $conn->prepare("SELECT * FROM products WHERE name LIKE ? OR description LIKE ? OR price LIKE ?");
    $stmt->bind_param("sss", $like, $like, $like);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Search Results - Esty Scents</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <!-- Navbar -->
  <?php include 'navbar.php'; ?>

  <div class="container my-5 pt-5">
    <h2 class="mb-4">Search Results for: <span class="text-primary"><?= htmlspecialchars($query); ?></span></h2>

    <div class="row g-4">
      <?php if (!empty($products)): ?>
        <?php foreach ($products as $p): ?>
          <div class="col-md-4 d-flex">
            <div class="card shadow h-100">
              <img src="<?= htmlspecialchars($p['image']); ?>" class="card-img-top" alt="<?= htmlspecialchars($p['name']); ?>">
              <div class="card-body text-center d-flex flex-column justify-content-between">
                <div>
                  <h5 class="card-title"><?= htmlspecialchars($p['name']); ?></h5>
                  <p class="card-text"><?= htmlspecialchars($p['description']); ?></p>
                  <p class="card-text fw-bold">₱<?= number_format($p['price'], 2); ?></p>
                </div>
                <form method="POST" action="index.php">
                  <input type="hidden" name="id" value="<?= $p['id']; ?>">
                  <input type="hidden" name="name" value="<?= htmlspecialchars($p['name']); ?>">
                  <input type="hidden" name="price" value="<?= $p['price']; ?>">
                  <button type="submit" name="add_to_cart" class="btn btn-primary mt-auto">Add to Cart</button>
                </form>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <p class="text-muted">No products found matching your search.</p>
      <?php endif; ?>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
