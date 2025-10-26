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
      <li><a class="dropdown-item" href="orders.php">My Orders</a></li>
      <li><a class="dropdown-item" href="address.php">My Address</a></li>
      <li><a class="dropdown-item" href="track.php">Track Order</a></li>
      <li><a class="dropdown-item" href="return.php">Return Order</a></li>
      <li><hr class="dropdown-divider"></li>
      <li><a class="dropdown-item" href="logout.php">Logout</a></li>
    <?php else: ?>
      <li><a class="dropdown-item" href="login.php">Login</a></li>
      <li><a class="dropdown-item" href="register.php">Register</a></li>
    <?php endif; ?>
  </ul>
</div>



        <!-- Cart Icon -->
            <a href="cart.php" class="text-dark fs-5 position-relative">
              <i class="bi bi-cart"></i>
              <?php if (!empty($_SESSION['cart'])): ?>
                <span class="badge bg-danger position-absolute top-0 start-100 translate-middle"
                      style="width: 18px; height: 18px; display: flex; align-items: center; justify-content: center; border-radius: 50%;
                             font-size: 0.6rem;">
                  <?= array_sum(array_column($_SESSION['cart'], 'quantity')); ?>
                </span>
              <?php endif; ?>
            </a>


      </div>
  </div>
</nav>
