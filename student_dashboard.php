<?php
session_start();
require 'db.php';

if ($_SESSION['role'] !== 'Student') {
    header('Location: index.php');
    exit;
}

$rollno = $_SESSION['user'];

// Fetch student details from the `students` table
$student = $conn->query("SELECT * FROM students WHERE rollno='$rollno'")->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Student Dashboard</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>

<div class="dashboard-container">
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="profile-section">
            <img src="uploads/<?= $student['photo'] ?>" class="profile-photo" alt="Profile Photo">
            <h3><?= htmlspecialchars($student['firstname'] . " " . $student['lastname']) ?></h3>
            <p>Roll No: <?= htmlspecialchars($student['rollno']) ?></p>
        </div>
        <ul class="sidebar-menu">
            <li><a href="view_profile.php">View Profile</a></li>
            <li><a href="mark_attendance.php">Mark Attendance</a></li>
            <li><a href="view_attendance.php">View Attendance</a></li>
            <li><a href="submit_leave.php">Submit Leave</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <h2>Welcome, <?= htmlspecialchars($student['firstname']) ?></h2>
        <p>Please select an option from the sidebar to proceed.</p>
    </div>
</div>

</body>
</html>
