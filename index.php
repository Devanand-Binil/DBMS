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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Login - Smart Attendance</title>
    <link rel="stylesheet" href="css/styles.css">
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
