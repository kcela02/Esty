<?php
session_start();
require 'db.php';

// Handle AJAX add_to_cart requests before any HTML output
if (isset($_POST['add_to_cart']) && isset($_POST['ajax']) && $_POST['ajax'] === 'true') {
    $product_id = intval($_POST['id']);

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
            }
        }
        $stmt->close();
    } else {
        // Guest cart
        $product = ['id'=>$product_id,'name'=>$product_name,'price'=>$price,'quantity'=>min(1, max(0,(int)$stock))];
        if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
        $found = false;
        foreach ($_SESSION['cart'] as &$item) {
            if ($item['id'] == $product_id) {
                $item['quantity'] = min($item['quantity'] + 1, max(0,(int)$stock));
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
                }
                $found = true;
                break;
            }
        }
        if (!$found) {
            if ($product['quantity'] > 0) {
                $_SESSION['cart'][] = $product;
                $_SESSION['flash'] = "✓ $product_name added to cart!";
                $_SESSION['flash_type'] = 'success';
                $_SESSION['last_product_name'] = $product_name;
                $_SESSION['last_product_quantity'] = $product['quantity'];
                $_SESSION['last_product_price'] = $price;
                $_SESSION['last_product_image'] = $image;
            } else {
                $_SESSION['flash'] = "$product_name is out of stock.";
            }
        }
    }

    // Calculate cart totals
    $cart_count = 0;
    $cart_total = 0;
    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
        $result = $conn->query("SELECT SUM(p.price * c.quantity) as total, COUNT(c.id) as count FROM carts c JOIN products p ON c.product_id = p.id WHERE c.user_id = $user_id");
        if ($result) {
            $row = $result->fetch_assoc();
            $cart_count = $row['count'] ?? 0;
            $cart_total = $row['total'] ?? 0;
        }
    } else if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
        $cart_count = count($_SESSION['cart']);
        $cart_total = 0;
        foreach ($_SESSION['cart'] as $item) {
            $cart_total += $item['price'] * $item['quantity'];
        }
    }

    // Return JSON response
    header('Content-Type: application/json');
    echo json_encode([
        'success' => isset($_SESSION['flash_type']) && $_SESSION['flash_type'] === 'success',
        'message' => $_SESSION['flash'] ?? '',
        'product_name' => $_SESSION['last_product_name'] ?? '',
        'cart_count' => $cart_count,
        'cart_total' => $cart_total
    ]);
    exit;
}

// Handle regular form submissions (non-AJAX)
if (isset($_POST['add_to_cart']) && (!isset($_POST['ajax']) || $_POST['ajax'] !== 'true')) {
    $product_id = intval($_POST['id']);

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
            }
        }
        $stmt->close();
    } else {
        // Guest cart
        $product = ['id'=>$product_id,'name'=>$product_name,'price'=>$price,'quantity'=>min(1, max(0,(int)$stock))];
        if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
        $found = false;
        foreach ($_SESSION['cart'] as &$item) {
            if ($item['id'] == $product_id) {
                $item['quantity'] = min($item['quantity'] + 1, max(0,(int)$stock));
                if ($item['quantity'] == 0) {
                    $_SESSION['flash'] = "$product_name is out of stock.";
                } else if ($item['quantity'] >= $stock) {
                    $_SESSION['flash'] = "Only $stock left in stock for $product_name.";
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
        if (!$found) {
            if ($product['quantity'] > 0) {
                $_SESSION['cart'][] = $product;
                $_SESSION['flash'] = "✓ $product_name added to cart!";
                $_SESSION['flash_type'] = 'success';
                $_SESSION['last_product_name'] = $product_name;
                $_SESSION['last_product_quantity'] = $product['quantity'];
                $_SESSION['last_product_price'] = $price;
                $_SESSION['last_product_image'] = $image;
            } else {
                $_SESSION['flash'] = "$product_name is out of stock.";
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

<?php if (!empty($_SESSION['flash']) && !empty($_SESSION['flash_type'])): ?>
  <?php
    $alert_type = $_SESSION['flash_type'];
    
    // Calculate cart totals
    $cart_total = 0;
    $cart_count = 0;
    if (!empty($_SESSION['cart'])) {
      foreach ($_SESSION['cart'] as $item) {
        $cart_total += ($item['price'] * $item['quantity']);
        $cart_count += $item['quantity'];
      }
    }
    
    // Get last added product info from session
    $last_product_name = $_SESSION['last_product_name'] ?? '';
    $last_product_image = $_SESSION['last_product_image'] ?? '';
    $last_product_quantity = $_SESSION['last_product_quantity'] ?? 1;
    $last_product_price = $_SESSION['last_product_price'] ?? 0;
    $last_product_subtotal = $last_product_quantity * $last_product_price;
  ?>
  
  <?php if ($alert_type === 'success' && $last_product_name): ?>
    <!-- Success Card Modal -->
    <div class="position-fixed" style="top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 9999; width: 90%; max-width: 500px;">
      <div class="card" style="border: none; border-radius: 20px; box-shadow: 0 15px 50px rgba(0, 0, 0, 0.25);">
        <!-- Close Button -->
        <button type="button" class="btn-close position-absolute" style="top: 15px; right: 15px; z-index: 10;" data-bs-dismiss="alert" aria-label="Close"></button>
        
        <!-- Success Header -->
        <div style="background: linear-gradient(135deg, rgba(16, 185, 129, 1) 0%, rgba(5, 150, 105, 1) 100%); padding: 20px; border-radius: 20px 20px 0 0; color: white; text-align: center;">
          <div style="font-size: 24px; margin-bottom: 8px;">
            <i class="bi bi-check-circle-fill"></i>
          </div>
          <h5 style="margin: 0; font-weight: 700; font-size: 16px;">Product successfully added to your Shopping Cart</h5>
        </div>
        
        <!-- Product Details -->
        <div style="padding: 25px; border-bottom: 1px solid rgb(229, 231, 235);">
          <div style="display: flex; gap: 15px; align-items: flex-start;">
            <!-- Product Image -->
            <?php if (!empty($last_product_image)): ?>
              <div style="flex-shrink: 0;">
                <img src="<?= htmlspecialchars($last_product_image); ?>" alt="<?= htmlspecialchars($last_product_name); ?>" style="width: 80px; height: 80px; object-fit: cover; border-radius: 10px; border: 1px solid rgb(229, 231, 235);">
              </div>
            <?php endif; ?>
            
            <!-- Product Info -->
            <div style="flex: 1;">
              <h6 style="color: rgb(37, 99, 235); font-weight: 700; margin-bottom: 8px; font-size: 14px;">
                <?= htmlspecialchars($last_product_name); ?>
              </h6>
              <p style="margin: 0; font-size: 14px; color: rgb(102, 102, 102);">
                Quantity: <strong><?= $last_product_quantity; ?></strong>
              </p>
              <p style="margin: 5px 0 0 0; font-size: 14px; color: rgb(102, 102, 102);">
                Cart Total: <strong style="color: rgb(17, 17, 17);">₱<?= number_format($last_product_subtotal, 2); ?></strong>
              </p>
            </div>
          </div>
        </div>
        
        <!-- Cart Summary -->
        <div style="padding: 20px; background-color: rgb(249, 250, 251); border-radius: 0 0 20px 20px;">
          <p style="margin: 0 0 15px 0; font-size: 14px; color: rgb(102, 102, 102);">
            There are <strong><?= $cart_count; ?></strong> item<?= $cart_count !== 1 ? 's' : ''; ?> in your cart.
          </p>
          <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; padding: 12px 0; border-top: 1px solid rgb(229, 231, 235); border-bottom: 1px solid rgb(229, 231, 235);">
            <span style="font-weight: 700; color: rgb(17, 17, 17);">Cart Total:</span>
            <span style="font-weight: 700; font-size: 18px; color: rgb(5, 150, 105);">₱<?= number_format($cart_total, 2); ?></span>
          </div>
          
          <!-- Action Buttons -->
          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
            <button type="button" class="btn" data-bs-dismiss="alert" style="background-color: rgb(229, 231, 235); color: rgb(17, 17, 17); font-weight: 700; border: none; border-radius: 25px; padding: 12px 20px;">
              Continue Shopping
            </button>
            <a href="cart.php" class="btn" style="background-color: rgb(251, 191, 36); color: rgb(17, 17, 17); font-weight: 700; border: none; border-radius: 25px; padding: 12px 20px; text-decoration: none; display: inline-block; text-align: center;">
              Proceed to Checkout
            </a>
          </div>
        </div>
      </div>
    </div>
  <?php else: ?>
    <!-- Warning/Error Alert -->
    <div class="alert alert-<?= $alert_type ?> alert-dismissible fade show" role="alert" 
         style="
           position: fixed;
           top: 50%;
           left: 50%;
           transform: translate(-50%, -50%);
           z-index: 9999;
           width: 90%;
           max-width: 500px;
           padding: 25px 35px;
           border-radius: 15px;
           border: none;
           box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
           font-weight: 600;
           font-size: 16px;
           display: flex;
           align-items: center;
           gap: 15px;
         ">
      <div style="display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: 24px;">
        <i class="bi bi-exclamation-triangle-fill"></i>
      </div>
      <div style="flex: 1;">
        <?= htmlspecialchars($_SESSION['flash']); ?>
      </div>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close" style="flex-shrink: 0;"></button>
    </div>
  <?php endif; ?>
  
  <?php unset($_SESSION['flash']); unset($_SESSION['flash_type']); unset($_SESSION['last_product_name']); unset($_SESSION['last_product_image']); unset($_SESSION['last_product_quantity']); unset($_SESSION['last_product_price']); ?>
<?php endif; ?>

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
              <form method="POST">
                <input type="hidden" name="id" value="<?= $p['id']; ?>">
                <input type="hidden" name="name" value="<?= htmlspecialchars($p['name']); ?>">
                <input type="hidden" name="price" value="<?= $p['price']; ?>">
                <div class="d-grid gap-2">
                    <button type="submit" name="add_to_cart" class="btn btn-primary" <?= ($stock !== null && $stock <= 0) ? 'disabled' : '' ?>>
                      <i class="bi bi-cart-plus"></i> Add to Cart
                    </button>
                    <a href="product_details.php?id=<?= $p['id']; ?>" class="btn btn-secondary btn-sm">
                      <i class="bi bi-eye"></i> View Details
                    </a>
                </div>
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

<!-- Include Login/Register Modals -->
<?php include 'login_register_modals.php'; ?>

<!-- Footer -->
<footer class="bg-light text-center py-3">
  <p>&copy; <?= date("Y"); ?> Esty Scents. All Rights Reserved.</p>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
