<?php
session_start();
if (!isset($_SESSION['user_id']) && !isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Report - Travel Tour Booking</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header>
        <nav>
            <!-- Navigation placeholder -->
        </nav>
    </header>
    <main>
        <section class="booking-report">
            <!-- Booking/payment report placeholder -->
        </section>
    </main>
    <footer>
        <!-- Footer placeholder -->
    </footer>
</body>
</html>
