<?php
// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Store the current page URL in session to redirect back after login
    $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
    header('Location: login.php');
    exit();
} 