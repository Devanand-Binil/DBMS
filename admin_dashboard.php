<?php
session_start();
require 'db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    header('Location: index.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="css/styles.css">
    <style>
        .dashboard-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100vh;
            text-align: center;
        }
        .dashboard-container h2 {
            margin-bottom: 20px;
        }
        .dashboard-buttons {
            display: flex;
            gap: 20px;
        }
        .dashboard-buttons a {
            text-decoration: none;
            background-color: #007bff;
            color: white;
            padding: 15px 25px;
            border-radius: 5px;
            font-size: 18px;
        }
        .dashboard-buttons a:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <h2>Welcome, Admin</h2>
        <div class="dashboard-buttons">
            <a href="manage_students.php">Manage Students</a>
            <a href="manage_faculty.php">Manage Faculty</a>
            <a href="manage_classes.php">Manage Classes</a>
        </div>
    </div>
</body>
</html>
