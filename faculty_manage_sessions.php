<?php
// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require 'db.php';

// Check if user is logged in and is faculty
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Faculty') {
    header('Location: index.php');
    exit;
}

// Check database connection
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Auto-expire logic for sessions
$conn->query("UPDATE class_sessions SET status='inactive' WHERE expires_at <= NOW() AND status='active'");

$rollno = $_SESSION['user'];
$faculty_stmt = $conn->prepare("SELECT name, profile_photo FROM faculty WHERE rollno=?");
if (!$faculty_stmt) {
    die("Error preparing faculty query: " . $conn->error);
}
$faculty_stmt->bind_param("s", $rollno);
$faculty_stmt->execute();
$faculty_result = $faculty_stmt->get_result();
$faculty = ($faculty_result->num_rows > 0) ? $faculty_result->fetch_assoc() : ['name' => 'Unknown', 'profile_photo' => 'uploads/default.png'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['class_name'], $_POST['code'], $_POST['expires_at'])) {
    $class_name = trim($_POST['class_name']);
    $code = trim($_POST['code']);
    $expires_at = trim($_POST['expires_at']);

    // Validate inputs
    if (empty($class_name) || empty($code) || empty($expires_at)) {
        $error = "All fields are required!";
    } else {
        // Get class location
        $class_stmt = $conn->prepare("SELECT latitude, longitude FROM classes WHERE class_name=?");
        if (!$class_stmt) {
            die("Error preparing class query: " . $conn->error);
        }
        $class_stmt->bind_param("s", $class_name);
        $class_stmt->execute();
        $class_result = $class_stmt->get_result();

        if ($class_result->num_rows > 0) {
            $class = $class_result->fetch_assoc();

            // Insert new session
            $insert_stmt = $conn->prepare("INSERT INTO class_sessions 
                (class_name, code, faculty_name, latitude, longitude, status, expires_at, created_at) 
                VALUES (?, ?, ?, ?, ?, 'active', ?, NOW())");
            if (!$insert_stmt) {
                die("Error preparing insert query: " . $conn->error);
            }
            $insert_stmt->bind_param(
                "sssdds",
                $class_name,
                $code,
                $faculty['name'],
                $class['latitude'],
                $class['longitude'],
                $expires_at
            );

            if ($insert_stmt->execute()) {
                $success = "Class session created successfully!";
            } else {
                $error = "Error creating session: " . $conn->error;
            }
        } else {
            $error = "Invalid class selected!";
        }
    }
}

// Get all classes for dropdown
$classes = $conn->query("SELECT class_name FROM classes");
if (!$classes) {
    die("Error fetching classes: " . $conn->error);
}

// Filter handling
$filter_class = isset($_GET['filter_class']) ? $conn->real_escape_string($_GET['filter_class']) : '';
$filter_status = isset($_GET['filter_status']) ? $conn->real_escape_string($_GET['filter_status']) : '';

// Build query with filters
$query = "SELECT * FROM class_sessions WHERE faculty_name = ?";
$params = [$faculty['name']];
$types = "s";

if (!empty($filter_class)) {
    $query .= " AND class_name = ?";
    $params[] = $filter_class;
    $types .= "s";
}

if (!empty($filter_status)) {
    $query .= " AND status = ?";
    $params[] = $filter_status;
    $types .= "s";
}

$query .= " ORDER BY created_at DESC";

// Execute filtered query
$stmt = $conn->prepare($query);
if (!$stmt) {
    die("Error preparing session query: " . $conn->error);
}
$stmt->bind_param($types, ...$params);
$stmt->execute();
$sessions = $stmt->get_result();
if (!$sessions) {
    die("Error executing session query: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faculty Dashboard - Manage Sessions</title>
    <style>
        :root {
            --primary: #2c3e50;
            --secondary: #3498db;
            --light: #ecf0f1;
            --dark: #34495e;
            --success: #2ecc71;
            --danger: #e74c3c;
        }
        
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: #f5f7fa;
            color: #333;
            line-height: 1.6;
        }
        
        .dashboard-container {
            display: flex;
            min-height: 100vh;
        }
        
        .sidebar {
            width: 250px;
            background-color: var(--primary);
            color: white;
            padding: 20px 0;
            position: fixed;
            height: 100%;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
        }
        
        .sidebar-header {
            padding: 0 20px 20px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .sidebar-profile {
            display: flex;
            align-items: center;
            padding: 15px 20px;
            margin-bottom: 20px;
        }
        
        .profile-photo {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 15px;
            border: 2px solid var(--secondary);
        }
        
        .sidebar-menu {
            list-style: none;
        }
        
        .sidebar-menu a {
            display: block;
            color: white;
            text-decoration: none;
            padding: 12px 20px;
            transition: all 0.3s;
        }
        
        .sidebar-menu a:hover, .sidebar-menu a.active {
            background-color: rgba(255,255,255,0.1);
            border-left: 3px solid var(--secondary);
        }
        
        .main-content {
            margin-left: 250px;
            padding: 30px;
            width: calc(100% - 250px);
        }
        
        .card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            padding: 25px;
            margin-bottom: 30px;
        }
        
        h1, h2, h3 {
            color: var(--primary);
            margin-bottom: 20px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--dark);
        }
        
        select, input {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
            transition: border 0.3s;
        }
        
        select:focus, input:focus {
            outline: none;
            border-color: var(--secondary);
        }
        
        button, .btn {
            background-color: var(--secondary);
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            transition: background 0.3s;
        }
        
        button:hover, .btn:hover {
            background-color: #2980b9;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        
        th {
            background-color: var(--primary);
            color: white;
        }
        
        tr:hover {
            background-color: #f9f9f9;
        }
        
        .status-active {
            color: var(--success);
            font-weight: 600;
        }
        
        .status-inactive {
            color: var(--danger);
            font-weight: 600;
        }
        
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .filters {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        
        .filter-group {
            flex: 1;
            min-width: 200px;
        }
        
        @media (max-width: 768px) {
            .dashboard-container {
                flex-direction: column;
            }
            
            .sidebar {
                width: 100%;
                position: relative;
                height: auto;
            }
            
            .main-content {
                margin-left: 0;
                width: 100%;
            }
            
            .filters {
                flex-direction: column;
                gap: 10px;
            }
        }
    </style>
</head>
<body>
<div class="dashboard-container">
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <h3>Faculty Dashboard</h3>
        </div>
        
        <div class="sidebar-profile">
            <img src="<?= htmlspecialchars($faculty['profile_photo']) ?>" class="profile-photo" alt="Profile">
            <div>
                <h4><?= htmlspecialchars($faculty['name']) ?></h4>
                <small>Faculty</small>
            </div>
        </div>
        
        <ul class="sidebar-menu">
            <li><a href="faculty_dashboard.php">Dashboard</a></li>
            <li><a href="faculty_manage_sessions.php" class="active">Manage Sessions</a></li>
            <li><a href="faculty_leave_review.php">Review Leave Applications</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <h1>Manage Class Sessions</h1>
        
        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <div class="card">
            <h2>Create New Session</h2>
            <form method="POST">
                <div class="form-group">
                    <label for="class_name">Class Name</label>
                    <select id="class_name" name="class_name" required>
                        <option value="">Select Class</option>
                        <?php while ($row = $classes->fetch_assoc()): ?>
                            <option value="<?= htmlspecialchars($row['class_name']) ?>">
                                <?= htmlspecialchars($row['class_name']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="code">Class Code</label>
                    <input type="text" id="code" name="code" required 
                           placeholder="Enter unique code for students to join">
                </div>
                
                <div class="form-group">
                    <label for="expires_at">Session Expiry Date & Time</label>
                    <input type="datetime-local" id="expires_at" name="expires_at" required>
                </div>
                
                <button type="submit">Create Session</button>
            </form>
        </div>

        <div class="card">
            <h2>Existing Sessions</h2>
            
            <div class="filters">
                <form method="GET">
                    <div class="filter-group">
                        <label for="filter_class">Filter by Class</label>
                        <select id="filter_class" name="filter_class">
                            <option value="">All Classes</option>
                            <?php
                            $class_list = $conn->query("SELECT DISTINCT class_name FROM class_sessions WHERE faculty_name='".$conn->real_escape_string($faculty['name'])."'");
                            while ($row = $class_list->fetch_assoc()):
                                $selected = ($row['class_name'] === $filter_class) ? 'selected' : '';
                            ?>
                                <option value="<?= htmlspecialchars($row['class_name']) ?>" <?= $selected ?>>
                                    <?= htmlspecialchars($row['class_name']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label for="filter_status">Filter by Status</label>
                        <select id="filter_status" name="filter_status">
                            <option value="">All Statuses</option>
                            <option value="active" <?= ($filter_status === 'active') ? 'selected' : '' ?>>Active</option>
                            <option value="inactive" <?= ($filter_status === 'inactive') ? 'selected' : '' ?>>Inactive</option>
                        </select>
                    </div>
                    
                    <div class="filter-group" style="align-self: flex-end;">
                        <button type="submit">Apply Filters</button>
                        <?php if ($filter_class || $filter_status): ?>
                            <a href="faculty_manage_sessions.php" style="margin-left: 10px; color: var(--secondary);">Clear Filters</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
            
            <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr>
                            <th>Class Name</th>
                            <th>Class Code</th>
                            <th>Status</th>
                            <th>Created At</th>
                            <th>Expires At</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($sessions->num_rows > 0): ?>
                            <?php while ($row = $sessions->fetch_assoc()): ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['class_name']) ?></td>
                                    <td><?= htmlspecialchars($row['code']) ?></td>
                                    <td>
                                        <span class="status-<?= htmlspecialchars($row['status']) ?>">
                                            <?= ucfirst(htmlspecialchars($row['status'])) ?>
                                        </span>
                                    </td>
                                    <td><?= htmlspecialchars($row['created_at']) ?></td>
                                    <td><?= htmlspecialchars($row['expires_at']) ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" style="text-align: center;">No sessions found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</body>
</html>