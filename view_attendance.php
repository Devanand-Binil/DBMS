<?php
session_start();
require 'db.php';

if ($_SESSION['role'] !== 'Student') {
    header('Location: index.php');
    exit;
}

$rollno = $_SESSION['user'];

// Fetch student details
$student = $conn->query("SELECT name FROM students WHERE rollno='$rollno'")->fetch_assoc();

// Fetch attendance records
$result = $conn->query("SELECT * FROM attendance WHERE rollno='$rollno' ORDER BY date DESC");

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>My Attendance</title>
    <link rel="stylesheet" href="css/styles.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            height: 100vh;
        }
        .sidebar {
            width: 250px;
            background-color: #2c3e50;
            color: white;
            padding: 20px;
            height: 100vh;
        }
        .sidebar h3, .sidebar p, .sidebar a {
            color: white;
            text-decoration: none;
            margin-bottom: 10px;
            display: block;
        }
        .main {
            flex-grow: 1;
            padding: 20px;
            background-color: #f4f4f4;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background-color: white;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 10px;
            text-align: center;
        }
        th {
            background-color: #34495e;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
    </style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
    <h3>Student Dashboard</h3>
    <p>Welcome, <?= htmlspecialchars($student['name']) ?> (<?= htmlspecialchars($rollno) ?>)</p>
    <a href="student_dashboard.php">Dashboard</a>
    <a href="view_attendance.php">View Attendance</a>
    <a href="submit_leave.php">Apply for Leave</a>
    <a href="logout.php">Logout</a>
</div>

<!-- Main Content -->
<div class="main">
    <h2>My Attendance Records</h2>
    <table>
        <tr>
            <th>Date</th>
            <th>Class Code</th>
            <th>Status</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= date('Y-m-d ', strtotime($row['date'])) ?></td>
                <td><?= htmlspecialchars($row['class_code']) ?></td>
                <td><?= htmlspecialchars($row['status']) ?></td>
            </tr>
        <?php endwhile; ?>
    </table>
</div>

</body>
</html>
