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
            --formBg: black;
            --formTxt: white;
        }

        [data-theme="light"] {
            --formBg: white;
            --formTxt: black;
        }

        * {
            font-family: "Montserrat", sans-serif;
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            margin: 0;
            padding: 0;
            background-color: #2596be;
            color: blue;
        }

        .dashboard-container {
            display: flex;
            height: 100vh;
        }

        .sidebar {
            width: 250px;
            background-color: #1a73e8;
            padding: 20px;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
        }

        .profile-section {
            text-align: center;
            margin-bottom: 20px;
        }

        .profile-photo {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            margin-bottom: 10px;
            object-fit: cover;
            border: 3px solid white;
        }

        .sidebar h3 {
            margin: 0;
            font-size: 18px;
            color: white;
        }

        .sidebar p {
            margin: 5px 0;
            font-size: 14px;
            color: white;
        }

        .sidebar-menu {
            list-style: none;
            padding: 0;
        }

        .sidebar-menu li {
            margin-bottom: 10px;
        }

        .sidebar-menu a {
            color: white;
            text-decoration: none;
            display: block;
            padding: 10px;
            border-radius: 5px;
            transition: background 0.3s;
        }

        .sidebar-menu a:hover {
            background-color: #0c5dbb;
        }

        .main-content {
            flex-grow: 1;
            padding: 20px;
            background-color: white;
            border-radius: 8px;
            margin: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .main-content h2 {
            margin-top: 0;
            color: #1a73e8;
        }

        @media (max-width: 768px) {
            .dashboard-container {
                flex-direction: column;
            }
            .sidebar {
                width: 100%;
                height: auto;
            }
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
            <h3><?= htmlspecialchars($student['name'] . " " . $student['lastname']) ?></h3>
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