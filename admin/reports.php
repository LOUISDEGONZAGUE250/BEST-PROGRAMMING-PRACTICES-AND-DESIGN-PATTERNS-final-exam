<?php
require_once '../config.php';
require_once '../auth_check.php';

// Check if user is admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../index.php');
    exit();
}

try {
    // Get total bookings
    $stmt = $pdo->query("SELECT COUNT(*) as total_bookings FROM bookings");
    $total_bookings = $stmt->fetch()['total_bookings'];

    // Get total revenue - Sum all bookings
    $stmt = $pdo->query("
        SELECT COALESCE(SUM(total_price), 0) as total_revenue 
        FROM bookings
    ");
    $total_revenue = $stmt->fetch()['total_revenue'];

    // Get bookings by status
    $stmt = $pdo->query("
        SELECT status, COUNT(*) as count 
        FROM bookings 
        GROUP BY status
    ");
    $bookings_by_status = $stmt->fetchAll();

    // Get recent bookings
    $stmt = $pdo->query("
        SELECT b.*, t.name as tour_name, u.username
        FROM bookings b
        JOIN tours t ON b.tour_id = t.id
        JOIN users u ON b.user_id = u.id
        ORDER BY b.booking_date DESC
        LIMIT 10
    ");
    $recent_bookings = $stmt->fetchAll();

    // Get popular tours with revenue - Updated to sum all bookings
    $stmt = $pdo->query("
        SELECT 
            t.name,
            COUNT(b.id) as booking_count,
            COALESCE(SUM(b.total_price), 0) as total_revenue
        FROM tours t
        LEFT JOIN bookings b ON t.id = b.tour_id
        GROUP BY t.id
        ORDER BY booking_count DESC
        LIMIT 5
    ");
    $popular_tours = $stmt->fetchAll();

    // Get top customers - Updated to sum all bookings
    $stmt = $pdo->query("
        SELECT 
            u.username,
            COUNT(b.id) as total_bookings,
            COALESCE(SUM(b.total_price), 0) as total_spent
        FROM users u
        JOIN bookings b ON u.id = b.user_id
        GROUP BY u.id
        ORDER BY total_spent DESC
        LIMIT 5
    ");
    $top_customers = $stmt->fetchAll();

} catch (PDOException $e) {
    $error = "Error fetching reports: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - Admin Dashboard</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .stat-card h3 {
            margin: 0 0 1rem 0;
            color: #333;
        }
        .stat-value {
            font-size: 2rem;
            font-weight: bold;
            color: #1976d2;
        }
        .recent-bookings, .popular-tours, .top-customers {
            background: white;
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 1.5rem;
        }
        .recent-bookings table {
            width: 100%;
            border-collapse: collapse;
        }
        .recent-bookings th, .recent-bookings td {
            padding: 0.75rem;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        .recent-bookings th {
            background: #f5f5f5;
            font-weight: bold;
        }
        .status-badge {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            border-radius: 12px;
            font-size: 0.875rem;
            font-weight: bold;
        }
        .status-pending { background: #fff3e0; color: #e65100; }
        .status-paid { background: #e8f5e9; color: #2e7d32; }
        .status-cancelled { background: #ffebee; color: #c62828; }
        .tour-item, .customer-item {
            display: flex;
            justify-content: space-between;
            padding: 0.75rem 0;
            border-bottom: 1px solid #eee;
        }
        .tour-item:last-child, .customer-item:last-child {
            border-bottom: none;
        }
        .print-button {
            background: #4caf50;
            color: white;
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin-bottom: 1rem;
        }
        .print-button:hover {
            background: #388e3c;
        }
        @media print {
            nav, .print-button {
                display: none;
            }
            .dashboard-section {
                margin: 0;
                padding: 0;
            }
            table {
                page-break-inside: auto;
            }
            tr {
                page-break-inside: avoid;
                page-break-after: auto;
            }
        }
    </style>
</head>
<body>
    <header>
        <nav>
            <div class="logo"><a href="../index.php" style="font-weight:bold;font-size:1.3rem;">TravelTourBooking</a></div>
            <div>
                <a href="dashboard.php">Dashboard</a>
                <a href="reports.php" class="active">Reports</a>
                <a href="users.php">Manage Users</a>
                <a href="../logout.php">Logout</a>
            </div>
        </nav>
    </header>

    <main>
        <section class="dashboard-section">
            <h2>Reports</h2>

            <?php if (isset($error)): ?>
                <div class="message error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <button class="print-button" onclick="window.print()">Print Report</button>

            <div class="dashboard-grid">
                <div class="stat-card">
                    <h3>Total Bookings</h3>
                    <div class="stat-value"><?php echo number_format($total_bookings); ?></div>
                </div>
                <div class="stat-card">
                    <h3>Total Revenue</h3>
                    <div class="stat-value">$<?php echo number_format($total_revenue, 2); ?></div>
                </div>
                <div class="stat-card">
                    <h3>Bookings by Status</h3>
                    <?php foreach ($bookings_by_status as $status): ?>
                        <div class="tour-item">
                            <span><?php echo ucfirst($status['status'] ?? 'Paid'); ?></span>
                            <span><?php echo number_format($status['count']); ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="recent-bookings">
                <h3>Recent Bookings</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Booking ID</th>
                            <th>Tour</th>
                            <th>Customer</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_bookings as $booking): ?>
                            <tr>
                                <td>#<?php echo str_pad($booking['id'], 6, '0', STR_PAD_LEFT); ?></td>
                                <td><?php echo htmlspecialchars($booking['tour_name']); ?></td>
                                <td><?php echo htmlspecialchars($booking['username']); ?></td>
                                <td>$<?php echo number_format($booking['total_price'], 2); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo $booking['status'] ?? 'paid'; ?>">
                                        <?php echo ucfirst($booking['status'] ?? 'Paid'); ?>
                                    </span>
                                </td>
                                <td><?php echo date('M j, Y', strtotime($booking['booking_date'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="popular-tours">
                <h3>Popular Tours</h3>
                <?php foreach ($popular_tours as $tour): ?>
                    <div class="tour-item">
                        <span><?php echo htmlspecialchars($tour['name']); ?></span>
                        <span>
                            <?php echo number_format($tour['booking_count']); ?> bookings
                            ($<?php echo number_format($tour['total_revenue'], 2); ?>)
                        </span>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="top-customers">
                <h3>Top Customers</h3>
                <?php foreach ($top_customers as $customer): ?>
                    <div class="customer-item">
                        <span><?php echo htmlspecialchars($customer['username']); ?></span>
                        <span>
                            <?php echo number_format($customer['total_bookings']); ?> bookings
                            ($<?php echo number_format($customer['total_spent'], 2); ?>)
                        </span>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    </main>

    <footer>
        © <?php echo date('Y'); ?> TravelTourBooking. All rights reserved.
    </footer>
</body>
</html>