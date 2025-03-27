<?php
session_start();
require 'db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Student') {
    header('Location: index.php');
    exit;
}

$rollno = $_SESSION['user'];

// Fetch student details using prepared statement
$studentQuery = $conn->prepare("SELECT name, profile_photo FROM students WHERE rollno=?");
$studentQuery->bind_param("s", $rollno);
$studentQuery->execute();
$studentResult = $studentQuery->get_result();

if ($studentResult->num_rows > 0) {
    $student = $studentResult->fetch_assoc();
} else {
    $student = ['name' => 'Unknown', 'profile_photo' => 'uploads/default.png'];
}

// Fetch attendance records with class name from classes table
$attendanceQuery = $conn->prepare("
    SELECT a.date, a.status, c.class_name 
    FROM attendance a
    JOIN classes c ON a.class_id = c.id
    WHERE a.rollno=? 
    ORDER BY a.date DESC
");
$attendanceQuery->bind_param("s", $rollno);
$attendanceQuery->execute();
$attendanceResult = $attendanceQuery->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Attendance</title>
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
        }

        h1, h2, h3 {
            color: var(--primary);
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
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

        .status-present {
            color: var(--success);
            font-weight: 600;
        }

        .status-absent {
            color: var(--danger);
            font-weight: 600;
        }

        .no-records {
            padding: 15px;
            text-align: center;
            color: #666;
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
        <div class="sidebar-profile">
            <img src="<?= htmlspecialchars($student['profile_photo']) ?>" 
                 class="profile-photo" 
                 alt="Profile Photo"
                 onerror="this.src='uploads/default.png'">
            <h3><?= htmlspecialchars($student['name']) ?></h3>
            <p>Roll No: <?= htmlspecialchars($rollno) ?></p>
        </div>
        <ul class="sidebar-menu">
            <li><a href="student_dashboard.php">Home</a></li>
            <li><a href="view_profile.php">View Profile</a></li>
            <li><a href="mark_attendance.php">Mark Attendance</a></li>
            <!-- <li><a href="view_attendance.php" class="active">View Attendance</a></li> -->
            <li><a href="submit_leave.php">Submit Leave</a></li>
            <li><a href="leaveStatus.php">Leave Status</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="card">
            <h2>My Attendance Records</h2>
            
            <?php if ($attendanceResult->num_rows > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Class Name</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $attendanceResult->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars(date('M d, Y', strtotime($row['date']))) ?></td>
                                <td><?= htmlspecialchars($row['class_name']) ?></td>
                                <td class="status-<?= strtolower($row['status']) ?>">
                                    <?= htmlspecialchars($row['status']) ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="no-records">No attendance records found.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

</body>
</html>