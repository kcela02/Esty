<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm px-4 fixed-top">
  <div class="container-fluid">

      <!-- Left: Logo -->
      <a class="navbar-brand d-flex align-items-center" href="index.php" style="flex:1;">
        <img src="images/logo.jpg" alt="Esty Scents Logo" class="me-2" style="height:50px; width:auto;">
        <span class="fw-bold fs-3">Esty Scents</span>
      </a>

      <!-- Right: Search + Icons -->
      <div class="d-flex align-items-center justify-content-end gap-3" style="flex:2;">

        <!-- Search Bar -->
        <form class="position-relative" action="search.php" method="get" style="width:250px;">
          <input type="text"
                 name="q"
                 class="form-control ps-4 pe-5"
                 placeholder="Search scents..."
                 aria-label="Search">
          <button type="submit"
                  class="position-absolute end-0 top-50 translate-middle-y border-0 bg-transparent p-0 me-2">
            <i class="bi bi-search"></i>
          </button>
        </form>

        <!-- User Account Dropdown (icon only) -->
<div class="dropdown">
  <a href="#" class="text-dark fs-5 dropdown-toggle" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
    <i class="bi bi-person"></i>
  </a>
  <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
    <?php if (isset($_SESSION['user_id'])): ?>
      <li class="dropdown-header">Hello, <?= htmlspecialchars($_SESSION['username']); ?>!</li>
  <li><a class="dropdown-item" href="account.php">My Account</a></li>
  <li><a class="dropdown-item" href="my_orders.php">My Orders</a></li>
      <li><a class="dropdown-item" href="track.php">Track Order</a></li>
      <li><a class="dropdown-item" href="return.php">Return Order</a></li>
      <li><hr class="dropdown-divider"></li>
      <li><a class="dropdown-item" href="logout.php">Logout</a></li>
    <?php else: ?>
      <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#loginModal">Login</a></li>
      <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#registerModal">Register</a></li>
    <?php endif; ?>
  </ul>
</div>



        <!-- Cart Icon -->
            <a href="#" class="text-dark fs-5 position-relative" title="Shopping Cart" data-bs-toggle="offcanvas" data-bs-target="#cartOffcanvas" aria-controls="cartOffcanvas" id="cartIconLink">
              <i class="bi bi-cart"></i>
              <?php
                // Compute authoritative cart quantity: sum of quantities from DB (logged-in) or session (guest)
                $cart_qty = 0;
                if (isset($_SESSION['user_id'])) {
                    $res = $conn->query("SELECT COALESCE(SUM(quantity),0) as qty FROM carts WHERE user_id = " . intval($_SESSION['user_id']));
                    if ($res) { $r = $res->fetch_assoc(); $cart_qty = $r['qty'] ?? 0; }
                } else if (!empty($_SESSION['cart'])) {
                    foreach ($_SESSION['cart'] as $item) { $cart_qty += (int)($item['quantity'] ?? 0); }
                }
                if ($cart_qty > 0):
              ?>
                <span class="badge bg-danger position-absolute top-0 start-100 translate-middle"
                      style="width: 18px; height: 18px; display: flex; align-items: center; justify-content: center; border-radius: 50%;
                             font-size: 0.6rem;">
                  <?= $cart_qty; ?>
                </span>
              <?php endif; ?>
            </a>

            <!-- Wishlist Icon -->
            <?php if (isset($_SESSION['user_id'])): ?>
              <a href="wishlist.php" class="text-dark fs-5 position-relative" title="My Wishlist">
                <i class="bi bi-heart"></i>
              </a>
            <?php endif; ?>

            <!-- Compare Icon -->
            <a href="compare.php" class="text-dark fs-5 position-relative" title="Compare Products">
              <i class="bi bi-graph-up"></i>
            </a>


      </div>
  </div>
</nav>

<!-- Include Login/Register Modals ONLY on pages that need them (cart.php, checkout.php) -->
<!-- DO NOT include on index.php to prevent modal from showing when adding to cart -->
<!-- Ensure the cart offcanvas is available on every page that includes the navbar -->
<?php if (file_exists(__DIR__ . '/cart_offcanvas.php')): ?>
  <?php include __DIR__ . '/cart_offcanvas.php'; ?>
<?php endif; ?>
