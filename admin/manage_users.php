<?php
session_start();
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
    <title>Manage Users - Admin</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="admin-login-bg" style="min-height:100vh;">
        <div class="admin-dashboard-content">
            <a href="dashboard.php">&larr; Back to Dashboard</a>
            <h2>Manage Users</h2>
            <p>User management functionality coming soon.</p>
        </div>
    </div>
</body>
</html> 