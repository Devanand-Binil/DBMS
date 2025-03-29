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
        
                body {
            background: url('uploads/admin_back.jpg') no-repeat center center fixed;
            background-size: cover;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            font-family: 'Poppins', sans-serif;
        }

       
        .dashboard-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 400px;
            width: 600px;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(8px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: transform 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        .dashboard-container:hover {
            transform: translateY(-5px);
        }
        .dashboard-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(
                90deg,
                transparent,
                rgba(255, 255, 255, 0.2),
                transparent
            );
            transition: 0.5s;
        }
        .dashboard-container:hover::before {
        left: 100%;
        }

        
        .dashboard-container h2 {
            margin-bottom: 2rem;
            font-size: 2.5rem;
            color: #fff;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
            position: relative;
            background: linear-gradient(45deg, #00ff88, #61dafb);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        
        .dashboard-container h2::after {
            content: '';
            position: absolute;
            bottom: -12px;
            left: 50%;
            transform: translateX(-50%);
            width: 90px;
            height: 4px;
            background: linear-gradient(90deg, rgb(194, 244, 254), rgb(191, 240, 254));
            border-radius: 3px;
        }

        
        .dashboard-buttons {
            display: flex;
            gap: 2rem;
            flex-wrap: wrap;
            justify-content: center;
        }

       
        .dashboard-buttons a {
            text-decoration: none;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1.2rem 2.5rem;
            border-radius: 15px;
            font-weight: 600;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            border: none;
            font-size: 1.1rem;
        }
        
        .dashboard-buttons a:hover {
            transform: translateY(-5px) scale(1.05);
            box-shadow: 0 6px 18px rgba(188, 240, 253, 0.4);
            background: linear-gradient(135deg, rgb(197, 246, 253), rgb(188, 240, 253));
        }

        
        .dashboard-buttons a::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.2);
            opacity: 0;
            transition: opacity 0.3s;
        }

        .dashboard-buttons a:hover::before {
            opacity: 1;
        }

     
        @media (max-width: 650px) {
            .dashboard-container {
                width: 90%;
                height: 350px; 
                padding: 20px;
            }
        }

        @media (max-width: 480px) {
            .dashboard-container {
                width: 90%;
                height: auto; 
                padding: 15px;
            }
            .dashboard-buttons {
                flex-direction: column;
                gap: 0.8rem;
            }
            .dashboard-buttons a {
                width: 100%;
                text-align: center;
            }
        }

        @media (max-width: 360px) {
            .dashboard-container {
                width: 95%;
                height: auto;
                padding: 10px;
            }
            .dashboard-container h2 {
                font-size: 1.8rem; 
            }
            .dashboard-buttons a {
                padding: 0.8rem 1rem;
                font-size: 0.9rem;
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
