<?php
require_once 'config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tour_id = $_POST['tour_id'] ?? null;
    $booking_date = $_POST['booking_date'] ?? null;
    $num_people = $_POST['num_people'] ?? null;

    if (!$tour_id || !$booking_date || !$num_people) {
        echo json_encode(['success' => false, 'message' => 'Missing required fields']);
        exit();
    }

    try {
        // Check if tour exists and has available capacity
        $stmt = $pdo->prepare("SELECT * FROM tours WHERE id = ?");
        $stmt->execute([$tour_id]);
        $tour = $stmt->fetch();

        if (!$tour) {
            echo json_encode(['success' => false, 'message' => 'Tour not found']);
            exit();
        }

        // Check if booking date is valid (not in the past)
        if (strtotime($booking_date) < strtotime('today')) {
            echo json_encode(['success' => false, 'message' => 'Booking date cannot be in the past']);
            exit();
        }

        // Check if number of people is within capacity
        if ($num_people > $tour['max_capacity']) {
            echo json_encode(['success' => false, 'message' => 'Number of people exceeds tour capacity']);
            exit();
        }

        // Create booking
        $stmt = $pdo->prepare("INSERT INTO bookings (user_id, tour_id, booking_date, num_people, total_price, status) VALUES (?, ?, ?, ?, ?, 'pending')");
        $total_price = $tour['price'] * $num_people;
        $stmt->execute([$_SESSION['user_id'], $tour_id, $booking_date, $num_people, $total_price]);
        $booking_id = $pdo->lastInsertId();

        echo json_encode(['success' => true, 'message' => 'Booking created successfully', 'booking_id' => $booking_id]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error creating booking: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
