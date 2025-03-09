<?php
session_start();
require 'db.php';

if ($_SESSION['role'] !== 'Student') {
    header('Location: index.php');
    exit;
}

$rollno = $_SESSION['user'];
$student = $conn->query("SELECT * FROM students WHERE rollno='$rollno'")->fetch_assoc();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $conn->real_escape_string($_POST['name']);
    $address = $conn->real_escape_string($_POST['address']);

    // Check if the uploads folder exists, create if not
    $uploadsDir = __DIR__ . '/uploads/';
    if (!is_dir($uploadsDir)) {
        mkdir($uploadsDir, 0777, true);
    }

    if (!empty($_FILES['photo']['name'])) {
        $filename = basename($_FILES['photo']['name']);
        $filename = time() . '_' . preg_replace("/[^a-zA-Z0-9\._-]/", "_", $filename); // Clean filename
        $targetPath = $uploadsDir . $filename;

        if (move_uploaded_file($_FILES['photo']['tmp_name'], $targetPath)) {
            // Save the relative path (for browser access)
            $relativePath = "uploads/$filename";
            $conn->query("UPDATE students SET profile_photo='$relativePath' WHERE rollno='$rollno'");
        } else {
            echo "<script>alert('File upload failed. Please check folder permissions.');</script>";
        }
    }

    $conn->query("UPDATE students SET name='$name', address='$address' WHERE rollno='$rollno'");
    header('Location: view_profile.php');
    exit;
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
    </style>
</head>
<body>

<div class="dashboard-container">
    <!-- Sidebar -->
    <div class="sidebar">
        <h3>Student Dashboard</h3>
        <img src="<?= htmlspecialchars($student['profile_photo']) ?>" class="profile-photo">
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
            <form method="POST" enctype="multipart/form-data">
                <label>Full Name</label>
                <input type="text" name="name" value="<?= htmlspecialchars($student['name']) ?>" required>

                <label>Address</label>
                <input type="text" name="address" value="<?= htmlspecialchars($student['address']) ?>">

                <label>Profile Photo</label>
                <input type="file" name="photo">

                <?php if (!empty($student['profile_photo'])): ?>
                    <img src="<?= htmlspecialchars($student['profile_photo']) ?>" class="profile-photo-preview">
                <?php endif; ?>

                <button type="submit">Save Changes</button>
            </form>
        </div>
    </div>
</div>

</body>
</html>
