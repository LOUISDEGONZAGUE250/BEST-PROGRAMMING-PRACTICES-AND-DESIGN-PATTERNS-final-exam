<?php
require_once 'config.php';
require_once 'auth_check.php';

$booking_id = $_GET['booking_id'] ?? null;

if (!$booking_id) {
    header('Location: booking.php');
    exit();
}

try {
    $stmt = $pdo->prepare("
        SELECT b.*, t.name as tour_name, t.price as tour_price, t.duration,
               u.username, u.email
        FROM bookings b
        JOIN tours t ON b.tour_id = t.id
        JOIN users u ON b.user_id = u.id
        WHERE b.id = ? AND b.user_id = ? AND b.status = 'pending'
    ");
    $stmt->execute([$booking_id, $_SESSION['user_id']]);
    $booking = $stmt->fetch();

    if (!$booking) {
        header('Location: booking.php');
        exit();
    }
} catch (PDOException $e) {
    $error = "Error fetching booking: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Confirmation - Travel Tour Booking</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .confirmation-container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 2rem;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .booking-summary {
            margin-bottom: 2rem;
            padding: 1rem;
            background: #f5f5f5;
            border-radius: 4px;
        }
        .status-badge {
            display: inline-block;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            background: #ffa726;
            color: white;
            font-weight: bold;
        }
        .button-group {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
        }
        .button.cancel {
            background: #f44336;
        }
        .button.cancel:hover {
            background: #d32f2f;
        }
    </style>
</head>
<body>
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
        <div class="confirmation-container">
            <h2>Booking Confirmation</h2>
            
            <div class="booking-summary">
                <h3>Booking Summary</h3>
                <p><strong>Booking ID:</strong> #<?php echo str_pad($booking['id'], 6, '0', STR_PAD_LEFT); ?></p>
                <p><strong>Status:</strong> <span class="status-badge">Pending Payment</span></p>
                <p><strong>Tour:</strong> <?php echo htmlspecialchars($booking['tour_name']); ?></p>
                <p><strong>Duration:</strong> <?php echo $booking['duration']; ?> days</p>
                <p><strong>Number of People:</strong> <?php echo $booking['num_people']; ?></p>
                <p><strong>Total Amount:</strong> $<?php echo number_format($booking['total_price'], 2); ?></p>
                <p><strong>Booking Date:</strong> <?php echo date('F j, Y', strtotime($booking['booking_date'])); ?></p>
            </div>

            <div class="button-group">
                <a href="payment.php?booking_id=<?php echo $booking['id']; ?>" class="button">Proceed to Payment</a>
                <a href="cancel_booking.php?booking_id=<?php echo $booking['id']; ?>" class="button cancel" onclick="return confirm('Are you sure you want to cancel this booking?')">Cancel Booking</a>
            </div>
        </div>
    </main>

    <footer>
        &copy; <?php echo date('Y'); ?> TravelTourBooking. All rights reserved.
    </footer>
</body>
</html> 