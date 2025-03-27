<?php
// Enable maximum error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// Verify session and role
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Student') {
    header('Location: index.php');
    exit;
}

require 'db.php';
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

$rollno = $_SESSION['user'];
$student = $conn->query("SELECT * FROM students WHERE rollno='$rollno'")->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        // Validate inputs
        if (empty($_POST['name'])) {
            throw new Exception("Name is required");
        }

        $name = $conn->real_escape_string($_POST['name']);
        $address = $conn->real_escape_string($_POST['address']);
        $relativePath = $student['profile_photo'] ?? 'uploads/default.png';

        // Handle file upload only if a file was actually uploaded
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $uploadsDir = __DIR__ . '/uploads/';
            
            // Create directory if it doesn't exist
            if (!is_dir($uploadsDir)) {
                if (!mkdir($uploadsDir, 0755, true)) {
                    throw new Exception("Could not create upload directory");
                }
            }

            // Verify directory is writable
            if (!is_writable($uploadsDir)) {
                throw new Exception("Upload directory is not writable");
            }

            // Validate file type
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            $fileInfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($fileInfo, $_FILES['photo']['tmp_name']);
            finfo_close($fileInfo);

            if (!in_array($mime, $allowedTypes)) {
                throw new Exception("Invalid file type. Only JPG, PNG, and GIF are allowed.");
            }

            // Validate file size
            if ($_FILES['photo']['size'] > 2000000) { // 2MB
                throw new Exception("File too large. Maximum size is 2MB.");
            }

            // Generate unique filename
            $extension = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
            $filename = time() . '_' . uniqid() . '.' . $extension;
            $targetPath = $uploadsDir . $filename;

            // Move uploaded file
            if (move_uploaded_file($_FILES['photo']['tmp_name'], $targetPath)) {
                $relativePath = "uploads/" . $filename;
                
                // Delete old file if it exists and it's not the default
                if (!empty($student['profile_photo']) && 
                    $student['profile_photo'] !== 'uploads/default.png' && 
                    file_exists(__DIR__ . '/' . $student['profile_photo'])) {
                    @unlink(__DIR__ . '/' . $student['profile_photo']);
                }
            } else {
                throw new Exception("Failed to move uploaded file. Check permissions.");
            }
        }

        // Use prepared statement to prevent SQL injection
        $stmt = $conn->prepare("UPDATE students SET name=?, address=?, profile_photo=? WHERE rollno=?");
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        
        $stmt->bind_param("ssss", $name, $address, $relativePath, $rollno);
        
        if (!$stmt->execute()) {
            throw new Exception("Database error: " . $stmt->error);
        }

        $_SESSION['success'] = "Profile updated successfully!";
        header('Location: view_profile.php');
        exit;

    } catch (Exception $e) {
        $_SESSION['error'] = "Error: " . $e->getMessage();
        header('Location: view_profile.php');
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>View Profile</title>
    <link rel="stylesheet" href="css/styles.css">
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
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
        }

        .sidebar-header {
            padding: 0 20px 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .sidebar-profile {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px;
            margin-bottom: 20px;
            text-align: center;
        }

        .profile-photo {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 15px;
            border: 3px solid var(--secondary);
        }

        .sidebar-menu {
            list-style: none;
            padding: 0;
        }

        .sidebar-menu a {
            display: block;
            color: white;
            text-decoration: none;
            padding: 12px 20px;
            transition: all 0.3s;
        }

        .sidebar-menu a:hover,
        .sidebar-menu a.active {
            background-color: rgba(255, 255, 255, 0.1);
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
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            padding: 25px;
            margin-bottom: 30px;
            max-width: 800px;
        }

        h1, h2, h3 {
            /* color: var(--primary); */
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

        input[type="text"],
        input[type="file"] {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
            margin-bottom: 15px;
        }

        button {
            background-color: var(--secondary);
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            transition: background 0.3s;
            margin-top: 10px;
        }

        button:hover {
            background-color: #2980b9;
        }

        .profile-photo-preview {
            width: 150px;
            height: 150px;
            border-radius: 8px;
            object-fit: cover;
            margin: 15px 0;
            border: 2px solid var(--secondary);
            display: block;
        }

        .current-photo-label {
            display: block;
            margin-top: -10px;
            margin-bottom: 15px;
            color: #666;
            font-size: 14px;
        }

        .alert {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
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
                padding: 20px;
            }
        }
    </style>
</head>
<body>

<div class="dashboard-container">
    <!-- Sidebar -->
    <div class="sidebar">
        <h3>Student profile</h3>
        <img src="<?= htmlspecialchars($student['profile_photo'] ?? 'uploads/default.png') ?>" class="profile-photo">
        <h3><?= htmlspecialchars($student['name']) ?></h3>
        <p>Roll No: <?= htmlspecialchars($rollno) ?></p>
        <ul class="sidebar-menu">
            <li><a href="student_dashboard.php">Home </a></li>
            <li><a href="mark_attendance.php">Mark Attendance</a></li>
            <li><a href="view_attendance.php">View Attendance</a></li>
            <li><a href="submit_leave.php">Submit Leave</a></li>
            <li><a href="leaveStatus.php">Leave Status</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="profile-form">
            <h2>Edit Profile</h2>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="error-message"><?= htmlspecialchars($_SESSION['error']) ?></div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['success'])): ?>
                <div class="success-message"><?= htmlspecialchars($_SESSION['success']) ?></div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>
            
            <form method="POST" enctype="multipart/form-data">
                <label>Full Name</label>
                <input type="text" name="name" value="<?= htmlspecialchars($student['name']) ?>" required>

                <label>Address</label>
                <input type="text" name="address" value="<?= htmlspecialchars($student['address']) ?>">

                <label>Profile Photo</label>
                <input type="file" name="photo" accept="image/jpeg, image/png, image/gif">

                <?php if (!empty($student['profile_photo'])): ?>
                    <img src="<?= htmlspecialchars($student['profile_photo']) ?>" class="profile-photo-preview">
                    <p>Current photo</p>
                <?php endif; ?>

                <button type="submit">Save Changes</button>
            </form>
        </div>
    </div>
</div>

</body>
</html>