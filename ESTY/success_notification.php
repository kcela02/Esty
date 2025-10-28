<?php
/**
 * Success Notification - Include this in any page where add_to_cart happens
 * This file is meant to be included after the navbar
 */

$flash_message = $_SESSION['flash'] ?? '';
$flash_type = $_SESSION['flash_type'] ?? '';
$last_product_name = $_SESSION['last_product_name'] ?? '';
$last_product_image = $_SESSION['last_product_image'] ?? '';
$last_product_quantity = $_SESSION['last_product_quantity'] ?? 0;
$last_product_price = $_SESSION['last_product_price'] ?? 0;
$last_product_subtotal = $last_product_quantity * $last_product_price;

$success_notification_rendered = false;

if (!empty($flash_message)) {
    if ($flash_type === 'success' && $last_product_name) {
        $cart_count = 0;
        $cart_total = 0;

        if (isset($_SESSION['user_id'])) {
            $user_id = $_SESSION['user_id'];
            $result = $conn->query("SELECT SUM(p.price * c.quantity) as total, COUNT(c.id) as count FROM carts c JOIN products p ON c.product_id = p.id WHERE c.user_id = $user_id");
            if ($result) {
                $row = $result->fetch_assoc();
                $cart_count = $row['count'] ?? 0;
                $cart_total = $row['total'] ?? 0;
            } else {
                error_log("Database error in success_notification.php: " . $conn->error);
            }
        } elseif (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
            foreach ($_SESSION['cart'] as $item) {
                $cart_count += $item['quantity'];
                $cart_total += $item['price'] * $item['quantity'];
            }
        }
        ?>
        <div id="successNotification" class="position-fixed" style="top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 9999; width: 90%; max-width: 500px;">
            <div class="card" style="border: none; border-radius: 20px; box-shadow: 0 15px 50px rgba(0, 0, 0, 0.25);">
                <button type="button" class="btn-close position-absolute" style="top: 15px; right: 15px; z-index: 10;" onclick="closeNotification()" aria-label="Close"></button>
                <div style="background: linear-gradient(135deg, rgba(16, 185, 129, 1) 0%, rgba(5, 150, 105, 1) 100%); padding: 20px; border-radius: 20px 20px 0 0; color: white; text-align: center;">
                    <div style="font-size: 24px; margin-bottom: 8px;">
                        <i class="bi bi-check-circle-fill"></i>
                    </div>
                    <h5 style="margin: 0; font-weight: 700; font-size: 16px;">Product successfully added to your Shopping Cart</h5>
                </div>
                <div style="padding: 25px; border-bottom: 1px solid rgb(229, 231, 235);">
                    <div style="display: flex; gap: 15px; align-items: flex-start;">
                        <?php if (!empty($last_product_image)): ?>
                            <div style="flex-shrink: 0;">
                                <img src="<?= htmlspecialchars($last_product_image); ?>" alt="<?= htmlspecialchars($last_product_name); ?>" style="width: 80px; height: 80px; object-fit: cover; border-radius: 10px; border: 1px solid rgb(229, 231, 235);">
                            </div>
                        <?php endif; ?>
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
                <div style="padding: 20px; background-color: rgb(249, 250, 251); border-radius: 0 0 20px 20px;">
                    <p style="margin: 0 0 15px 0; font-size: 14px; color: rgb(102, 102, 102);">
                        There are <strong><?= $cart_count; ?></strong> item<?= $cart_count !== 1 ? 's' : ''; ?> in your cart.
                    </p>
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; padding: 12px 0; border-top: 1px solid rgb(229, 231, 235); border-bottom: 1px solid rgb(229, 231, 235);">
                        <span style="font-weight: 700; color: rgb(17, 17, 17);">Cart Total:</span>
                        <span style="font-weight: 700; font-size: 18px; color: rgb(5, 150, 105);">₱<?= number_format($cart_total, 2); ?></span>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                        <button type="button" class="btn" onclick="closeNotification()" style="background-color: rgb(229, 231, 235); color: rgb(17, 17, 17); font-weight: 700; border: none; border-radius: 25px; padding: 12px 20px;">
                            Continue Shopping
                        </button>
                        <a href="cart.php" class="btn" style="background-color: rgb(251, 191, 36); color: rgb(17, 17, 17); font-weight: 700; border: none; border-radius: 25px; padding: 12px 20px; text-decoration: none; display: inline-block; text-align: center;">
                            Proceed to Checkout
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <script>
        function closeNotification() {
            const notification = document.getElementById('successNotification');
            if (notification) {
                notification.style.display = 'none';
            }
        }
        setTimeout(() => { closeNotification(); }, 8000);
        </script>
        <?php
        $success_notification_rendered = true;
    } else {
        $alertClass = 'alert-warning';
        if ($flash_type === 'danger') {
            $alertClass = 'alert-danger';
        } elseif ($flash_type === 'info') {
            $alertClass = 'alert-info';
        }
        ?>
        <div class="alert <?= $alertClass ?> alert-dismissible fade show" role="alert" style="position: fixed; top: 20px; right: 20px; z-index: 9999; max-width: 420px;">
            <?= htmlspecialchars($flash_message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php
        $success_notification_rendered = true;
    }
}

if ($success_notification_rendered) {
    unset($_SESSION['flash']);
    unset($_SESSION['flash_type']);
    unset($_SESSION['last_product_name']);
    unset($_SESSION['last_product_image']);
    unset($_SESSION['last_product_quantity']);
    unset($_SESSION['last_product_price']);
}
?>
