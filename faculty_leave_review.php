<?php
session_start();
require 'db.php';

if ($_SESSION['role'] !== 'Faculty') {
    header('Location: index.php');
    exit;
}

$rollno = $_SESSION['user'];
$faculty_stmt = $conn->prepare("SELECT name, profile_photo FROM faculty WHERE rollno=?");
$faculty_stmt->bind_param("s", $rollno);
$faculty_stmt->execute();
$faculty_result = $faculty_stmt->get_result();
$faculty = ($faculty_result->num_rows > 0) ? $faculty_result->fetch_assoc() : ['name' => 'Unknown', 'profile_photo' => 'uploads/default.png'];

$applications = $conn->query("SELECT * FROM leave_applications WHERE professor_name='{$faculty['name']}' ORDER BY id DESC");

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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Review Leave Applications</title>
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
            align-items: center;
            padding: 15px 20px;
            margin-bottom: 20px;
        }

        .profile-photo {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 15px;
            border: 2px solid var(--secondary);
        }

        .sidebar-menu {
            list-style: none;
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

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--dark);
        }

        select, input {
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
            transition: border 0.3s;
        }

        select:focus, input:focus {
            outline: none;
            border-color: var(--secondary);
        }

        button, .btn {
            background-color: var(--secondary);
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            transition: background 0.3s;
        }

        button:hover, .btn:hover {
            background-color: #2980b9;
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
            text-align: center;
        }

        tr:hover {
            background-color: #f9f9f9;
        }

        .status-approved {
            color: var(--success);
            font-weight: 600;
        }

        .status-rejected {
            color: var(--danger);
            font-weight: 600;
        }

        .status-pending {
            color: #f39c12;
            font-weight: 600;
        }

        .action-form {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .action-form select {
            flex: 1;
        }

        .action-form button {
            white-space: nowrap;
        }

        .file-link {
            color: var(--secondary);
            text-decoration: none;
        }

        .file-link:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>
    <div class="dashboard-container">
        <div class="sidebar">
            <div class="sidebar-header">
                <h2>Faculty Dashboard</h2>
            </div>
            <div class="sidebar-profile">
                <img src="<?php echo htmlspecialchars($faculty['profile_photo']); ?>" alt="Profile" class="profile-photo">
                <div>
                    <p><?php echo htmlspecialchars($faculty['name']); ?></p>
                    <small>Faculty</small>
                </div>
            </div>
            <ul class="sidebar-menu">
                <li><a href="faculty_dashboard.php">Dashboard</a></li>
                <li><a href="faculty_viewprofile.php">View profile</a></li>
                <li><a href="faculty_manage_sessions.php">Manage Sessions</a></li>
                <li><a href="faculty_leave_review.php" class="active">Review Leave Applications</a></li>
                <li><a href="faculty_view_attendance.php">View Attendance</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </div>

        <div class="main-content">
            <h1>Review Leave Applications</h1>

            <div class="card">
                <table>
                    <thead>
                        <tr>
                            <th>Roll No</th>
                            <th>Student Name</th>
                            <th>Professor</th>
                            <th>Reason</th>
                            <th>Attachment</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $applications->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['student_rollno']) ?></td>
                                <td><?= htmlspecialchars($row['student_name']) ?></td>
                                <td><?= htmlspecialchars($row['professor_name']) ?></td>
                                <td><?= htmlspecialchars($row['explanation']) ?></td>
                                <td>
                                    <?php if ($row['document']): ?>
                                        <a href="<?= htmlspecialchars($row['document']) ?>" target="_blank" class="file-link">View File</a>
                                    <?php else: ?>
                                        None
                                    <?php endif; ?>
                                </td>
                                <td class="status-<?= strtolower($row['status']) ?>">
                                    <?= htmlspecialchars($row['status']) ?>
                                </td>
                                <td>
                                    <form method="POST" class="action-form">
                                        <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                        <select name="status">
                                            <option value="Approved" <?= ($row['status'] == 'Approved') ? 'selected' : '' ?>>Approve</option>
                                            <option value="Rejected" <?= ($row['status'] == 'Rejected') ? 'selected' : '' ?>>Reject</option>
                                        </select>
                                        <button type="submit">Update</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                        <?php if ($applications->num_rows == 0): ?>
                            <tr>
                                <td colspan="7" style="text-align: center;">No leave applications found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>