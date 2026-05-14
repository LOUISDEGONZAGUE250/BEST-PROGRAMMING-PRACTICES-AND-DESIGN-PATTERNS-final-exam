<?php
require_once 'config.php';
require_once 'auth_check.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "User is not logged in.";
    exit();
} else {
    // echo "User ID: " . $_SESSION['user_id'] . "<br>"; // Removed debugging message
}

// Fetch user's bookings with tour details
try {
    $stmt = $pdo->prepare("
        SELECT b.*, t.name as tour_name, t.price as tour_price, t.duration
        FROM bookings b
        JOIN tours t ON b.tour_id = t.id
        WHERE b.user_id = ?
        ORDER BY b.booking_date DESC
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $bookings = $stmt->fetchAll();

    // Debugging: Check if any bookings were fetched
    // if (empty($bookings)) {
    //     echo "No bookings found for user ID: " . $_SESSION['user_id'] . "<br>"; // Removed debugging message
    // } else {
    //     echo "Bookings found: " . count($bookings) . "<br>"; // Removed debugging message
    // }
} catch (PDOException $e) {
    $error = "Error fetching bookings: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookings - Travel Tour Booking</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .booking-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 1rem;
            padding: 1.5rem;
        }
        .booking-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #eee;
        }
        .booking-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
        }
        .status-badge {
            display: inline-block;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            color: white;
            font-weight: bold;
        }
        .status-pending {
            background: #ffa726;
        }
        .status-paid {
            background: #4CAF50;
        }
        .status-cancelled {
            background: #f44336;
        }
        .button-group {
            display: flex;
            gap: 1rem;
            margin-top: 1rem;
        }
        .button.cancel {
            background: #f44336;
        }
        .button.cancel:hover {
            background: #d32f2f;
        }
        .message {
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 4px;
            color: white;
        }
        .message.success {
            background: #4CAF50;
        }
        .message.error {
            background: #f44336;
        }
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 25px;
            border-radius: 5px;
            color: white;
            z-index: 1000;
            animation: slideIn 0.5s ease-out;
        }
        .notification.success {
            background-color: #4CAF50;
        }
        .notification.error {
            background-color: #f44336;
        }
        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
    </style>
</head>
<body>
    <?php if (isset($_GET['message'])): ?>
        <div class="notification success">
            <?php echo htmlspecialchars($_GET['message']); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['error'])): ?>
        <div class="notification error">
            <?php echo htmlspecialchars($_GET['error']); ?>
        </div>
    <?php endif; ?>

    <header>
        <nav>
            <div class="logo"><a href="index.php" style="font-weight:bold;font-size:1.3rem;">TravelTourBooking</a></div>
            <div>
                <a href="index.php">Home</a>
                <a href="tours.php">Tours</a>
                <a href="booking.php">My Bookings</a>
                <a href="logout.php">Logout</a>
            </div>
        </nav>
    </header>

    <main>
        <section class="bookings-section">
            <h2>My Bookings</h2>

            <?php if (empty($bookings)): ?>
                <div class="no-bookings" style="text-align: center; padding: 2rem;">
                    <h3>No Bookings Yet</h3>
                    <p>You haven't made any bookings yet. Start exploring our amazing tours!</p>
                    <a href="tours.php" class="button primary" style="margin-top: 1rem;">Browse Tours</a>
                </div>
            <?php else: ?>
                <?php foreach ($bookings as $booking): ?>
                    <div class="booking-card">
                        <div class="booking-header">
                            <h3>Booking #<?php echo str_pad($booking['id'], 6, '0', STR_PAD_LEFT); ?></h3>
                            <span class="status-badge status-<?php echo $booking['status']; ?>">
                                <?php echo ucfirst($booking['status']); ?>
                            </span>
                        </div>
                        
                        <div class="booking-details">
                            <div>
                                <p><strong>Tour:</strong> <?php echo htmlspecialchars($booking['tour_name']); ?></p>
                                <p><strong>Duration:</strong> <?php echo $booking['duration']; ?> days</p>
                                <p><strong>Number of People:</strong> <?php echo $booking['num_people']; ?></p>
                            </div>
                            <div>
                                <p><strong>Total Amount:</strong> $<?php echo number_format($booking['total_price'], 2); ?></p>
                                <p><strong>Booking Date:</strong> <?php echo date('F j, Y', strtotime($booking['booking_date'])); ?></p>
                                <?php if (!empty($booking['payment_date'])): ?>
                                    <p><strong>Payment Date:</strong> <?php echo date('F j, Y', strtotime($booking['payment_date'])); ?></p>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="button-group">
                            <?php if ($booking['status'] === 'pending'): ?>
                                <a href="payment.php?booking_id=<?php echo $booking['id']; ?>" class="button">Proceed to Payment</a>
                                <a href="cancel_booking.php?booking_id=<?php echo $booking['id']; ?>" class="button cancel" onclick="return confirm('Are you sure you want to cancel this booking?')">Cancel Booking</a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </section>
    </main>

    <footer>
        &copy; <?php echo date('Y'); ?> TravelTourBooking. All rights reserved.
    </footer>
</body>
</html>
