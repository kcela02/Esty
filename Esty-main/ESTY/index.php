<?php
session_start();
require 'db.php';

// shared cart helpers
require_once __DIR__ . '/cart_helpers.php';

if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
  $_SESSION['cart'] = [];
}

if (isset($_SESSION['user_id'])) {
  sync_session_cart_from_db($conn, $_SESSION['user_id']);
}

// Handle AJAX add_to_cart requests before any HTML output
if (isset($_POST['add_to_cart']) && isset($_POST['ajax']) && $_POST['ajax'] === 'true') {
    $product_id = intval($_POST['id']);

    // DEBUG: Log add-to-cart attempt
    $debug_log = fopen(__DIR__ . '/logs/cart_debug.log', 'a');
    if ($debug_log) {
        $session_qty = 0;
        foreach ($_SESSION['cart'] as $item) { $session_qty += (int)($item['quantity'] ?? 0); }
        $db_qty = 0;
        if (isset($_SESSION['user_id'])) {
            $dq = $conn->query("SELECT COALESCE(SUM(quantity),0) as qty FROM carts WHERE user_id = " . intval($_SESSION['user_id']));
            if ($dq) { $dr = $dq->fetch_assoc(); $db_qty = $dr['qty']; }
        }
        fwrite($debug_log, json_encode(['time' => date('Y-m-d H:i:s'), 'type' => 'AJAX_add', 'product_id' => $product_id, 'session_id' => session_id(), 'user_id' => $_SESSION['user_id'] ?? null, 'session_qty' => $session_qty, 'db_qty' => $db_qty]) . "\n");
        fclose($debug_log);
    }

    // Simple duplicate-submit guard: ignore immediate repeat adds for same product (debounce 2s)
    if (!isset($_SESSION['recent_adds'])) $_SESSION['recent_adds'] = [];
    $last = (int)($_SESSION['recent_adds'][$product_id] ?? 0);
    if (time() - $last < 2) {
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'message' => 'Already added', 'cart_count' => isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0]);
    exit;
  }

    // Fetch product data
    $stock = null; $product_name = null; $price = null; $image = null;
    $pstmt = $conn->prepare("SELECT name, price, COALESCE(stock, 0) as stock, image FROM products WHERE id = ?");
    $pstmt->bind_param("i", $product_id);
    $pstmt->execute();
    $pstmt->bind_result($product_name, $price, $stock, $image);
    $pstmt->fetch();
    $pstmt->close();

    if ($product_name === null) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Product not found']);
        exit;
    }

  // Handle add to cart logic
  if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
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
        $insert->bind_param("ii", $user_id, $product_id);
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
    // Keep session in sync with DB for logged-in users so navbar and cart display immediately
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
      $initialQty = min(1, max(0, (int)$stock));
      if ($initialQty > 0) {
        $_SESSION['cart'][] = [
          'id' => $product_id,
          'name' => $product_name,
          'price' => $price,
          'quantity' => $initialQty,
          'image' => $image,
        ];
        $_SESSION['flash'] = "✓ $product_name added to cart!";
        $_SESSION['flash_type'] = 'success';
        $_SESSION['last_product_name'] = $product_name;
        $_SESSION['last_product_quantity'] = $initialQty;
        $_SESSION['last_product_price'] = $price;
        $_SESSION['last_product_image'] = $image;
      } else {
        $_SESSION['flash'] = "$product_name is out of stock.";
        $_SESSION['flash_type'] = 'warning';
      }
    }
  }

    // Calculate cart totals
    $cart_count = 0;
    $cart_total = 0;
  $cart_qty = 0;
    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
    $result = $conn->query("SELECT SUM(p.price * c.quantity) as total, COUNT(c.id) as count, COALESCE(SUM(c.quantity),0) as qty FROM carts c JOIN products p ON c.product_id = p.id WHERE c.user_id = $user_id");
        if ($result) {
            $row = $result->fetch_assoc();
            $cart_count = $row['count'] ?? 0;
            $cart_total = $row['total'] ?? 0;
      $cart_qty = $row['qty'] ?? 0;
        }
    } else if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    $cart_count = count($_SESSION['cart']);
    $cart_total = 0;
    $cart_qty = 0;
    foreach ($_SESSION['cart'] as $item) {
      $cart_total += $item['price'] * $item['quantity'];
      $cart_qty += (int)($item['quantity'] ?? 0);
    }
    }

    // Return JSON response
    header('Content-Type: application/json');
    echo json_encode([
        'success' => isset($_SESSION['flash_type']) && $_SESSION['flash_type'] === 'success',
        'message' => $_SESSION['flash'] ?? '',
        'product_name' => $_SESSION['last_product_name'] ?? '',
    'cart_count' => $cart_count,
    'cart_total' => $cart_total,
    'cart_qty' => $cart_qty
    ]);
  // record last added time for this product for debounce
  if (!isset($_SESSION['recent_adds'])) $_SESSION['recent_adds'] = [];
  $_SESSION['recent_adds'][$product_id] = time();
    exit;
}

// Handle regular form submissions (non-AJAX)
if (isset($_POST['add_to_cart']) && (!isset($_POST['ajax']) || $_POST['ajax'] !== 'true')) {
    $product_id = intval($_POST['id']);

    // DEBUG: Log add-to-cart attempt
    $debug_log = fopen(__DIR__ . '/logs/cart_debug.log', 'a');
    if ($debug_log) {
        $session_qty = 0;
        foreach ($_SESSION['cart'] as $item) { $session_qty += (int)($item['quantity'] ?? 0); }
        $db_qty = 0;
        if (isset($_SESSION['user_id'])) {
            $dq = $conn->query("SELECT COALESCE(SUM(quantity),0) as qty FROM carts WHERE user_id = " . intval($_SESSION['user_id']));
            if ($dq) { $dr = $dq->fetch_assoc(); $db_qty = $dr['qty']; }
        }
        fwrite($debug_log, json_encode(['time' => date('Y-m-d H:i:s'), 'type' => 'FORM_add', 'product_id' => $product_id, 'session_id' => session_id(), 'user_id' => $_SESSION['user_id'] ?? null, 'session_qty' => $session_qty, 'db_qty' => $db_qty]) . "\n");
        fclose($debug_log);
    }

    // Simple duplicate-submit guard for non-AJAX flows as well (2s)
    if (!isset($_SESSION['recent_adds'])) $_SESSION['recent_adds'] = [];
    $last = (int)($_SESSION['recent_adds'][$product_id] ?? 0);
    if (time() - $last < 2) {
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit;
  }

    // Fetch product data
    $stock = null; $product_name = null; $price = null; $image = null;
    $pstmt = $conn->prepare("SELECT name, price, COALESCE(stock, 0) as stock, image FROM products WHERE id = ?");
    $pstmt->bind_param("i", $product_id);
    $pstmt->execute();
    $pstmt->bind_result($product_name, $price, $stock, $image);
    $pstmt->fetch();
    $pstmt->close();

    if ($product_name === null) {
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit;
    }

  if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
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
        $insert->bind_param("ii", $user_id, $product_id);
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
      $qtyToAdd = min(1, max(0, (int)$stock));
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

    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit;
}

include 'navbar.php';
include 'success_notification.php';

// Fetch featured products (index) WITH ratings
$products = [];
$result = $conn->query("
    SELECT 
        p.*,
        COALESCE(pr.average_rating, 0) as average_rating,
        COALESCE(pr.review_count, 0) as review_count
    FROM products p
    LEFT JOIN product_ratings pr ON p.id = pr.product_id
    WHERE p.featured = 1 
    ORDER BY p.id ASC 
    LIMIT 4
");
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
            <a href="product_details.php?id=<?= $p['id']; ?>" style="text-decoration: none; color: inherit;">
              <img src="<?= htmlspecialchars($p['image']); ?>" class="card-img-top" alt="<?= htmlspecialchars($p['name']); ?>">
            </a>
            <div class="card-body text-center d-flex flex-column justify-content-between">
              <div>
                <a href="product_details.php?id=<?= $p['id']; ?>" style="text-decoration: none; color: inherit;">
                  <h5 class="card-title"><?= htmlspecialchars($p['name']); ?></h5>
                </a>
                <p class="card-text small mb-1 text-muted"><?= htmlspecialchars($p['description']); ?></p>
                
                <!-- Star Rating -->
                <div style="color: rgb(251, 191, 36); font-size: 0.95rem; margin-bottom: 8px;">
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
                
                <p class="card-text">₱<?= number_format($p['price'], 2); ?></p>
                <?php $stock = isset($p['stock']) ? (int)$p['stock'] : null; ?>
                <p class="mb-2">
                  <?php if ($stock !== null): ?>
                    <span class="badge <?= $stock > 0 ? 'bg-success' : 'bg-secondary'; ?>"><?= $stock > 0 ? ('In stock: ' . $stock) : 'Out of stock'; ?></span>
                  <?php endif; ?>
                </p>
              </div>
              <div class="d-grid gap-2">
                    <button type="button" onclick="addToCartAjax(<?= $p['id']; ?>, '<?= htmlspecialchars($p['name']); ?>', <?= $p['price']; ?>, '<?= htmlspecialchars($p['image']); ?>')" 
                            class="btn btn-primary" <?= ($stock !== null && $stock <= 0) ? 'disabled' : '' ?>>
                      <i class="bi bi-cart-plus"></i> Add to Cart
                    </button>
                    <a href="product_details.php?id=<?= $p['id']; ?>" class="btn btn-secondary btn-sm">
                      <i class="bi bi-eye"></i> View Details
                    </a>
                </div>
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
            // Update navbar badge quantity from server-provided cart_qty
            try {
                var qty = parseInt(data.cart_qty) || 0;
                var cartLink = document.getElementById('cartIconLink');
                if (cartLink) {
                    var badge = cartLink.querySelector('.badge');
                    if (!badge && qty > 0) {
                        badge = document.createElement('span');
                        badge.className = 'badge bg-danger position-absolute top-0 start-100 translate-middle';
                        badge.style.width = '18px';
                        badge.style.height = '18px';
                        badge.style.display = 'flex';
                        badge.style.alignItems = 'center';
                        badge.style.justifyContent = 'center';
                        badge.style.fontSize = '0.6rem';
                        badge.style.borderRadius = '50%';
                        cartLink.appendChild(badge);
                    }
                    if (badge) {
                        badge.textContent = qty > 0 ? qty : '';
                        badge.style.display = qty > 0 ? 'flex' : 'none';
                    }
                }
            } catch(e) { console.warn(e); }
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

<!-- Include Login/Register Modals -->
<?php include 'login_register_modals.php'; ?>
<?php include 'cart_offcanvas.php'; ?>

<!-- Footer -->
<footer class="bg-light text-center py-3">
  <p>&copy; <?= date("Y"); ?> Esty Scents. All Rights Reserved.</p>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
