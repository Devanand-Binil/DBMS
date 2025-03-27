<?php
session_start();
require 'db.php';

if ($_SESSION['role'] !== 'Student') {
    header('Location: index.php');
    exit;
}

$rollno = $_SESSION['user'];

// Fetch student details from the `students` table
$student = $conn->query("SELECT *, profile_photo as photo FROM students WHERE rollno='$rollno'")->fetch_assoc();

// Debugging - uncomment to check photo path
// echo "<pre>"; print_r($student); echo "</pre>"; exit;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Student Dashboard</title>
    <link rel="stylesheet" href="css/styles.css">
    <style>
        :root {
            --primary: #2c3e50;
            --secondary: #3498db;
            --light: #ecf0f1;
            --dark: #34495e;
            --success: #2ecc71;
            --danger: #e74c3c;
            --warning: #f39c12;
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
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .sidebar-profile {
            display: flex;
            align-items: center;
            padding: 15px 20px;
            margin-bottom: 20px;
        }

        .profile-photo {
            width: 100px0px;
            height: 100px;
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
        }

        h1, h2, h3 {
            /* color: var(--primary); */
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }


        .main-content {
            flex-grow: 1;
            padding: 20px;
            background-color: white;
            border-radius: 8px;
            margin: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        
    </style>
</head>
<body>

<div class="dashboard-container">
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="profile-section">
            <img src="<?= !empty($student['photo']) ? htmlspecialchars($student['photo']) : 'uploads/default.png' ?>" 
                 class="profile-photo" 
                 alt="Profile Photo"
                 onerror="this.src='uploads/default.png'">
            <!-- <h3><?= htmlspecialchars($student['name'] ) ?></h3> -->
            <h3><?= htmlspecialchars($student['name']) ?></h3>
            <p>Roll No: <?= htmlspecialchars($student['rollno']) ?></p>
        </div>
        <ul class="sidebar-menu">
            <li><a href="view_profile.php">View Profile</a></li>
            <li><a href="mark_attendance.php">Mark Attendance</a></li>
            <li><a href="view_attendance.php">View Attendance</a></li>
            <li><a href="submit_leave.php">Submit Leave</a></li>
            <li><a href="leaveStatus.php">Leave Status</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <h2>Welcome, <?= htmlspecialchars($student['name']) ?></h2>
        <p>Please select an option from the sidebar to proceed.</p>
        
        <!-- Debugging section (remove in production)
        <div style="margin-top: 20px; color: #666; font-size: 12px;">
            <strong>Debug Info:</strong><br>
            Photo Path: <?= htmlspecialchars($student['photo']) ?><br>
            File Exists: <?= file_exists($student['photo']) ? 'Yes' : 'No' ?>
        </div> -->
    </div>
</div>

</body>
</html>