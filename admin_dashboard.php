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
        min-height: 100vh;
        background: #f8f9fa;
        font-family: 'Segoe UI', sans-serif;
        padding: 2rem;
    }

    .dashboard-container h2 {
        margin-bottom: 2rem;
        font-size: 2.2rem;
        color: #2c3e50;
        position: relative;
    }

    .dashboard-container h2::after {
        content: '';
        position: absolute;
        bottom: -10px;
        left: 50%;
        transform: translateX(-50%);
        width: 60px;
        height: 3px;
        background: #007bff;
        border-radius: 3px;
    }

    .dashboard-buttons {
        display: flex;
        gap: 1.5rem;
        flex-wrap: wrap;
        justify-content: center;
    }

    .dashboard-buttons a {
        text-decoration: none;
        background: #007bff;
        color: white;
        padding: 1rem 2rem;
        border-radius: 8px;
        font-weight: 500;
        box-shadow: 0 4px 12px rgba(0, 123, 255, 0.2);
        transition: all 0.3s ease;
    }

    .dashboard-buttons a:hover {
        transform: translateY(-3px);
        box-shadow: 0 6px 16px rgba(0, 123, 255, 0.3);
        background: #0069d9;
    }

    /* Responsive adjustment */
    @media (max-width: 600px) {
        .dashboard-buttons {
            gap: 1rem;
        }
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
