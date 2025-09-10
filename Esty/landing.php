<?php
// Newsletter form handling
$message = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);

    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        // For demo only (real app: save to DB or send email)
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
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <!-- Hero Section -->
    <header class="hero">
        <div class="hero-text">
            <h1>Welcome to Esty Scents</h1>
            <p>Discover premium fragrances made just for you.</p>
            <a href="#products" class="btn">Shop Now</a>
        </div>
    </header>

    <!-- Product Showcase -->
    <section id="products" class="products">
        <h2>Featured Products</h2>
        <div class="product-grid">
            <div class="product-card">
                <img src="images/product1.jpg" alt="Perfume 1">
                <h3>Rose Bliss</h3>
                <p>₱599</p>
                <button>Add to Cart</button>
            </div>
            <div class="product-card">
                <img src="images/product2.jpg" alt="Perfume 2">
                <h3>Ocean Mist</h3>
                <p>₱699</p>
                <button>Add to Cart</button>
            </div>
            <div class="product-card">
                <img src="images/product3.jpg" alt="Perfume 3">
                <h3>Vanilla Dream</h3>
                <p>₱799</p>
                <button>Add to Cart</button>
            </div>
        </div>
    </section>

    <!-- Newsletter -->
    <section class="newsletter">
        <h2>Stay Updated</h2>
        <p>Subscribe for the latest promos and offers!</p>
        <form method="POST" action="">
            <input type="email" name="email" placeholder="Enter your email" required>
            <button type="submit">Subscribe</button>
        </form>
        <?php if ($message): ?>
            <p class="message"><?= $message; ?></p>
        <?php endif; ?>
    </section>

    <!-- Footer -->
    <footer>
        <p>&copy; <?= date("Y"); ?> Esty Scents. All Rights Reserved.</p>
    </footer>

</body>
</html>