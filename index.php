<?php
session_start();
require 'db.php';

$error = "";
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $rollno = $_POST['rollno'];
    $password = $_POST['password'];
    $role = $_POST['role'];

    // Determine the table based on role
    if ($role == 'Student') {
        $table = 'students';
    } elseif ($role == 'Faculty') {
        $table = 'faculty';
    } else {
        $table = 'admin';
    }

    // Check login credentials
    $query = "SELECT * FROM $table WHERE rollno='$rollno' AND password='$password'";
    $result = $conn->query($query);

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $_SESSION['user'] = $user['rollno'];
        $_SESSION['role'] = $role;

        // Redirect based on role
        if ($role == 'Student') {
            header('Location: student_dashboard.php');
        } elseif ($role == 'Faculty') {
            header('Location: faculty_dashboard.php');
        } else {
            header('Location: admin_dashboard.php');
        }
        exit;
    } else {
        $error = "Invalid credentials!";
    }
}
?><!DOCTYPE html>
<html lang="en">
<head>
    <title>Login - Smart Attendance</title>
    <!-- <link rel="stylesheet" href="css/styles.css"> -->
    <style>
        body {
            background: url('public/1.png') no-repeat center center fixed;
            background-size: cover;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .login-container {
            position:fixed;
            width: 500px;
            margin-top:400px;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            background: rgba(250,250,250, 0.5); /* Semi-transparent background */
        }
        .login-container h2 {
            text-align: center;
            margin-bottom: 20px;
        }
        .login-container label {
            display: block;
            margin-bottom: 5px;
        }
        .login-container input, .login-container select, .login-container button {
            width: 100%;
            padding: 10px;
            margin-top: 10px;
            border: 1px solid #ccc;
            background: rgba(255, 255, 255, 0.5);
            border-radius: 5px;
        }
        .login-container .error {
            color: red;
            text-align: center;
        }
        button {
            background-color: rgb(114, 107, 219);
            color: white;
            border: none;
            cursor: pointer;
        }
        button:hover {
            background-color: rgb(54, 90, 158);
        }
    </style>
</head>
<body>

<div class="login-container">
    <h2>Smart Attendance Login</h2>
    <form method="POST">
        <label>Roll No</label>
        <input type="text" name="rollno" required>

        <label>Password</label>
        <input type="password" name="password" required>

        <label>Role</label>
        <select name="role">
            <option value="Student">Student</option>
            <option value="Faculty">Faculty</option>
            <option value="Admin">Admin</option>
        </select>

        <button type="submit">Login</button>
    </form>

    <?php if ($error): ?>
        <p class="error"><?= $error ?></p>
    <?php endif; ?>
</div>

</body>
</html>