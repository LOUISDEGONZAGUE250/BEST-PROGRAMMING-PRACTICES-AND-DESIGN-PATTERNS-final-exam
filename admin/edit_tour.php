<?php
require_once '../config.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

$tour_id = $_GET['id'] ?? null;

if (!$tour_id) {
    header('Location: manage_tours.php');
    exit();
}

try {
    $stmt = $pdo->prepare("SELECT * FROM tours WHERE id = ?");
    $stmt->execute([$tour_id]);
    $tour = $stmt->fetch();

    if (!$tour) {
        header('Location: manage_tours.php');
        exit();
    }
} catch (PDOException $e) {
    $error = "Error fetching tour: " . $e->getMessage();
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
            $stmt = $pdo->prepare("UPDATE tours SET name = ?, price = ?, duration = ?, description = ? WHERE id = ?");
            $stmt->execute([$name, $price, $duration, $description, $tour_id]);
            header('Location: manage_tours.php');
            exit();
        } catch (PDOException $e) {
            $error = "Error updating tour: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Tour - Travel Tour Booking</title>
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
        <section class="edit-tour">
            <h2>Edit Tour</h2>
            <?php if (isset($error)): ?>
                <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <form method="POST" id="editTourForm">
                <div class="form-group">
                    <label for="name">Tour Name</label>
                    <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($tour['name']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="price">Price</label>
                    <input type="number" id="price" name="price" step="0.01" value="<?php echo $tour['price']; ?>" required>
                </div>
                <div class="form-group">
                    <label for="duration">Duration (days)</label>
                    <input type="number" id="duration" name="duration" value="<?php echo $tour['duration']; ?>" required>
                </div>
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" required><?php echo htmlspecialchars($tour['description']); ?></textarea>
                </div>
                <button type="submit" class="button">Update Tour</button>
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

    document.getElementById('editTourForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        fetch('edit_tour.php?id=<?php echo $tour_id; ?>', {
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
            showNotification('An error occurred while updating the tour.', 'error');
        });
    });
    </script>
</body>
</html> 