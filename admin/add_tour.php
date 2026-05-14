<?php
require_once '../config.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $price = $_POST['price'] ?? 0;
    $duration = $_POST['duration'] ?? 0;
    $description = $_POST['description'] ?? '';

    if (empty($name) || $price <= 0 || $duration <= 0) {
        $error = "All fields are required and must be valid.";
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO tours (name, price, duration, description) VALUES (?, ?, ?, ?)");
            $stmt->execute([$name, $price, $duration, $description]);
            header('Location: manage_tours.php');
            exit();
        } catch (PDOException $e) {
            $error = "Error adding tour: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Tour - Travel Tour Booking</title>
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
        <section class="add-tour">
            <h2>Add New Tour</h2>
            <?php if (isset($error)): ?>
                <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <form method="POST" id="addTourForm">
                <div class="form-group">
                    <label for="name">Tour Name</label>
                    <input type="text" id="name" name="name" required>
                </div>
                <div class="form-group">
                    <label for="price">Price</label>
                    <input type="number" id="price" name="price" step="0.01" required>
                </div>
                <div class="form-group">
                    <label for="duration">Duration (days)</label>
                    <input type="number" id="duration" name="duration" required>
                </div>
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" required></textarea>
                </div>
                <button type="submit" class="button">Add Tour</button>
            </form>
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

    document.getElementById('addTourForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        fetch('add_tour.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification(data.message, 'success');
                setTimeout(() => {
                    window.location.href = 'manage_tours.php';
                }, 1500);
            } else {
                showNotification(data.message, 'error');
            }
        })
        .catch(error => {
            showNotification('An error occurred while adding the tour.', 'error');
        });
    });
    </script>
</body>
</html> 