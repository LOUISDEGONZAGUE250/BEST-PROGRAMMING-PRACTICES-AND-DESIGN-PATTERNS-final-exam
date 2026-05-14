<?php
require_once 'config.php';
require_once 'auth_check.php';

$tour_id = $_GET['tour_id'] ?? null;
$num_people = $_POST['num_people'] ?? null;

if (!$tour_id) {
    header('Location: tours.php');
    exit();
}

try {
    // Get tour details
    $stmt = $pdo->prepare("SELECT * FROM tours WHERE id = ?");
    $stmt->execute([$tour_id]);
    $tour = $stmt->fetch();

    if (!$tour) {
        header('Location: tours.php');
        exit();
    }

    // If form is submitted
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $num_people) {
        // Validate number of people
        if ($num_people < 1 || $num_people > 10) {
            $error = "Number of people must be between 1 and 10";
        } else {
            // Calculate total price
            $total_price = $tour['price'] * $num_people;

            // Create booking with pending status
            $stmt = $pdo->prepare("
                INSERT INTO bookings (user_id, tour_id, num_people, total_price, status, booking_date)
                VALUES (?, ?, ?, ?, 'pending', NOW())
            ");
            $stmt->execute([$_SESSION['user_id'], $tour_id, $num_people, $total_price]);
            
            // Get the new booking ID
            $booking_id = $pdo->lastInsertId();
            
            // Redirect to booking confirmation page
            header("Location: booking_confirmation.php?booking_id=" . $booking_id);
            exit();
        }
    }
} catch (PDOException $e) {
    $error = "Error: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Tour - Travel Tour Booking</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .booking-form {
            max-width: 500px;
            margin: 2rem auto;
            padding: 2rem;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .tour-summary {
            margin-bottom: 2rem;
            padding: 1rem;
            background: #f5f5f5;
            border-radius: 4px;
        }
        .error-message {
            color: #f44336;
            margin-bottom: 1rem;
            padding: 0.5rem;
            background: #ffebee;
            border-radius: 4px;
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
        <section class="booking-section">
            <h2>Book Tour</h2>
            
            <div class="booking-form">
                <div class="tour-summary">
                    <h3>Tour Summary</h3>
                    <p><strong>Tour:</strong> <?php echo htmlspecialchars($tour['name']); ?></p>
                    <p><strong>Duration:</strong> <?php echo $tour['duration']; ?> days</p>
                    <p><strong>Price per person:</strong> $<?php echo number_format($tour['price'], 2); ?></p>
                </div>

                <?php if (isset($error)): ?>
                    <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="form-group">
                        <label for="num_people">Number of People</label>
                        <input type="number" id="num_people" name="num_people" min="1" max="10" value="<?php echo $num_people ?? 1; ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Total Price</label>
                        <p id="total_price">$<?php echo number_format($tour['price'] * ($num_people ?? 1), 2); ?></p>
                    </div>
                    <div class="button-group">
                        <button type="submit" class="button">Confirm Booking</button>
                        <a href="tours.php" class="button cancel">Cancel</a>
                    </div>
                </form>
            </div>
        </section>
    </main>

    <footer>
        &copy; <?php echo date('Y'); ?> TravelTourBooking. All rights reserved.
    </footer>

    <script>
    document.getElementById('num_people').addEventListener('input', function(e) {
        const price = <?php echo $tour['price']; ?>;
        const numPeople = parseInt(e.target.value) || 0;
        const total = price * numPeople;
        document.getElementById('total_price').textContent = '$' + total.toFixed(2);
    });
    </script>
</body>
</html> 