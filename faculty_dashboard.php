<?php
session_start();
require 'db.php';

// Secure session validation
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Faculty') {
    header('Location: index.php');
    exit;
}

$rollno = $_SESSION['user'];

// Fetch faculty details using prepared statement
$facultyQuery = $conn->prepare("SELECT name, profile_photo FROM faculty WHERE rollno=?");
$facultyQuery->bind_param("s", $rollno);
$facultyQuery->execute();
$facultyResult = $facultyQuery->get_result();

if ($facultyResult->num_rows > 0) {
    $faculty = $facultyResult->fetch_assoc();
} else {
    $faculty = ['name' => 'Unknown', 'profile_photo' => 'uploads/default.png'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faculty Dashboard</title>
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

        .welcome-message {
            font-size: 18px;
            color: var(--dark);
            margin-bottom: 20px;
        }

        .quick-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            padding: 20px;
            text-align: center;
        }

        .stat-number {
            font-size: 24px;
            font-weight: bold;
            color: var(--secondary);
            margin: 10px 0;
        }

        .stat-label {
            color: var(--dark);
            font-size: 14px;
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
            .quick-stats {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

<div class="dashboard-container">
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-profile">
            <img src="<?= htmlspecialchars($faculty['profile_photo']) ?>" 
                 class="profile-photo" 
                 alt="Profile Photo"
                 onerror="this.src='uploads/default.png'">
            <h3><?= htmlspecialchars($faculty['name']) ?></h3>
            <p>Faculty ID: <?= htmlspecialchars($rollno) ?></p>
        </div>
        <ul class="sidebar-menu">
            <li><a href="faculty_dashboard.php" class="active">Dashboard</a></li>
            <li><a href="faculty_manage_sessions.php">Manage Sessions</a></li>
            <li><a href="faculty_leave_review.php">Review Leave Applications</a></li>
            <li><a href="faculty_view_attendance.php">View Attendance</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <h2>Welcome, Prof. <?= htmlspecialchars($faculty['name']) ?></h2>
        <p class="welcome-message">Use the sidebar to manage your classes and review student submissions.</p>
        
       
       
    </div>
</div>

</body>
</html>