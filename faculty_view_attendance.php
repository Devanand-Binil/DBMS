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

$message = "";
$attendance_records = [];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && !isset($_POST['download_excel'])) {
    $filter_type = $_POST['filter_type'];

    if ($filter_type == 'date') {
        $date = $_POST['date'];
        $query = "SELECT a.rollno, s.name, c.class_name, a.date, a.time, a.status 
                  FROM attendance a
                  JOIN students s ON a.rollno = s.rollno
                  JOIN classes c ON a.class_id = c.id
                  WHERE a.date = '$date'
                  ORDER BY a.time ASC";
    } elseif ($filter_type == 'rollno') {
        $rollno = $_POST['rollno'];
        $query = "SELECT a.rollno, s.name, c.class_name, a.date, a.time, a.status 
                  FROM attendance a
                  JOIN students s ON a.rollno = s.rollno
                  JOIN classes c ON a.class_id = c.id
                  WHERE a.rollno = '$rollno'
                  ORDER BY a.date DESC, a.time ASC";
    } elseif ($filter_type == 'class') {
        $class_name = $_POST['class_name'];
        $query = "SELECT a.rollno, s.name, c.class_name, a.date, a.time, a.status 
                  FROM attendance a
                  JOIN students s ON a.rollno = s.rollno
                  JOIN classes c ON a.class_id = c.id
                  WHERE c.class_name = '$class_name'
                  ORDER BY a.date DESC, a.time ASC";
    }

    $result = $conn->query($query);

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $attendance_records[] = $row;
        }
    } else {
        $message = "No attendance records found.";
    }
}

// Handle Excel Download
if (isset($_POST['download_excel'])) {
    header("Content-Type: application/xls");
    header("Content-Disposition: attachment; filename=attendance_report.xls");
    header("Pragma: no-cache");
    header("Expires: 0");

    $sep = "\t";
    echo "Roll No\tName\tClass\tDate\tTime\tStatus\n";
    foreach ($attendance_records as $record) {
        echo $record['rollno'] . $sep . $record['name'] . $sep . $record['class_name'] . $sep . $record['date'] . $sep . $record['time'] . $sep . $record['status'] . "\n";
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Attendance</title>
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

        .alert {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
        }

        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .filter-form {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            align-items: center;
            margin-bottom: 20px;
        }

        .filter-form > * {
            flex: 1 1 200px;
        }

        .filter-form button {
            white-space: nowrap;
        }

        .status-present {
            color: var(--success);
            font-weight: 600;
        }

        .status-absent {
            color: var(--danger);
            font-weight: 600;
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
                <li><a href="faculty_leave_review.php">Review Leave Applications</a></li>
                <!-- <li><a href="faculty_view_attendance.php" class="active">View Attendance</a></li> -->
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </div>

        <div class="main-content">
            <h1>View Attendance Records</h1>

            <?php if ($message): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>

            <div class="card">
                <form method="POST" class="filter-form">
                    <div>
                        <label>Filter By:</label>
                        <select name="filter_type" required id="filter_type" onchange="toggleInputs()">
                            <option value="date">Date</option>
                            <option value="rollno">Roll No</option>
                            <option value="class">Class</option>
                        </select>
                    </div>

                    <div id="date_input_container">
                        <label>Date</label>
                        <input type="date" name="date" id="date_input">
                    </div>

                    <div id="rollno_input_container" style="display:none;">
                        <label>Roll No</label>
                        <input type="text" name="rollno" id="rollno_input" placeholder="Enter Roll No">
                    </div>

                    <div id="class_input_container" style="display:none;">
                        <label>Class Name</label>
                        <input type="text" name="class_name" id="class_input" placeholder="Enter Class Name">
                    </div>

                    <div style="align-self: flex-end;">
                        <button type="submit">Filter</button>
                        <button type="submit" name="download_excel">Download Excel</button>
                    </div>
                </form>
                
                <table>
                    <thead>
                        <tr>
                            <th>Roll No</th>
                            <th>Name</th>
                            <th>Class</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($attendance_records as $record): ?>
                            <tr>
                                <td><?= htmlspecialchars($record['rollno']) ?></td>
                                <td><?= htmlspecialchars($record['name']) ?></td>
                                <td><?= htmlspecialchars($record['class_name']) ?></td>
                                <td><?= htmlspecialchars($record['date']) ?></td>
                                <td><?= htmlspecialchars($record['time']) ?></td>
                                <td class="status-<?= strtolower($record['status']) ?>">
                                    <?= htmlspecialchars($record['status']) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($attendance_records)): ?>
                            <tr>
                                <td colspan="6" style="text-align: center;">No attendance records found. Apply filters to view records.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        function toggleInputs() {
            var filterType = document.getElementById('filter_type').value;
            
            document.getElementById('date_input_container').style.display = 'none';
            document.getElementById('rollno_input_container').style.display = 'none';
            document.getElementById('class_input_container').style.display = 'none';

            if (filterType === 'date') {
                document.getElementById('date_input_container').style.display = 'block';
            } else if (filterType === 'rollno') {
                document.getElementById('rollno_input_container').style.display = 'block';
            } else if (filterType === 'class') {
                document.getElementById('class_input_container').style.display = 'block';
            }
        }

        // Initialize the correct input on page load
        document.addEventListener('DOMContentLoaded', function() {
            toggleInputs();
        });
    </script>
</body>
</html>