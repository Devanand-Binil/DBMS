<?php
session_start();
require 'db.php';

if ($_SESSION['role'] !== 'Faculty') {
    header('Location: index.php');
    exit;
}

$rollno = $_SESSION['user'];
$faculty = $conn->query("SELECT * FROM faculty WHERE rollno='$rollno'")->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Faculty Dashboard</title>
    <link rel="stylesheet" href="css/styles.css">
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
        }
        .dashboard-container {
            display: flex;
            height: 100vh;
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
            margin: 0 0 10px;
            font-size: 18px;
        }
        .sidebar p {
            margin: 5px 0 15px;
            font-size: 14px;
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
        .sidebar .profile-photo {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 10px;
        }
        .main-content {
            flex-grow: 1;
            padding: 30px;
            box-sizing: border-box;
            overflow-y: auto;
            background-color: #f8f9fa;
        }
        h2 {
            margin: 0 0 15px;
        }
        p {
            font-size: 16px;
        }
    </style>
</head>
<body>

<div class="dashboard-container">
    <!-- Sidebar -->
    <div class="sidebar">
        <h3><?= htmlspecialchars($faculty['name']) ?></h3>
        <img src="<?= htmlspecialchars($faculty['profile_photo'] ?? 'uploads/default.png') ?>" class="profile-photo" alt="Profile Photo">
        <p>Faculty ID: <?= htmlspecialchars($faculty['rollno']) ?></p>

        <a href="faculty_dashboard.php">Home</a>
        <a href="faculty_manage_sessions.php">Manage Sessions</a>
        <a href="faculty_leave_review.php">Review Leave Applications</a>
        <a href="faculty_view_attendance.php" class="active">View Attendance</a> <!-- 🔥 Added Attendance Page Link -->
        <a href="logout.php">Logout</a>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <h2>Welcome, Prof. <?= htmlspecialchars($faculty['name']) ?></h2>
        <p>Select an option from the sidebar to manage class sessions or review leave applications.</p>
    </div>
</div>

</body>
</html>
