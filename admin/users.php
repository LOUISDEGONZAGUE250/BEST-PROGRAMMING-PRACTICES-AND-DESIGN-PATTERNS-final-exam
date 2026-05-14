<?php
require_once '../config.php';
require_once '../auth_check.php';

// Check if user is admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../index.php');
    exit();
}

// Handle user actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $user_id = $_POST['user_id'] ?? null;
        
        if ($user_id) {
            try {
                switch ($_POST['action']) {
                    case 'delete':
                        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND role != 'admin'");
                        $stmt->execute([$user_id]);
                        $message = "User deleted successfully";
                        break;
                    
                    case 'update_role':
                        $new_role = $_POST['role'] ?? 'user';
                        $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE id = ? AND role != 'admin'");
                        $stmt->execute([$new_role, $user_id]);
                        $message = "User role updated successfully";
                        break;
                }
            } catch (PDOException $e) {
                $error = "Error: " . $e->getMessage();
            }
        }
    }
}

// Get filter parameters
$search = $_GET['search'] ?? '';
$role_filter = $_GET['role'] ?? '';
$sort_by = $_GET['sort'] ?? 'id';
$sort_order = $_GET['order'] ?? 'DESC';

// Build query
$query = "
    SELECT u.*, 
           COUNT(b.id) as total_bookings,
           SUM(CASE WHEN b.status = 'paid' THEN b.total_price ELSE 0 END) as total_spent,
           MAX(b.booking_date) as last_booking
    FROM users u
    LEFT JOIN bookings b ON u.id = b.user_id
    WHERE 1=1
";

$params = [];

if ($search) {
    $query .= " AND (u.username LIKE ? OR u.email LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($role_filter) {
    $query .= " AND u.role = ?";
    $params[] = $role_filter;
}

$query .= " GROUP BY u.id";

// Add sorting
$allowed_sort_columns = ['id', 'username', 'email', 'role', 'total_bookings', 'total_spent', 'last_booking'];
$sort_by = in_array($sort_by, $allowed_sort_columns) ? $sort_by : 'id';
$sort_order = strtoupper($sort_order) === 'ASC' ? 'ASC' : 'DESC';
$query .= " ORDER BY $sort_by $sort_order";

try {
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $users = $stmt->fetchAll();

    // Get user statistics
    $stmt = $pdo->query("
        SELECT 
            COUNT(*) as total_users,
            SUM(CASE WHEN role = 'admin' THEN 1 ELSE 0 END) as admin_count
        FROM users
    ");
    $user_stats = $stmt->fetch();

} catch (PDOException $e) {
    $error = "Error fetching users: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - Admin Dashboard</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .users-table {
            width: 100%;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-top: 1rem;
        }
        .users-table table {
            width: 100%;
            border-collapse: collapse;
        }
        .users-table th, .users-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        .users-table th {
            background: #f5f5f5;
            font-weight: bold;
        }
        .users-table th a {
            color: inherit;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .role-badge {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            border-radius: 12px;
            font-size: 0.875rem;
            font-weight: bold;
        }
        .role-admin { background: #e3f2fd; color: #1565c0; }
        .role-user { background: #f5f5f5; color: #616161; }
        .action-buttons {
            display: flex;
            gap: 0.5rem;
        }
        .button.small {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }
        .button.danger {
            background: #f44336;
        }
        .button.danger:hover {
            background: #d32f2f;
        }
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            align-items: center;
            justify-content: center;
        }
        .modal-content {
            background: white;
            padding: 2rem;
            border-radius: 8px;
            max-width: 400px;
            width: 100%;
        }
        .modal-buttons {
            display: flex;
            justify-content: flex-end;
            gap: 1rem;
            margin-top: 1rem;
        }
        .filters {
            display: flex;
            gap: 1rem;
            margin-bottom: 1rem;
            flex-wrap: wrap;
        }
        .filter-group {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        .stat-card {
            background: white;
            padding: 1rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .stat-card h3 {
            margin: 0 0 0.5rem 0;
            color: #666;
            font-size: 0.875rem;
        }
        .stat-value {
            font-size: 1.5rem;
            font-weight: bold;
            color: #1976d2;
        }
        .search-box {
            flex: 1;
            min-width: 200px;
        }
        .search-box input {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <header>
        <nav>
            <div class="logo"><a href="../index.php" style="font-weight:bold;font-size:1.3rem;">TravelTourBooking</a></div>
            <div>
                <a href="dashboard.php">Dashboard</a>
                <a href="reports.php">Reports</a>
                <a href="users.php" class="active">Manage Users</a>
                <a href="../logout.php">Logout</a>
            </div>
        </nav>
    </header>

    <main>
        <section class="dashboard-section">
            <h2>Manage Users</h2>

            <?php if (isset($message)): ?>
                <div class="message success"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>

            <?php if (isset($error)): ?>
                <div class="message error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <div class="stats-grid">
                <div class="stat-card">
                    <h3>Total Users</h3>
                    <div class="stat-value"><?php echo number_format($user_stats['total_users'] ?? 0); ?></div>
                </div>
                <div class="stat-card">
                    <h3>Admin Users</h3>
                    <div class="stat-value"><?php echo number_format($user_stats['admin_count'] ?? 0); ?></div>
                </div>
            </div>

            <form method="GET" class="filters">
                <div class="search-box">
                    <input type="text" name="search" placeholder="Search users..." value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <div class="filter-group">
                    <label>Role:</label>
                    <select name="role" onchange="this.form.submit()">
                        <option value="">All Roles</option>
                        <option value="admin" <?php echo $role_filter === 'admin' ? 'selected' : ''; ?>>Admin</option>
                        <option value="user" <?php echo $role_filter === 'user' ? 'selected' : ''; ?>>User</option>
                    </select>
                </div>
            </form>

            <div class="users-table">
                <table>
                    <thead>
                        <tr>
                            <th>
                                <a href="?<?php echo http_build_query(array_merge($_GET, ['sort' => 'id', 'order' => $sort_by === 'id' && $sort_order === 'ASC' ? 'DESC' : 'ASC'])); ?>">
                                    ID <?php echo $sort_by === 'id' ? ($sort_order === 'ASC' ? '↑' : '↓') : ''; ?>
                                </a>
                            </th>
                            <th>
                                <a href="?<?php echo http_build_query(array_merge($_GET, ['sort' => 'username', 'order' => $sort_by === 'username' && $sort_order === 'ASC' ? 'DESC' : 'ASC'])); ?>">
                                    Username <?php echo $sort_by === 'username' ? ($sort_order === 'ASC' ? '↑' : '↓') : ''; ?>
                                </a>
                            </th>
                            <th>
                                <a href="?<?php echo http_build_query(array_merge($_GET, ['sort' => 'email', 'order' => $sort_by === 'email' && $sort_order === 'ASC' ? 'DESC' : 'ASC'])); ?>">
                                    Email <?php echo $sort_by === 'email' ? ($sort_order === 'ASC' ? '↑' : '↓') : ''; ?>
                                </a>
                            </th>
                            <th>Role</th>
                            <th>
                                <a href="?<?php echo http_build_query(array_merge($_GET, ['sort' => 'total_bookings', 'order' => $sort_by === 'total_bookings' && $sort_order === 'ASC' ? 'DESC' : 'ASC'])); ?>">
                                    Bookings <?php echo $sort_by === 'total_bookings' ? ($sort_order === 'ASC' ? '↑' : '↓') : ''; ?>
                                </a>
                            </th>
                            <th>
                                <a href="?<?php echo http_build_query(array_merge($_GET, ['sort' => 'total_spent', 'order' => $sort_by === 'total_spent' && $sort_order === 'ASC' ? 'DESC' : 'ASC'])); ?>">
                                    Spent <?php echo $sort_by === 'total_spent' ? ($sort_order === 'ASC' ? '↑' : '↓') : ''; ?>
                                </a>
                            </th>
                            <th>
                                <a href="?<?php echo http_build_query(array_merge($_GET, ['sort' => 'last_booking', 'order' => $sort_by === 'last_booking' && $sort_order === 'ASC' ? 'DESC' : 'ASC'])); ?>">
                                    Last Booking <?php echo $sort_by === 'last_booking' ? ($sort_order === 'ASC' ? '↑' : '↓') : ''; ?>
                                </a>
                            </th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td>#<?php echo str_pad($user['id'], 6, '0', STR_PAD_LEFT); ?></td>
                                <td><?php echo htmlspecialchars($user['username']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td>
                                    <span class="role-badge role-<?php echo $user['role']; ?>">
                                        <?php echo ucfirst($user['role']); ?>
                                    </span>
                                </td>
                                <td><?php echo number_format($user['total_bookings']); ?></td>
                                <td>$<?php echo number_format($user['total_spent'] ?? 0, 2); ?></td>
                                <td><?php echo $user['last_booking'] ? date('M j, Y', strtotime($user['last_booking'])) : 'Never'; ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <?php if ($user['role'] !== 'admin'): ?>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                <input type="hidden" name="action" value="update_role">
                                                <select name="role" onchange="this.form.submit()" class="button small">
                                                    <option value="user" <?php echo $user['role'] === 'user' ? 'selected' : ''; ?>>User</option>
                                                    <option value="admin" <?php echo $user['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                                                </select>
                                            </form>
                                            <button onclick="confirmDelete(<?php echo $user['id']; ?>)" class="button small danger">Delete</button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <h3>Confirm Delete</h3>
            <p>Are you sure you want to delete this user? This action cannot be undone.</p>
            <div class="modal-buttons">
                <button onclick="closeModal()" class="button secondary">Cancel</button>
                <form method="POST" style="display: inline;">
                    <input type="hidden" name="user_id" id="deleteUserId">
                    <input type="hidden" name="action" value="delete">
                    <button type="submit" class="button danger">Delete</button>
                </form>
            </div>
        </div>
    </div>

    <footer>
        &copy; <?php echo date('Y'); ?> TravelTourBooking. All rights reserved.
    </footer>

    <script>
    function confirmDelete(userId) {
        document.getElementById('deleteUserId').value = userId;
        document.getElementById('deleteModal').style.display = 'flex';
    }

    function closeModal() {
        document.getElementById('deleteModal').style.display = 'none';
    }

    // Close modal when clicking outside
    window.onclick = function(event) {
        const modal = document.getElementById('deleteModal');
        if (event.target === modal) {
            closeModal();
        }
    }
    </script>
</body>
</html> 