<?php
require_once 'config.php';
require_once 'auth_check.php';

$booking_id = $_GET['booking_id'] ?? null;

if (!$booking_id) {
    header('Location: booking.php');
    exit();
}

try {
    // Check if booking exists and belongs to user
    $stmt = $pdo->prepare("SELECT * FROM bookings WHERE id = ? AND user_id = ? AND status = 'pending'");
    $stmt->execute([$booking_id, $_SESSION['user_id']]);
    $booking = $stmt->fetch();

    if (!$booking) {
        header('Location: booking.php');
        exit();
    }

    // Update booking status to cancelled
    $stmt = $pdo->prepare("UPDATE bookings SET status = 'cancelled' WHERE id = ?");
    $stmt->execute([$booking_id]);

    // Redirect to bookings page with success message
    header('Location: booking.php?message=Booking cancelled successfully');
    exit();
} catch (PDOException $e) {
    header('Location: booking.php?error=Error cancelling booking');
    exit();
} 