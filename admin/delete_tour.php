<?php
require_once '../config.php';
session_start();

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
    $stmt = $pdo->prepare("DELETE FROM tours WHERE id = ?");
    $stmt->execute([$tour_id]);
    header('Location: manage_tours.php');
    exit();
} catch (PDOException $e) {
    $error = "Error deleting tour: " . $e->getMessage();
}
?> 