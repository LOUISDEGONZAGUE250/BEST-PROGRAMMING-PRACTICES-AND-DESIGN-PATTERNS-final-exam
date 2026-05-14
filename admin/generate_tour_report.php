<?php
require_once '../config.php';
require_once '../auth_check.php';

// Check if user is admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../index.php');
    exit();
}

// Get filter parameters
$search = $_GET['search'] ?? '';
$category_filter = $_GET['category'] ?? '';
$availability_filter = $_GET['availability'] ?? '';
$sort_by = $_GET['sort'] ?? 'id';
$sort_order = $_GET['order'] ?? 'DESC';

// Build query
$query = "
    SELECT t.*, 
           COUNT(b.id) as total_bookings,
           COALESCE(SUM(CASE WHEN b.status = 'paid' OR b.status IS NULL THEN b.total_price ELSE 0 END), 0) as total_revenue
    FROM tours t
    LEFT JOIN bookings b ON t.id = b.tour_id
    WHERE 1=1
";

$params = [];

if ($search) {
    $query .= " AND (t.name LIKE ? OR t.location LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($category_filter) {
    $query .= " AND t.category = ?";
    $params[] = $category_filter;
}

if ($availability_filter !== '') {
    $query .= " AND t.availability = ?";
    $params[] = $availability_filter;
}

$query .= " GROUP BY t.id";

// Add sorting
$allowed_sort_columns = ['id', 'name', 'price', 'duration', 'total_bookings', 'total_revenue'];
$sort_by = in_array($sort_by, $allowed_sort_columns) ? $sort_by : 'id';
$sort_order = strtoupper($sort_order) === 'ASC' ? 'ASC' : 'DESC';
$query .= " ORDER BY $sort_by $sort_order";

try {
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $tours = $stmt->fetchAll();

    // Get tour statistics
    $stmt = $pdo->query("
        SELECT 
            COUNT(*) as total_tours,
            COALESCE(SUM(price), 0) as total_value,
            AVG(price) as avg_price,
            SUM(CASE WHEN availability = 1 THEN 1 ELSE 0 END) as available_tours
        FROM tours
    ");
    $tour_stats = $stmt->fetch();

} catch (PDOException $e) {
    $error = "Error fetching tours: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tour Report - TravelTourBooking</title>
    <style>
        @page {
            size: A4;
            margin: 0;
        }
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 20px;
            background: #fff;
        }
        .report-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background: #fff;
        }
        .report-header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #1E88E5;
        }
        .report-title {
            color: #1E88E5;
            font-size: 24px;
            margin: 0;
            padding: 0;
        }
        .report-subtitle {
            color: #666;
            font-size: 16px;
            margin: 10px 0;
        }
        .report-date {
            color: #666;
            font-size: 14px;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            border: 1px solid #e0e0e0;
        }
        .stat-label {
            color: #666;
            font-size: 14px;
            margin-bottom: 5px;
        }
        .stat-value {
            color: #1E88E5;
            font-size: 20px;
            font-weight: bold;
        }
        .tours-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .tours-table th {
            background: #1E88E5;
            color: white;
            padding: 12px;
            text-align: left;
            font-size: 14px;
        }
        .tours-table td {
            padding: 12px;
            border-bottom: 1px solid #e0e0e0;
            font-size: 14px;
        }
        .tours-table tr:nth-child(even) {
            background: #f8f9fa;
        }
        .price {
            color: #4CAF50;
            font-weight: bold;
        }
        .availability {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
        }
        .available {
            background: #e8f5e9;
            color: #2e7d32;
        }
        .unavailable {
            background: #ffebee;
            color: #c62828;
        }
        .report-footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e0e0e0;
            text-align: center;
            color: #666;
            font-size: 12px;
        }
        @media print {
            body {
                padding: 0;
            }
            .report-container {
                padding: 0;
            }
            .tours-table th {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .tours-table tr:nth-child(even) {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .availability {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>
<body>
    <div class="report-container">
        <div class="report-header">
            <h1 class="report-title">Tour Management Report</h1>
            <div class="report-subtitle">TravelTourBooking System</div>
            <div class="report-date">Generated on: <?php echo date('F d, Y h:i A'); ?></div>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-label">Total Tours</div>
                <div class="stat-value"><?php echo number_format($tour_stats['total_tours']); ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Available Tours</div>
                <div class="stat-value"><?php echo number_format($tour_stats['available_tours']); ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Total Value</div>
                <div class="stat-value">$<?php echo number_format($tour_stats['total_value'], 2); ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Average Price</div>
                <div class="stat-value">$<?php echo number_format($tour_stats['avg_price'], 2); ?></div>
            </div>
        </div>

        <table class="tours-table">
            <thead>
                <tr>
                    <th>Tour Name</th>
                    <th>Location</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Duration</th>
                    <th>Max Participants</th>
                    <th>Availability</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($tours as $tour): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($tour['name']); ?></td>
                        <td><?php echo htmlspecialchars($tour['location']); ?></td>
                        <td><?php echo htmlspecialchars($tour['category']); ?></td>
                        <td class="price">$<?php echo number_format($tour['price'], 2); ?></td>
                        <td><?php echo $tour['duration']; ?> days</td>
                        <td><?php echo $tour['max_participants']; ?></td>
                        <td>
                            <span class="availability <?php echo $tour['availability'] ? 'available' : 'unavailable'; ?>">
                                <?php echo $tour['availability'] ? 'Available' : 'Unavailable'; ?>
                            </span>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="report-footer">
            <p>This report was generated by <?php echo htmlspecialchars($_SESSION['username']); ?> on <?php echo date('F d, Y h:i A'); ?></p>
            <p>TravelTourBooking System - All rights reserved</p>
        </div>
    </div>

    <script>
        // Automatically trigger print when page loads
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html> 