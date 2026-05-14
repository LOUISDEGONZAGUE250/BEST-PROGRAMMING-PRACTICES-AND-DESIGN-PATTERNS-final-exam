<?php
session_start();
require_once '../config.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username && $password) {
        $stmt = $pdo->prepare('SELECT * FROM admins WHERE username = ? LIMIT 1');
        $stmt->execute([$username]);
        $admin = $stmt->fetch();
        if ($admin && password_verify($password, $admin['password'])) {
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            header('Location: dashboard.php');
            exit;
        } else {
            $error = 'Invalid username or password.';
        }
    } else {
        $error = 'Please enter both username and password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Travel Tour Booking</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="admin-login-bg">
        <div class="admin-login-container">
            <div class="admin-login-left">
                <div class="admin-login-brand">TravelTourBooking</div>
                <div class="admin-login-desc">Plan Your Dream Journey</div>
            </div>
            <div class="admin-login-right">
                <h2>Login</h2>
                <p>Welcome back! Sign in to continue your journey.</p>
                <?php if ($error): ?>
                    <div class="admin-login-error"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                <form method="post" autocomplete="off">
                    <input type="text" name="username" placeholder="Username" required>
                    <input type="password" name="password" placeholder="Password" required>
                    <button type="submit">Sign In</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html> 