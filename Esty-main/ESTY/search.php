<?php
session_start();
require 'db.php';
require_once __DIR__ . '/cart_helpers.php';

if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
  $_SESSION['cart'] = [];
}

if (isset($_SESSION['user_id'])) {
  sync_session_cart_from_db($conn, $_SESSION['user_id']);
}

// Handle AJAX add-to-cart requests so other pages can update without reload
if (isset($_POST['add_to_cart']) && isset($_POST['ajax']) && $_POST['ajax'] === 'true') {
  $product_id = intval($_POST['id'] ?? 0);

  $stock = null; $product_name = null; $price = null; $image = null;
  $pstmt = $conn->prepare("SELECT name, price, COALESCE(stock,0) as stock, image FROM products WHERE id = ?");
  $pstmt->bind_param('i', $product_id);
  $pstmt->execute();
  $pstmt->bind_result($product_name, $price, $stock, $image);
  $pstmt->fetch();
  $pstmt->close();

  if ($product_name === null) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Product not found']);
    exit;
  }

  if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT quantity FROM carts WHERE user_id = ? AND product_id = ?");
    $stmt->bind_param('ii', $user_id, $product_id);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
      $stmt->bind_result($qty);
      $stmt->fetch();
      $newQty = min($qty + 1, max(0, (int)$stock));
      if ($newQty > 0) {
        $update = $conn->prepare("UPDATE carts SET quantity = ? WHERE user_id = ? AND product_id = ?");
        $update->bind_param('iii', $newQty, $user_id, $product_id);
        $update->execute();
        $_SESSION['flash'] = "✓ $product_name added to cart!";
        $_SESSION['flash_type'] = 'success';
        $_SESSION['last_product_name'] = $product_name;
        $_SESSION['last_product_quantity'] = $newQty;
        $_SESSION['last_product_price'] = $price;
        $_SESSION['last_product_image'] = $image;
        if ($newQty < $qty + 1) {
          $_SESSION['flash'] .= " (Only $stock left in stock)";
        }
      } else {
        $_SESSION['flash'] = "$product_name is out of stock.";
        $_SESSION['flash_type'] = 'warning';
      }
    } else {
      if ($stock > 0) {
        $insert = $conn->prepare("INSERT INTO carts (user_id, product_id, quantity) VALUES (?, ?, 1)");
        $insert->bind_param('ii', $user_id, $product_id);
        $insert->execute();
        $_SESSION['flash'] = "✓ $product_name added to cart!";
        $_SESSION['flash_type'] = 'success';
        $_SESSION['last_product_name'] = $product_name;
        $_SESSION['last_product_quantity'] = 1;
        $_SESSION['last_product_price'] = $price;
        $_SESSION['last_product_image'] = $image;
      } else {
        $_SESSION['flash'] = "$product_name is out of stock.";
        $_SESSION['flash_type'] = 'warning';
      }
    }
    $stmt->close();
    sync_user_cart($conn, $user_id);
  } else {
    if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
      $_SESSION['cart'] = [];
    }
    $found = false;
    foreach ($_SESSION['cart'] as &$item) {
      if ($item['id'] == $product_id) {
        $item['quantity'] = min($item['quantity'] + 1, max(0, (int)$stock));
        if ($item['quantity'] > 0) {
          $_SESSION['flash'] = "✓ $product_name added to cart!";
          $_SESSION['flash_type'] = 'success';
          $_SESSION['last_product_name'] = $product_name;
          $_SESSION['last_product_quantity'] = $item['quantity'];
          $_SESSION['last_product_price'] = $price;
          $_SESSION['last_product_image'] = $image;
          if ($item['quantity'] >= $stock) {
            $_SESSION['flash'] .= " (Only $stock left in stock)";
          }
        } else {
          $_SESSION['flash'] = "$product_name is out of stock.";
          $_SESSION['flash_type'] = 'warning';
        }
        $found = true;
        break;
      }
    }
    unset($item);
    if (!$found) {
      $qty = min(1, max(0, (int)$stock));
      if ($qty > 0) {
        $_SESSION['cart'][] = [
          'id' => $product_id,
          'name' => $product_name,
          'price' => $price,
          'quantity' => $qty,
          'image' => $image,
        ];
        $_SESSION['flash'] = "✓ $product_name added to cart!";
        $_SESSION['flash_type'] = 'success';
        $_SESSION['last_product_name'] = $product_name;
        $_SESSION['last_product_quantity'] = $qty;
        $_SESSION['last_product_price'] = $price;
        $_SESSION['last_product_image'] = $image;
      } else {
        $_SESSION['flash'] = "$product_name is out of stock.";
        $_SESSION['flash_type'] = 'warning';
      }
    }
  }

  $cart_count = 0;
  $cart_total = 0;
  if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $totals = $conn->query("SELECT SUM(p.price * c.quantity) as total, COUNT(c.id) as count FROM carts c JOIN products p ON c.product_id = p.id WHERE c.user_id = $user_id");
    if ($totals) {
      $row = $totals->fetch_assoc();
      $cart_count = $row['count'] ?? 0;
      $cart_total = $row['total'] ?? 0;
    }
  } elseif (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    $cart_count = count($_SESSION['cart']);
    foreach ($_SESSION['cart'] as $item) {
      $cart_total += $item['price'] * $item['quantity'];
    }
  }

  header('Content-Type: application/json');
  echo json_encode([
    'success' => isset($_SESSION['flash_type']) && $_SESSION['flash_type'] === 'success',
    'message' => $_SESSION['flash'] ?? '',
    'product_name' => $_SESSION['last_product_name'] ?? '',
    'cart_count' => $cart_count,
    'cart_total' => $cart_total,
  ]);
  exit;
}

// Handle regular form submissions (non-AJAX) so cart count refreshes like on index.php
if (isset($_POST['add_to_cart']) && (!isset($_POST['ajax']) || $_POST['ajax'] !== 'true')) {
  $product_id = intval($_POST['id'] ?? 0);
  $searchTerm = trim($_POST['search_query'] ?? ($_GET['q'] ?? ''));

  $stock = null; $product_name = null; $price = null; $image = null;
  $pstmt = $conn->prepare("SELECT name, price, COALESCE(stock,0) as stock, image FROM products WHERE id = ?");
  $pstmt->bind_param('i', $product_id);
  $pstmt->execute();
  $pstmt->bind_result($product_name, $price, $stock, $image);
  $pstmt->fetch();
  $pstmt->close();

  if ($product_name === null) {
    $redirect = 'search.php';
    if ($searchTerm !== '') {
      $redirect .= '?q=' . urlencode($searchTerm);
    }
    header('Location: ' . $redirect);
    exit;
  }

  if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT quantity FROM carts WHERE user_id = ? AND product_id = ?");
    $stmt->bind_param('ii', $user_id, $product_id);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
      $stmt->bind_result($qty);
      $stmt->fetch();
      $newQty = min($qty + 1, max(0, (int)$stock));
      if ($newQty > 0) {
        $update = $conn->prepare("UPDATE carts SET quantity = ? WHERE user_id = ? AND product_id = ?");
        $update->bind_param('iii', $newQty, $user_id, $product_id);
        $update->execute();
        if ($newQty < $qty + 1) {
          $_SESSION['flash'] = "Only $stock left in stock for $product_name.";
          $_SESSION['flash_type'] = 'warning';
        } else {
          $_SESSION['flash'] = "✓ $product_name added to cart!";
          $_SESSION['flash_type'] = 'success';
          $_SESSION['last_product_name'] = $product_name;
          $_SESSION['last_product_quantity'] = $newQty;
          $_SESSION['last_product_price'] = $price;
          $_SESSION['last_product_image'] = $image;
        }
      } else {
        $_SESSION['flash'] = "$product_name is out of stock.";
        $_SESSION['flash_type'] = 'warning';
      }
    } else {
      if ($stock > 0) {
        $insert = $conn->prepare("INSERT INTO carts (user_id, product_id, quantity) VALUES (?, ?, 1)");
        $insert->bind_param('ii', $user_id, $product_id);
        $insert->execute();
        $_SESSION['flash'] = "✓ $product_name added to cart!";
        $_SESSION['flash_type'] = 'success';
        $_SESSION['last_product_name'] = $product_name;
        $_SESSION['last_product_quantity'] = 1;
        $_SESSION['last_product_price'] = $price;
        $_SESSION['last_product_image'] = $image;
      } else {
        $_SESSION['flash'] = "$product_name is out of stock.";
        $_SESSION['flash_type'] = 'warning';
      }
    }
    $stmt->close();
    sync_user_cart($conn, $user_id);
  } else {
    $qtyToAdd = min(1, max(0, (int)$stock));
    $found = false;
    foreach ($_SESSION['cart'] as &$item) {
      if ($item['id'] == $product_id) {
        $item['quantity'] = min($item['quantity'] + 1, max(0, (int)$stock));
        if ($item['quantity'] == 0) {
          $_SESSION['flash'] = "$product_name is out of stock.";
          $_SESSION['flash_type'] = 'warning';
        } elseif ($item['quantity'] >= $stock) {
          $_SESSION['flash'] = "Only $stock left in stock for $product_name.";
          $_SESSION['flash_type'] = 'warning';
        } else {
          $_SESSION['flash'] = "✓ $product_name added to cart!";
          $_SESSION['flash_type'] = 'success';
          $_SESSION['last_product_name'] = $product_name;
          $_SESSION['last_product_quantity'] = $item['quantity'];
          $_SESSION['last_product_price'] = $price;
          $_SESSION['last_product_image'] = $image;
        }
        $found = true;
        break;
      }
    }
    unset($item);

    if (!$found) {
      if ($qtyToAdd > 0) {
        $_SESSION['cart'][] = [
          'id' => $product_id,
          'name' => $product_name,
          'price' => $price,
          'quantity' => $qtyToAdd,
          'image' => $image,
        ];
        $_SESSION['flash'] = "✓ $product_name added to cart!";
        $_SESSION['flash_type'] = 'success';
        $_SESSION['last_product_name'] = $product_name;
        $_SESSION['last_product_quantity'] = $qtyToAdd;
        $_SESSION['last_product_price'] = $price;
        $_SESSION['last_product_image'] = $image;
      } else {
        $_SESSION['flash'] = "$product_name is out of stock.";
        $_SESSION['flash_type'] = 'warning';
      }
    }
  }

  $redirect = 'search.php';
  if ($searchTerm !== '') {
    $redirect .= '?q=' . urlencode($searchTerm);
  }
  header('Location: ' . $redirect);
  exit;
}

// Get search query
$query = isset($_GET['q']) ? trim($_GET['q']) : '';
$products = [];

if ($query !== '') {
  $like = "%" . $query . "%";
  $stmt = $conn->prepare("
    SELECT 
      p.id, p.name, p.description, p.price, p.image, p.stock,
      c.name AS category_name, b.name AS brand_name,
      COALESCE(pr.average_rating, 0) AS average_rating,
      COALESCE(pr.review_count, 0) AS review_count
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    LEFT JOIN brands b ON p.brand_id = b.id
    LEFT JOIN product_ratings pr ON p.id = pr.product_id
    WHERE p.name LIKE ?
       OR p.description LIKE ?
       OR CAST(p.price AS CHAR) LIKE ?
    ORDER BY p.name ASC
  ");
  if ($stmt) {
    $stmt->bind_param("sss", $like, $like, $like);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
      $products[] = $row;
    }
    $stmt->close();
  } else {
    error_log('Search query prepare failed: ' . $conn->error);
  }
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
  <?php include 'success_notification.php'; ?>

  <div class="container my-5 pt-5">
    <h2 class="mb-4">Search Results for: <span class="text-primary"><?= htmlspecialchars($query); ?></span></h2>

    <div class="row g-4">
      <?php if (!empty($products)): ?>
        <?php foreach ($products as $p): ?>
          <div class="col-md-4">
            <div class="card product-card shadow h-100">
              <a href="product_details.php?id=<?= $p['id']; ?>" style="text-decoration: none; color: inherit;">
                <img src="<?= htmlspecialchars($p['image']); ?>" class="card-img-top" alt="<?= htmlspecialchars($p['name']); ?>" style="height: 250px; object-fit: cover;">
              </a>
              <div class="card-body text-center d-flex flex-column">
                <a href="product_details.php?id=<?= $p['id']; ?>" style="text-decoration: none; color: inherit;">
                  <h6 class="card-title mb-2"><?= htmlspecialchars($p['name']); ?></h6>
                </a>

                <?php
                  $description = $p['description'] ?? '';
                  $shortDesc = strlen($description) > 80 ? substr($description, 0, 80) . '…' : $description;
                ?>
                <p class="small mb-2 text-muted">
                  <?= htmlspecialchars($shortDesc); ?>
                </p>

                <div class="stars mb-2" style="color: rgb(251, 191, 36);">
          <?php
          $rating = floatval($p['average_rating']);
          for ($i = 1; $i <= 5; $i++) {
            if ($i <= floor($rating)) {
              echo "<i class='bi bi-star-fill'></i>";
            } elseif ($i - $rating < 1) {
              echo "<i class='bi bi-star-half'></i>";
            } else {
              echo "<i class='bi bi-star'></i>";
            }
          }
          ?>
                  <span style="font-size: 0.85rem; color: rgb(107, 114, 128);">(<?= (int)($p['review_count'] ?? 0); ?>)</span>
                </div>

                <p class="fw-bold mb-1">₱<?= number_format($p['price'], 2); ?></p>

                <?php if (!empty($p['category_name'])): ?>
                  <p class="small mb-2" style="color: rgb(107, 114, 128);">📦 <?= htmlspecialchars($p['category_name']); ?></p>
                <?php endif; ?>

                <div class="mt-auto">
                  <?php $stock = isset($p['stock']) ? (int)$p['stock'] : 0; ?>
                  <p class="mb-2">
                    <span class="badge <?= $stock > 0 ? 'bg-success' : 'bg-danger'; ?>">
                      <?= $stock > 0 ? 'In Stock' : 'Out of Stock'; ?>
                    </span>
                  </p>
                  <form method="POST" action="search.php" class="d-grid">
                    <input type="hidden" name="id" value="<?= $p['id']; ?>">
                    <input type="hidden" name="name" value="<?= htmlspecialchars($p['name']); ?>">
                    <input type="hidden" name="price" value="<?= $p['price']; ?>">
                    <input type="hidden" name="search_query" value="<?= htmlspecialchars($query); ?>">
                    <button type="submit" name="add_to_cart" class="btn btn-primary btn-sm w-100" <?= $stock <= 0 ? 'disabled' : ''; ?>>
                      <i class="bi bi-cart-plus"></i> Add to Cart
                    </button>
                  </form>
                </div>
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
