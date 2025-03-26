<?php
session_start();
require 'db.php';

if ($_SESSION['role'] !== 'Student') {
    header('Location: index.php');
    exit;
}

$rollno = $_SESSION['user'];

// Fetch leave status
$leaveQuery = $conn->query("SELECT * FROM leave_requests WHERE rollno='$rollno' ORDER BY date_submitted DESC");

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Leave Status</title>
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
    <p>Welcome, <?= htmlspecialchars($_SESSION['user']) ?></p>
    <a href="student_dashboard.php">Dashboard</a>
    <a href="view_attendance.php">View Attendance</a>
    <a href="submit_leave.php">Apply for Leave</a>
    <a href="leaveStatus.php">Leave Status</a>
    <a href="logout.php">Logout</a>
</div>

<!-- Main Content -->
<div class="main">
    <h2>My Leave Status</h2>
    <table>
        <tr>
            <th>Date Submitted</th>
            <th>Leave Start Date</th>
            <th>Leave End Date</th>
            <th>Status</th>
            <th>Remarks</th>
        </tr>
        <?php if ($leaveQuery->num_rows > 0): ?>
            <?php while ($row = $leaveQuery->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars(date('Y-m-d', strtotime($row['date_submitted']))) ?></td>
                    <td><?= htmlspecialchars(date('Y-m-d', strtotime($row['start_date']))) ?></td>
                    <td><?= htmlspecialchars(date('Y-m-d', strtotime($row['end_date']))) ?></td>
                    <td><?= htmlspecialchars($row['status']) ?></td>
                    <td><?= htmlspecialchars($row['remarks']) ?></td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="5">No leave requests found.</td>
            </tr>
        <?php endif; ?>
    </table>
</div>

</body>
</html>