<?php
require_once '../config.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Travel Tour Booking</title>
    <link rel="stylesheet" href="../assets/css/style.css">
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
            <div class="logo"><a href="../index.php" style="font-weight:bold;font-size:1.3rem;">TravelTourBooking</a></div>
            <div>
                <a href="dashboard.php">Dashboard</a>
                <a href="manage_tours.php">Manage Tours</a>
                <a href="reports.php">Reports</a>
                <a href="manage_users.php">Manage Users</a>
                <a href="../logout.php">Logout</a>
            </div>
        </nav>
    </header>

    <main>
        <section class="admin-dashboard">
            <h2>Welcome to Admin Dashboard</h2>
            <p>Use the navigation menu above to manage tours, users, and view reports.</p>
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

    // Show welcome message
    document.addEventListener('DOMContentLoaded', function() {
        showNotification('Welcome to the Admin Dashboard!', 'success');
    });
    </script>
</body>
</html> 