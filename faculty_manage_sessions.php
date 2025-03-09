<?php
session_start();
require 'db.php';

if ($_SESSION['role'] !== 'Faculty') {
    header('Location: index.php');
    exit;
}

// Auto-expire logic: Update sessions that are past expiry time
$conn->query("UPDATE class_sessions SET status='inactive' WHERE expires_at <= NOW() AND status='active'");

$rollno = $_SESSION['user'];
$faculty_result = $conn->query("SELECT name FROM faculty WHERE rollno='$rollno'");
$faculty_name = ($faculty_result->num_rows > 0) ? $faculty_result->fetch_assoc()['name'] : 'Unknown';

// Handle form submission - Create New Class Session
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $class_name = $_POST['class_name'];
    $code = $_POST['code'];
    $expires_at = $_POST['expires_at']; // Faculty sets expiry date & time

    // Fetch latitude & longitude from classes table
    $class_query = $conn->query("SELECT latitude, longitude FROM classes WHERE class_name='$class_name'");
    if ($class_query->num_rows > 0) {
        $class = $class_query->fetch_assoc();
        $latitude = $class['latitude'];
        $longitude = $class['longitude'];

        // Insert into class_sessions
        $conn->query("INSERT INTO class_sessions (class_name, code, faculty_name, latitude, longitude, status, expires_at, created_at) 
                      VALUES ('$class_name', '$code', '$faculty_name', '$latitude', '$longitude', 'active', '$expires_at', NOW())");

        echo "<script>alert('Class session created successfully!');</script>";
    } else {
        echo "<script>alert('Invalid class selected.');</script>";
    }
}

// Fetch available classes
$classes = $conn->query("SELECT class_name FROM classes");

// Fetch sessions with filtering
$filter_class = $_GET['filter_class'] ?? '';
$filter_status = $_GET['filter_status'] ?? '';

$query = "SELECT * FROM class_sessions WHERE 1=1";
if ($filter_class) {
    $query .= " AND class_name='$filter_class'";
}
if ($filter_status) {
    $query .= " AND status='$filter_status'";
}
$query .= " ORDER BY created_at DESC";

$sessions = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Manage Classes</title>
    <link rel="stylesheet" href="css/styles.css">
    <style>
        /* Sidebar Styles */
        .dashboard {
            display: flex;
        }
        .sidebar {
            width: 250px;
            background-color: #2c3e50;
            color: white;
            padding: 20px;
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
        }
        .sidebar h3 {
            text-align: center;
            margin-bottom: 20px;
        }
        .sidebar a {
            display: block;
            color: white;
            text-decoration: none;
            padding: 12px 15px;
            margin: 5px 0;
            border-radius: 5px;
            transition: background 0.3s;
        }
        .sidebar a:hover, .sidebar a.active {
            background-color: #1a252f;
        }

        /* Adjust the main content to avoid overlap */
        .main {
            margin-left: 270px;
            padding: 20px;
            width: calc(100% - 270px);
        }

        /* Table Styles */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 10px;
            text-align: center;
        }
        .filter-container {
            margin-bottom: 15px;
        }

        /* Mobile Optimization */
        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
            }
            .main {
                margin-left: 0;
                width: 100%;
            }
        }
    </style>
</head>
<body>

<div class="dashboard">
    <!-- Sidebar -->
    <div class="sidebar">
        <h3>Faculty Dashboard</h3>
        <a href="faculty_dashboard.php">Dashboard</a>
        <a href="faculty_manage_sessions.php" class="active">Manage Sessions</a>
        <a href="faculty_leave_review.php">Review Leave Applications</a>
        <a href="logout.php">Logout</a>
    </div>

    <!-- Main Content -->
    <div class="main">
        <h2>Schedule New Class Session</h2>
        <form method="POST">
            <label>Class Name</label>
            <select name="class_name" required>
                <option value="">Select Class</option>
                <?php while ($row = $classes->fetch_assoc()) { ?>
                    <option value="<?= htmlspecialchars($row['class_name']) ?>"><?= htmlspecialchars($row['class_name']) ?></option>
                <?php } ?>
            </select>

            <label>Class Code (for students to join)</label>
            <input type="text" name="code" required>

            <label>Session Expiry Date & Time</label>
            <input type="datetime-local" name="expires_at" required>

            <button type="submit">Create Session</button>
        </form>

        <h3>Existing Sessions</h3>

        <!-- Filter Options -->
        <div class="filter-container">
            <form method="GET">
                <label>Filter by Class:</label>
                <select name="filter_class">
                    <option value="">All</option>
                    <?php
                    $class_list = $conn->query("SELECT DISTINCT class_name FROM class_sessions");
                    while ($row = $class_list->fetch_assoc()) {
                        $selected = ($row['class_name'] === $filter_class) ? 'selected' : '';
                        echo "<option value='{$row['class_name']}' $selected>{$row['class_name']}</option>";
                    }
                    ?>
                </select>

                <label>Filter by Status:</label>
                <select name="filter_status">
                    <option value="">All</option>
                    <option value="active" <?= ($filter_status === 'active') ? 'selected' : '' ?>>Active</option>
                    <option value="inactive" <?= ($filter_status === 'inactive') ? 'selected' : '' ?>>Inactive</option>
                </select>

                <button type="submit">Filter</button>
            </form>
        </div>

        <table>
            <tr>
                <th>Class</th>
                <th>Class Code</th>
                <th>Faculty</th>
                <th>Status</th>
                <th>Created At</th>
                <th>Expires At</th>
            </tr>
            <?php while ($row = $sessions->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['class_name']) ?></td>
                    <td><?= htmlspecialchars($row['code']) ?></td>
                    <td><?= htmlspecialchars($row['faculty_name']) ?></td>
                    <td><?= htmlspecialchars($row['status']) ?></td>
                    <td><?= htmlspecialchars($row['created_at']) ?></td>
                    <td><?= htmlspecialchars($row['expires_at']) ?></td>
                </tr>
            <?php endwhile; ?>
        </table>
    </div>
</div>

</body>
</html>
