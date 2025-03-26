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
        .dashboard-container {
            display: flex;
            height: 100vh;
            margin: 0;
        }
        .sidebar {
            width: 250px;
            background-color: #007bff;
            color: white;
            padding: 20px;
            box-sizing: border-box;
            flex-shrink: 0;
        }
        .sidebar h3 {
            margin: 0 0 15px;
        }
        .sidebar .profile-photo {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 10px;
        }
        .sidebar a {
            display: block;
            padding: 10px;
            margin: 5px 0;
            color: white;
            text-decoration: none;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 5px;
            text-align: center;
        }
        .sidebar a:hover, .sidebar .active {
            background: rgba(255, 255, 255, 0.4);
        }
        .main-content {
            flex-grow: 1;
            padding: 30px;
            box-sizing: border-box;
            overflow-y: auto;
        }
        form label {
            display: block;
            margin: 10px 0 5px;
        }
        form input, form button {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            box-sizing: border-box;
        }
        form button {
            background-color: #28a745;
            color: white;
            border: none;
            cursor: pointer;
        }
        form button:hover {
            background-color: #218838;
        }
        .profile-form {
            max-width: 500px;
            margin: 0 auto;
        }
        .profile-photo-preview {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 50%;
            margin-bottom: 15px;
        }
        .error-message {
            color: red;
            margin: 10px 0;
            padding: 10px;
            border: 1px solid red;
            background-color: #ffeeee;
        }
        .success-message {
            color: green;
            margin: 10px 0;
            padding: 10px;
            border: 1px solid green;
            background-color: #eeffee;
        }
    </style>
</head>
<body>

<div class="dashboard-container">
    <!-- Sidebar -->
    <div class="sidebar">
        <h3>Student Dashboard</h3>
        <img src="<?= htmlspecialchars($student['profile_photo'] ?? 'uploads/default.png') ?>" class="profile-photo">
        <p>Roll No: <?= htmlspecialchars($rollno) ?></p>
        <a href="student_dashboard.php">Home</a>
        <a href="view_profile.php" class="active">View Profile</a>
        <a href="mark_attendance.php">Mark Attendance</a>
        <a href="view_attendance.php">View Attendance</a>
        <a href="submit_leave.php">Submit Leave</a>
        <a href="logout.php">Logout</a>
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