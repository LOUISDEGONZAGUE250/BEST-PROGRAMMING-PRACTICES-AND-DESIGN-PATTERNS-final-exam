<?php
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Travel Tour Booking - Home</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header>
        <nav>
            <div class="logo"><a href="index.php" style="font-weight:bold;font-size:1.3rem;">TravelTourBooking</a></div>
            <div>
                <a href="index.php">Home</a>
                <a href="tours.php">Tours</a>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="booking.php">My Bookings</a>
                    <a href="logout.php">Logout</a>
                <?php else: ?>
                    <a href="login.php">Login</a>
                <?php endif; ?>
                <a href="admin/dashboard.php">Admin Dashboard</a>
            </div>
        </nav>
    </header>

    <section class="hero">
        <h1>Pack your bags, let's go somewhere amazing</h1>
        <p>Hidden gems, breathtaking views, unforgettable adventures—where will you go next?</p>
        <a href="tours.php" class="cta-btn">Browse Tours</a>
    </section>

    <main>
        <section class="featured-tours">
            <h2>Featured Tours</h2>
            <div class="tour-cards">
                <?php
                $stmt = $pdo->query("SELECT * FROM tours ORDER BY created_at DESC LIMIT 3");
                while ($tour = $stmt->fetch()) {
                    echo '<div class="tour-card">';
                    $fallbackNumber = ($tour['id'] % 6) + 1; // fallback to one of 6 bundled images
                    $imageSrc = !empty($tour['image_path']) ? $tour['image_path'] : 'assets/images/tour' . $fallbackNumber . '.jpg';
                    echo '<img src="' . htmlspecialchars($imageSrc) . '" alt="' . htmlspecialchars($tour['name']) . '">';
                    echo '<div class="tour-info">';
                    echo '<div class="tour-title">' . htmlspecialchars($tour['name']) . '</div>';
                    echo '<div class="tour-desc">' . htmlspecialchars($tour['description']) . '</div>';
                    echo '<div class="tour-price">From $' . number_format($tour['price'], 2) . '</div>';
                    echo '</div>';
                    echo '</div>';
                }
                ?>
            </div>
        </section>
    </main>

    <footer>
        &copy; <?php echo date('Y'); ?> TravelTourBooking. All rights reserved.
    </footer>

    <script src="assets/js/main.js"></script>
</body>
</html>
