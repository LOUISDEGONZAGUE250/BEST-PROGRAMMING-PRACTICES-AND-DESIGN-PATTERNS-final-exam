<?php
require_once '../config.php';
require_once '../auth_check.php';

// Check if user is admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../index.php');
    exit();
}

// Handle tour actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        try {
            switch ($_POST['action']) {
                case 'add':
                    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_SPECIAL_CHARS);
                    $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_SPECIAL_CHARS);
                    $price = filter_input(INPUT_POST, 'price', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
                    $duration = filter_input(INPUT_POST, 'duration', FILTER_SANITIZE_NUMBER_INT);
                    $max_participants = filter_input(INPUT_POST, 'max_participants', FILTER_SANITIZE_NUMBER_INT);
                    $location = filter_input(INPUT_POST, 'location', FILTER_SANITIZE_SPECIAL_CHARS);
                    $category = filter_input(INPUT_POST, 'category', FILTER_SANITIZE_SPECIAL_CHARS);
                    $availability = isset($_POST['availability']) ? 1 : 0;
                    
                    // Handle image upload
                    $image_path = '';
                    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                        $upload_dir = '../assets/images/tours/';
                        if (!file_exists($upload_dir)) {
                            mkdir($upload_dir, 0777, true);
                        }
                        
                        $file_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
                        if (!in_array($file_extension, ['jpg', 'jpeg', 'png'])) {
                            throw new Exception('Invalid file type. Only JPG and PNG are allowed.');
                        }
                        
                        if ($_FILES['image']['size'] > 5 * 1024 * 1024) {
                            throw new Exception('File size too large. Maximum size is 5MB.');
                        }
                        
                        $new_filename = uniqid() . '.' . $file_extension;
                        $target_path = $upload_dir . $new_filename;
                        
                        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_path)) {
                            $image_path = 'assets/images/tours/' . $new_filename;
                        }
                    }
                    
                    $stmt = $pdo->prepare("
                        INSERT INTO tours (name, description, price, duration, max_participants, location, category, image_path, availability)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                    ");
                    $stmt->execute([$name, $description, $price, $duration, $max_participants, $location, $category, $image_path, $availability]);
                    
                    $stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action, timestamp) VALUES (?, ?, NOW())");
                    $stmt->execute([$_SESSION['user_id'], "Added new tour: $name"]);
                    
                    $message = "Tour added successfully";
                    break;
                
                case 'update':
                    $tour_id = filter_input(INPUT_POST, 'tour_id', FILTER_SANITIZE_NUMBER_INT);
                    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_SPECIAL_CHARS);
                    $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_SPECIAL_CHARS);
                    $price = filter_input(INPUT_POST, 'price', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
                    $duration = filter_input(INPUT_POST, 'duration', FILTER_SANITIZE_NUMBER_INT);
                    $max_participants = filter_input(INPUT_POST, 'max_participants', FILTER_SANITIZE_NUMBER_INT);
                    $location = filter_input(INPUT_POST, 'location', FILTER_SANITIZE_SPECIAL_CHARS);
                    $category = filter_input(INPUT_POST, 'category', FILTER_SANITIZE_SPECIAL_CHARS);
                    $availability = isset($_POST['availability']) ? 1 : 0;
                    
                    $image_path = $_POST['current_image'];
                    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                        $upload_dir = '../assets/images/tours/';
                        if (!file_exists($upload_dir)) {
                            mkdir($upload_dir, 0777, true);
                        }
                        
                        $file_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
                        if (!in_array($file_extension, ['jpg', 'jpeg', 'png'])) {
                            throw new Exception('Invalid file type. Only JPG and PNG are allowed.');
                        }
                        
                        if ($_FILES['image']['size'] > 5 * 1024 * 1024) {
                            throw new Exception('File size too large. Maximum size is 5MB.');
                        }
                        
                        if ($image_path && file_exists('../' . $image_path)) {
                            unlink('../' . $image_path);
                        }
                        
                        $new_filename = uniqid() . '.' . $file_extension;
                        $target_path = $upload_dir . $new_filename;
                        
                        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_path)) {
                            $image_path = 'assets/images/tours/' . $new_filename;
                        }
                    }
                    
                    $stmt = $pdo->prepare("
                        UPDATE tours 
                        SET name = ?, description = ?, price = ?, duration = ?, 
                            max_participants = ?, location = ?, category = ?, image_path = ?, availability = ?
                        WHERE id = ?
                    ");
                    $stmt->execute([$name, $description, $price, $duration, $max_participants, $location, $category, $image_path, $availability, $tour_id]);
                    
                    $stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action, timestamp) VALUES (?, ?, NOW())");
                    $stmt->execute([$_SESSION['user_id'], "Updated tour: $name"]);
                    
                    $message = "Tour updated successfully";
                    break;
                
                case 'delete':
                    $tour_id = filter_input(INPUT_POST, 'tour_id', FILTER_SANITIZE_NUMBER_INT);
                    
                    $stmt = $pdo->prepare("SELECT name FROM tours WHERE id = ?");
                    $stmt->execute([$tour_id]);
                    $tour_name = $stmt->fetchColumn();
                    
                    $stmt = $pdo->prepare("SELECT image_path FROM tours WHERE id = ?");
                    $stmt->execute([$tour_id]);
                    $image_path = $stmt->fetchColumn();
                    
                    if ($image_path && file_exists('../' . $image_path)) {
                        unlink('../' . $image_path);
                    }
                    
                    $stmt = $pdo->prepare("DELETE FROM tours WHERE id = ?");
                    $stmt->execute([$tour_id]);
                    
                    $stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action, timestamp) VALUES (?, ?, NOW())");
                    $stmt->execute([$_SESSION['user_id'], "Deleted tour: $tour_name"]);
                    
                    $message = "Tour deleted successfully";
                    break;
            }
        } catch (Exception $e) {
            $error = "Error: " . $e->getMessage();
        }
    }
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

    // Get tour categories
    $stmt = $pdo->query("SELECT DISTINCT category FROM tours ORDER BY category");
    $categories = $stmt->fetchAll(PDO::FETCH_COLUMN);

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
    <title>Manage Tours - Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .tour-cards { display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 1.5rem; }
        .tour-card { background: #fff; border-radius: 8px; box-shadow: 0 2px 8px #eee; overflow: hidden; display: flex; flex-direction: column; }
        .tour-card img { width: 100%; height: 180px; object-fit: cover; }
        .tour-info { padding: 1rem; flex: 1; display: flex; flex-direction: column; }
        .tour-title { font-size: 1.2rem; font-weight: bold; margin-bottom: 0.5rem; }
        .tour-desc { color: #555; margin-bottom: 0.5rem; }
        .tour-details { margin-bottom: 0.5rem; }
        .tour-price { color: #4CAF50; font-weight: bold; }
        .tour-duration { margin-left: 1rem; }
        .availability-badge { padding: 2px 8px; border-radius: 4px; font-size: 0.9em; }
        .tour-actions { margin-top: auto; display: flex; gap: 0.5rem; }
        .edit-button { background: #2196F3; color: #fff; border: none; padding: 0.5rem 1rem; border-radius: 4px; cursor: pointer; }
        .delete-button { background: #f44336; color: #fff; border: none; padding: 0.5rem 1rem; border-radius: 4px; cursor: pointer; }
        .add-product-form { margin-bottom: 2rem; }
        .add-product-form form { background: #fff; padding: 1.5rem; border-radius: 8px; box-shadow: 0 2px 8px #eee; max-width: 500px; margin: auto; }
        .add-product-form label { display: block; margin-bottom: 0.25rem; }
        .add-product-form input, .add-product-form textarea { width: 100%; padding: 0.5rem; margin-bottom: 1rem; border-radius: 4px; border: 1px solid #ccc; }
        .add-product-form button { width: 100%; background: #4CAF50; color: #fff; padding: 0.75rem; border: none; border-radius: 4px; font-size: 1rem; }
        nav { display: flex; justify-content: space-between; align-items: center; background: #1E88E5; padding: 1rem 2rem; }
        nav .logo a { color: #fff; text-decoration: none; font-weight: bold; font-size: 1.3rem; }
        nav .nav-links a { color: #fff; margin-left: 1.5rem; text-decoration: none; font-weight: 500; }
        nav .nav-links a.active { text-decoration: underline; }
    </style>
</head>
<body>
    <header>
        <nav>
            <div class="logo"><a href="../index.php">TravelTourBooking</a></div>
            <div class="nav-links">
                <a href="tours.php" class="active">Manage Tours</a>
                <a href="users.php">Manage Users</a>
                <a href="reports.php">Reports</a>
                <a href="../logout.php">Logout</a>
            </div>
        </nav>
    </header>
    <main>
        <section class="tours-section">
            <h2>Manage Tours</h2>
            <!-- Add New Tour Form -->
            <div class="add-product-form">
                <form method="POST" enctype="multipart/form-data" id="addTourForm">
                    <input type="hidden" name="action" value="add">
                    <label>Tour Name:</label>
                    <input type="text" name="name" required>
                    <label>Price ($):</label>
                    <input type="number" name="price" step="0.01" required>
                    <label>Duration (days):</label>
                    <input type="number" name="duration" required>
                    <label>Max Participants:</label>
                    <input type="number" name="max_participants" required>
                    <label>Location:</label>
                    <input type="text" name="location" required>
                    <label>Category:</label>
                    <input type="text" name="category" required>
                    <label>Description:</label>
                    <textarea name="description" required></textarea>
                    <label>Available:</label>
                    <input type="checkbox" name="availability" checked>
                    <label>Tour Image:</label>
                    <input type="file" name="image" accept="image/*" required>
                    <button type="submit">Add Tour</button>
                </form>
            </div>
            <!-- Tour Cards Grid -->
            <div class="tour-cards">
                <?php foreach ($tours as $tour): ?>
                    <div class="tour-card">
                        <img src="../<?php echo htmlspecialchars($tour['image_path'] ?: 'assets/images/tour1.jpg'); ?>" alt="<?php echo htmlspecialchars($tour['name']); ?>">
                        <div class="tour-info">
                            <div class="tour-title"> <?php echo htmlspecialchars($tour['name']); ?> </div>
                            <div class="tour-desc"> <?php echo htmlspecialchars($tour['description']); ?> </div>
                            <div class="tour-details">
                                <span class="tour-price">$<?php echo number_format($tour['price'], 2); ?></span>
                                <span class="tour-duration"> <?php echo $tour['duration']; ?> days</span>
                            </div>
                            <div>
                                <span>Max: <?php echo $tour['max_participants']; ?></span> | <span><?php echo htmlspecialchars($tour['location']); ?></span> | <span><?php echo htmlspecialchars($tour['category']); ?></span>
                            </div>
                            <div>
                                <span class="availability-badge" style="<?php echo $tour['availability'] ? 'background:#e8f5e9;color:#2e7d32;' : 'background:#ffebee;color:#c62828;'; ?>">
                                    <?php echo $tour['availability'] ? 'Available' : 'Unavailable'; ?>
                                </span>
                            </div>
                            <div class="tour-actions">
                                <button onclick="openEditModal(<?php echo htmlspecialchars(json_encode($tour)); ?>)" class="edit-button">Edit</button>
                                <button onclick="confirmDelete(<?php echo $tour['id']; ?>)" class="delete-button">Delete</button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    </main>
    <footer>
        &copy; <?php echo date('Y'); ?> TravelTourBooking. All rights reserved.
    </footer>

    <!-- Edit Tour Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <h3>Edit Tour</h3>
            <form method="POST" enctype="multipart/form-data" id="editTourForm">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="tour_id" id="edit_tour_id">
                <input type="hidden" name="current_image" id="edit_current_image">
                
                <div class="form-group">
                    <label>Name</label>
                    <input type="text" name="name" id="edit_name" required>
                </div>
                
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" id="edit_description" required></textarea>
                </div>
                
                <div class="form-group">
                    <label>Price</label>
                    <input type="number" name="price" id="edit_price" step="0.01" required>
                </div>
                
                <div class="form-group">
                    <label>Duration (days)</label>
                    <input type="number" name="duration" id="edit_duration" required>
                </div>
                
                <div class="form-group">
                    <label>Max Participants</label>
                    <input type="number" name="max_participants" id="edit_max_participants" required>
                </div>
                
                <div class="form-group">
                    <label>Location</label>
                    <input type="text" name="location" id="edit_location" required>
                </div>
                
                <div class="form-group">
                    <label>Category</label>
                    <input type="text" name="category" id="edit_category" required>
                </div>
                
                <div class="form-group checkbox">
                    <input type="checkbox" name="availability" id="edit_availability">
                    <label for="edit_availability">Available</label>
                </div>
                
                <div class="form-group">
                    <label>Image</label>
                    <input type="file" name="image" accept="image/*" onchange="previewImage(this, 'editPreview')">
                    <small>Leave empty to keep current image</small>
                    <div id="editPreview" class="photo-preview"></div>
                </div>
                
                <div class="modal-buttons">
                    <button type="button" onclick="closeModal('editModal')" class="button secondary">Cancel</button>
                    <button type="submit" class="button primary">Update Tour</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <h3>Confirm Delete</h3>
            <p>Are you sure you want to delete this tour? This action cannot be undone.</p>
            <div class="modal-buttons">
                <button onclick="closeModal('deleteModal')" class="button secondary">Cancel</button>
                <form method="POST" style="display: inline;">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="tour_id" id="delete_tour_id">
                    <button type="submit" class="button danger">Delete</button>
                </form>
            </div>
        </div>
    </div>

    <script>
    function openAddModal() {
        document.getElementById('addModal').style.display = 'flex';
    }

    function openEditModal(tour) {
        document.getElementById('edit_tour_id').value = tour.id;
        document.getElementById('edit_name').value = tour.name;
        document.getElementById('edit_description').value = tour.description;
        document.getElementById('edit_price').value = tour.price;
        document.getElementById('edit_duration').value = tour.duration;
        document.getElementById('edit_max_participants').value = tour.max_participants;
        document.getElementById('edit_location').value = tour.location;
        document.getElementById('edit_category').value = tour.category;
        document.getElementById('edit_current_image').value = tour.image_path;
        document.getElementById('edit_availability').checked = tour.availability == 1;
        
        // Show current image preview
        const preview = document.getElementById('editPreview');
        preview.innerHTML = '';
        if (tour.image_path) {
            const img = document.createElement('img');
            img.src = '../' + tour.image_path;
            preview.appendChild(img);
        }
        
        document.getElementById('editModal').style.display = 'flex';
    }

    function confirmDelete(tourId) {
        document.getElementById('delete_tour_id').value = tourId;
        document.getElementById('deleteModal').style.display = 'flex';
    }

    function closeModal(modalId) {
        document.getElementById(modalId).style.display = 'none';
    }

    function previewImage(input, previewId) {
        const preview = document.getElementById(previewId);
        preview.innerHTML = '';
        
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const img = document.createElement('img');
                img.src = e.target.result;
                preview.appendChild(img);
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    // Close modal when clicking outside
    window.onclick = function(event) {
        if (event.target.classList.contains('modal')) {
            event.target.style.display = 'none';
        }
    }

    // Form validation
    document.getElementById('addTourForm').addEventListener('submit', function(e) {
        const price = this.querySelector('input[name="price"]').value;
        const duration = this.querySelector('input[name="duration"]').value;
        const maxParticipants = this.querySelector('input[name="max_participants"]').value;
        
        if (price <= 0 || duration <= 0 || maxParticipants <= 0) {
            e.preventDefault();
            alert('Price, duration, and max participants must be greater than 0');
        }
    });

    document.getElementById('editTourForm').addEventListener('submit', function(e) {
        const price = this.querySelector('input[name="price"]').value;
        const duration = this.querySelector('input[name="duration"]').value;
        const maxParticipants = this.querySelector('input[name="max_participants"]').value;
        
        if (price <= 0 || duration <= 0 || maxParticipants <= 0) {
            e.preventDefault();
            alert('Price, duration, and max participants must be greater than 0');
        }
    });
    </script>
</body>
</html> 