<?php
require_once 'config.php';
require_once 'auth_check.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Available Tours - Travel Tour Booking</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 25px;
            border-radius: 5px;
            color: white;
            display: none;
            z-index: 1000;
        }
        .notification.success {
            background-color: #4CAF50;
        }
        .notification.error {
            background-color: #f44336;
        }
    </style>
</head>
<body>
    <div id="notification" class="notification"></div>
    
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
            </div>
        </nav>
    </header>

    <main>
        <section class="tours-section">
            <h2>Available Tours</h2>
            
            <!-- Tour Filtering Section -->
            <div class="filter-section">
                <input type="text" id="tourSearch" placeholder="Search tours...">
                <input type="range" id="priceRange" min="0" max="1500" step="50" value="1500">
                <span id="priceValue">Max Price: $1500</span>
                <select id="durationFilter">
                    <option value="all">All Durations</option>
                    <option value="1">1 Day</option>
                    <option value="2">2 Days</option>
                    <option value="3">3 Days</option>
                    <option value="4">4 Days</option>
                    <option value="5">5 Days</option>
                </select>
            </div>

            <div class="tour-cards">
                <?php
                $stmt = $pdo->query("SELECT * FROM tours ORDER BY created_at DESC");
                while ($tour = $stmt->fetch()) {
                    echo '<div class="tour-card" data-price="' . $tour['price'] . '" data-duration="' . $tour['duration'] . '">';
                    $fallbackNumber = ($tour['id'] % 6) + 1; // fallback set
                    $imageSrc = !empty($tour['image_path']) ? $tour['image_path'] : 'assets/images/tour' . $fallbackNumber . '.jpg';
                    echo '<img src="' . htmlspecialchars($imageSrc) . '" alt="' . htmlspecialchars($tour['name']) . '">';
                    echo '<div class="tour-info">';
                    echo '<div class="tour-title">' . htmlspecialchars($tour['name']) . '</div>';
                    echo '<div class="tour-desc">' . htmlspecialchars($tour['description']) . '</div>';
                    echo '<div class="tour-details">';
                    echo '<span class="tour-price">From $' . number_format($tour['price'], 2) . '</span>';
                    echo '<span class="tour-duration">' . $tour['duration'] . ' days</span>';
                    echo '</div>';
                    echo '<div class="tour-actions">';
                    echo '<a href="tour_details.php?id=' . $tour['id'] . '" class="button">View Details</a>';
                    if (isset($_SESSION['user_id'])) {
                        echo '<a href="book_tour.php?tour_id=' . $tour['id'] . '" class="button primary">Book Now</a>';
                    } else {
                        echo '<a href="login.php" class="button primary">Login to Book</a>';
                    }
                    echo '</div>';
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

    <script>
    function showNotification(message, type = 'success') {
        const notification = document.getElementById('notification');
        notification.textContent = message;
        notification.className = `notification ${type}`;
        notification.style.display = 'block';
        
        setTimeout(() => {
            notification.style.display = 'none';
        }, 3000);
    }

    function processBooking(tourId) {
        fetch('process_booking.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ tour_id: tourId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification(data.message, 'success');
                setTimeout(() => {
                    window.location.href = 'booking.php';
                }, 1500);
            } else {
                showNotification(data.message, 'error');
            }
        })
        .catch(error => {
            showNotification('An error occurred while processing your booking.', 'error');
        });
    }
    </script>
</body>
</html>
