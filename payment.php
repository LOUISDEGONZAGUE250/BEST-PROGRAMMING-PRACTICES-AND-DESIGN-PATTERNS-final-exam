<?php
require_once 'config.php';
require_once 'auth_check.php';

$booking_id = $_GET['booking_id'] ?? null;

if (!$booking_id) {
    header('Location: booking.php');
    exit();
}

try {
    // Get booking details
    $stmt = $pdo->prepare("
        SELECT b.*, t.name as tour_name, t.price as tour_price, t.duration
        FROM bookings b
        JOIN tours t ON b.tour_id = t.id
        WHERE b.id = ? AND b.user_id = ? AND b.status = 'pending'
    ");
    $stmt->execute([$booking_id, $_SESSION['user_id']]);
    $booking = $stmt->fetch();

    if (!$booking) {
        header('Location: booking.php?error=Invalid booking');
        exit();
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Basic server-side validation for card number (exactly 16 digits)
        $raw_card = $_POST['card_number'] ?? '';
        $digits_only = preg_replace('/\D/', '', $raw_card);
        if (strlen($digits_only) !== 16) {
            $error = 'Please enter a valid 16-digit card number.';
        }

        // Process payment (simplified). Only proceed if no validation errors
        $payment_success = empty($error);

        if ($payment_success) {
            // Generate receipt number
            $receipt_number = 'RCPT-' . date('Ymd') . '-' . str_pad($booking_id, 4, '0', STR_PAD_LEFT);

            // Update booking status
            $stmt = $pdo->prepare("
                UPDATE bookings 
                SET status = 'paid', 
                    payment_date = NOW(),
                    receipt_number = ?
                WHERE id = ?
            ");
            $stmt->execute([$receipt_number, $booking_id]);

            // Show receipt page
            ?>
            <!DOCTYPE html>
            <html lang="en">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Receipt - Travel Tour Booking</title>
                <style>
                    body {
                        font-family: Arial, sans-serif;
                        line-height: 1.6;
                        margin: 0;
                        padding: 20px;
                    }
                    .receipt {
                        max-width: 800px;
                        margin: 0 auto;
                        padding: 20px;
                        border: 1px solid #ddd;
                        border-radius: 8px;
                    }
                    .receipt-header {
                        text-align: center;
                        margin-bottom: 30px;
                        padding-bottom: 20px;
                        border-bottom: 2px solid #eee;
                    }
                    .receipt-section {
                        margin-bottom: 20px;
                    }
                    .receipt-section h3 {
                        color: #333;
                        margin-bottom: 10px;
                    }
                    .receipt-info {
                        display: grid;
                        grid-template-columns: 150px 1fr;
                        gap: 10px;
                    }
                    .receipt-info p {
                        margin: 5px 0;
                    }
                    .receipt-footer {
                        margin-top: 30px;
                        text-align: center;
                        padding-top: 20px;
                        border-top: 2px solid #eee;
                        font-style: italic;
                        color: #666;
                    }
                    .button-group {
                        text-align: center;
                        margin-top: 20px;
                    }
                    .button {
                        display: inline-block;
                        padding: 10px 20px;
                        background: #4CAF50;
                        color: white;
                        text-decoration: none;
                        border-radius: 4px;
                        margin: 0 10px;
                    }
                    .button.secondary {
                        background: #666;
                    }
                    @media print {
                        .button-group {
                            display: none;
                        }
                    }
                </style>
            </head>
            <body>
                <div class="receipt">
                    <div class="receipt-header">
                        <h1>Booking Receipt</h1>
                        <p>Receipt Number: <?php echo $receipt_number; ?></p>
                        <p>Date: <?php echo date('F j, Y'); ?></p>
                    </div>

                    <div class="receipt-section">
                        <h3>Customer Information</h3>
                        <div class="receipt-info">
                            <p><strong>Name:</strong></p>
                            <p><?php echo htmlspecialchars($_SESSION['username']); ?></p>
                            <p><strong>Email:</strong></p>
                            <p><?php echo isset($_SESSION['email']) ? htmlspecialchars($_SESSION['email']) : 'Not provided'; ?></p>
                        </div>
                    </div>

                    <div class="receipt-section">
                        <h3>Tour Information</h3>
                        <div class="receipt-info">
                            <p><strong>Tour:</strong></p>
                            <p><?php echo htmlspecialchars($booking['tour_name']); ?></p>
                            <p><strong>Duration:</strong></p>
                            <p><?php echo $booking['duration']; ?> days</p>
                            <p><strong>Number of People:</strong></p>
                            <p><?php echo $booking['num_people']; ?></p>
                        </div>
                    </div>

                    <div class="receipt-section">
                        <h3>Payment Information</h3>
                        <div class="receipt-info">
                            <p><strong>Total Amount:</strong></p>
                            <p>$<?php echo number_format($booking['total_price'], 2); ?></p>
                            <p><strong>Status:</strong></p>
                            <p>Paid</p>
                            <p><strong>Payment Date:</strong></p>
                            <p><?php echo date('F j, Y H:i:s'); ?></p>
                        </div>
                    </div>

                    <div class="receipt-footer">
                        <p>Thank you for choosing TravelTourBooking!</p>
                    </div>

                    <div class="button-group">
                        <button onclick="window.print()" class="button">Print Receipt</button>
                        <a href="booking.php" class="button secondary">Back to Bookings</a>
                    </div>
                </div>
            </body>
            </html>
            <?php
            exit();
        } else {
            $error = "Payment failed. Please try again.";
        }
    }
} catch (PDOException $e) {
    $error = "Error processing payment: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment - Travel Tour Booking</title>
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
        .payment-form {
            max-width: 600px;
            margin: 2rem auto;
            padding: 2rem;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .form-group {
            margin-bottom: 1rem;
        }
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: bold;
        }
        .form-group input {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .booking-summary {
            margin-bottom: 2rem;
            padding: 1rem;
            background: #f9f9f9;
            border-radius: 4px;
        }
        
        /* Disable browser validation styling */
        input:invalid {
            box-shadow: none;
        }
        
        input:invalid:focus {
            box-shadow: 0 0 0 2px #4CAF50;
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
                <a href="booking.php">My Bookings</a>
                <a href="logout.php">Logout</a>
            </div>
        </nav>
    </header>

    <main>
        <section class="payment-section">
            <h2>Payment</h2>
            
            <?php if (isset($error)): ?>
                <div class="message error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <div class="payment-form">
                <div class="booking-summary">
                    <h3>Booking Summary</h3>
                    <p><strong>Tour:</strong> <?php echo htmlspecialchars($booking['tour_name']); ?></p>
                    <p><strong>Duration:</strong> <?php echo $booking['duration']; ?> days</p>
                    <p><strong>Number of People:</strong> <?php echo $booking['num_people']; ?></p>
                    <p><strong>Total Amount:</strong> $<?php echo number_format($booking['total_price'], 2); ?></p>
                </div>

                <form method="POST" action="">
                    <div class="form-group">
                        <label for="card_number">Card Number</label>
                        <input type="text" id="card_number" name="card_number" placeholder="1234 5678 9012 3456" required>
                    </div>

                    <div class="form-group">
                        <label for="expiry">Expiry Date</label>
                        <input type="text" id="expiry" name="expiry" placeholder="MM/YY" required>
                    </div>

                    <div class="form-group">
                        <label for="cvv">CVV</label>
                        <input type="text" id="cvv" name="cvv" placeholder="123" required>
                    </div>

                    <div class="form-group">
                        <label for="name">Name on Card</label>
                        <input type="text" id="name" name="name" required>
                    </div>

                    <button type="submit" class="button primary">Pay Now</button>
                </form>
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

    // Format card number input and enforce exactly 16 digits
    const cardInput = document.getElementById('card_number');
    cardInput.addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '').slice(0,16);
        value = value.replace(/(\d{4})(?=\d)/g, '$1 ').trim();
        e.target.value = value;
    });

    // Prevent submit unless exactly 16 digits and valid expiry
    const paymentForm = document.querySelector('.payment-form form');
    paymentForm.addEventListener('submit', function(e) {
        const digits = cardInput.value.replace(/\D/g, '');
        const expiry = document.getElementById('expiry').value;
        const cvv = document.getElementById('cvv').value;
        
        let isValid = true;
        
        if (digits.length !== 16) {
            e.preventDefault();
            alert('Please enter a valid 16-digit card number.');
            cardInput.focus();
            isValid = false;
        }
        
        if (!/^\d{2}\/\d{2}$/.test(expiry)) {
            e.preventDefault();
            alert('Please enter expiry date in MM/YY format.');
            document.getElementById('expiry').focus();
            isValid = false;
        }
        
        if (!/^\d{3,4}$/.test(cvv)) {
            e.preventDefault();
            alert('Please enter a valid 3-4 digit CVV.');
            document.getElementById('cvv').focus();
            isValid = false;
        }
    });

    // Format expiry date input
    document.getElementById('expiry').addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        if (value.length >= 2) {
            value = value.slice(0,2) + '/' + value.slice(2,4);
        }
        e.target.value = value;
        
        // Clear any validation messages
        e.target.setCustomValidity('');
    });

    // Format CVV input
    document.getElementById('cvv').addEventListener('input', function(e) {
        e.target.value = e.target.value.replace(/\D/g, '').slice(0,3);
    });

    // Remove the conflicting fetch submission - form will submit normally via POST
    </script>
</body>
</html>
