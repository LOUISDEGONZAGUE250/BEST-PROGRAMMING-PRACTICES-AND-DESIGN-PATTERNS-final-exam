<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MFA - Admin - Travel Tour Booking</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <main>
        <section class="admin-mfa">
            <!-- MFA placeholder -->
        </section>
    </main>
</body>
</html>

<?php
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
} 