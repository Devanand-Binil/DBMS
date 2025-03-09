<?php
session_start();
require 'db.php';

if ($_SESSION['role'] !== 'Faculty') {
    header('Location: index.php');
    exit;
}

//$faculty_name = $_SESSION['faculty_name']; // Get the logged-in faculty name
$rollno = $_SESSION['user'];
$faculty_result = $conn->query("SELECT name FROM faculty WHERE rollno='$rollno'");
$faculty_name = ($faculty_result->num_rows > 0) ? $faculty_result->fetch_assoc()['name'] : 'Unknown';
$applications = $conn->query("SELECT * FROM leave_applications WHERE professor_name='$faculty_name' ORDER BY id DESC");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];
    $status = $_POST['status'];
    $conn->query("UPDATE leave_applications SET status='$status' WHERE id='$id'");
    echo "<script>alert('Leave status updated!'); window.location='faculty_leave_review.php';</script>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Review Leave Applications</title>
    <link rel="stylesheet" href="css/styles.css">
    <style>
        .container {
            display: flex;
            min-height: 100vh;
        }
        .sidebar {
            width: 250px;
            background-color: #2c3e50;
            color: white;
            padding: 20px;
        }
        .sidebar a {
            display: block;
            color: white;
            padding: 10px;
            text-decoration: none;
            margin: 5px 0;
        }
        .sidebar a:hover {
            background-color: #34495e;
        }
        .main-content {
            flex: 1;
            padding: 20px;
            overflow-x: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table, th, td {
            border: 1px solid #ccc;
            padding: 10px;
            text-align: center;
        }
        th {
            background-color: #f4f4f4;
        }
        select, button {
            padding: 5px;
            margin-top: 5px;
        }
    </style>
</head>
<body>

<div class="container">
    <!-- Sidebar -->
    <div class="sidebar">
        <h3>Faculty Dashboard</h3>
        <a href="faculty_dashboard.php">Dashboard</a>
        <a href="faculty_manage_sessions.php">Manage Sessions</a>
        <a href="faculty_leave_review.php">Review Leave Applications</a>
        <a href="logout.php">Logout</a>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <h2>Review Leave Applications</h2>

        <table>
            <tr>
                <th>Roll No</th>
                <th>Student Name</th>
                <th>Professor</th>
                <th>Reason</th>
                <th>Attachment</th>
                <th>Status</th>
                <th>Action</th>
            </tr>

            <?php while ($row = $applications->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['student_rollno']) ?></td>
                    <td><?= htmlspecialchars($row['student_name']) ?></td>
                    <td><?= htmlspecialchars($row['professor_name']) ?></td>
                    <td><?= htmlspecialchars($row['explanation']) ?></td>
                    <td>
                        <?php if ($row['document']): ?>
                            <a href="<?= htmlspecialchars($row['document']) ?>" target="_blank">View File</a>
                        <?php else: ?>
                            None
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($row['status']) ?></td>
                    <td>
                        <form method="POST">
                            <input type="hidden" name="id" value="<?= $row['id'] ?>">
                            <select name="status">
                                <option value="Approved">Approve</option>
                                <option value="Rejected">Reject</option>
                            </select>
                            <button type="submit">Update</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>
    </div>
</div>

</body>
</html>
