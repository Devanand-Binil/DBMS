<?php
session_start();
require 'db.php';

if ($_SESSION['role'] !== 'Student') {
    header('Location: index.php');
    exit;
}

$rollno = $_SESSION['user'];
$message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $code = $_POST['class_code'];
    $lat = $_POST['latitude'];
    $long = $_POST['longitude'];

    // Get class location from class_sessions table
    $class_query = $conn->query("SELECT * FROM class_sessions WHERE code='$code' AND status='active'");

    if ($class_query->num_rows > 0) {
        $class = $class_query->fetch_assoc();

        $class_lat = $class['latitude'];
        $class_long = $class['longitude'];

        $distance = haversine($lat, $long, $class_lat, $class_long);

        if ($distance <= 25) {
            $class_id_result = $conn->query("SELECT id FROM classes WHERE class_name = '{$class['class_name']}'");
if ($class_id_result->num_rows > 0) {
    $class_id = $class_id_result->fetch_assoc()['id'];
    $conn->query("INSERT INTO attendance (rollno,class_id, class_code ,date, time, status) VALUES ('$rollno','$class_id','$code', CURDATE(), CURTIME(), 'Present')");
}
$message = "Attendance marked successfully!";

        } else {
            $message = "You are not within 100 meters of the class location. Distance: {$distance} meters.";
        }
    } else {
        $message = "Invalid or expired class code.";
    }
}

function haversine($lat1, $lon1, $lat2, $lon2) {
    $earth_radius = 6371000; // meters
    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);

    $a = sin($dLat/2) * sin($dLat/2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon/2) * sin($dLon/2);
    $c = 2 * atan2(sqrt($a), sqrt(1-$a));
    return $earth_radius * $c;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Mark Attendance</title>
    <link rel="stylesheet" href="css/styles.css">
    <script>
        function getLocation() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(position => {
                    document.getElementById('latitude').value = position.coords.latitude;
                    document.getElementById('longitude').value = position.coords.longitude;
                }, () => alert('Geolocation failed! Enable location.'));
            } else {
                alert('Geolocation is not supported.');
            }
        }
        window.onload = getLocation;
    </script>
    <style>
        /* Basic Layout for Sidebar + Content */
        .dashboard-container {
            display: flex;
            height: 100vh;
            margin: 0;
        }
        .sidebar {
            width: 250px;
            background-color: #007bff;
            color: white;
            padding: 20px;
            box-sizing: border-box;
            flex-shrink: 0;
        }
        .sidebar h3 {
            margin: 0 0 15px;
        }
        .sidebar .profile-photo {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 10px;
        }
        .sidebar a {
            display: block;
            padding: 10px;
            margin: 5px 0;
            color: white;
            text-decoration: none;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 5px;
            text-align: center;
        }
        .sidebar a:hover, .sidebar .active {
            background: rgba(255, 255, 255, 0.4);
        }
        .main-content {
            flex-grow: 1;
            padding: 30px;
            box-sizing: border-box;
            overflow-y: auto;
        }
        .alert {
            padding: 10px;
            background-color: #d4edda;
            color: #155724;
            margin-bottom: 15px;
            border: 1px solid #c3e6cb;
            border-radius: 5px;
        }
        form label {
            display: block;
            margin: 10px 0 5px;
        }
        form input, form button {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            box-sizing: border-box;
        }
        form button {
            background-color: #28a745;
            color: white;
            border: none;
            cursor: pointer;
        }
        form button:hover {
            background-color: #218838;
        }
    </style>
</head>
<body>

<div class="dashboard-container">
    <!-- Sidebar -->
    <div class="sidebar">
        <h3>Student Dashboard</h3>
        <img src="uploads/default.png" class="profile-photo"> <!-- You can load student photo if you want -->
        <p>Roll No: <?= htmlspecialchars($rollno) ?></p>
        <a href="student_dashboard.php">Home</a>
        <a href="view_profile.php">View Profile</a>
        <a href="mark_attendance.php" class="active">Mark Attendance</a>
        <a href="view_attendance.php">View Attendance</a>
        <a href="submit_leave.php">Submit Leave</a>
        <a href="logout.php">Logout</a>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <h2>Mark Attendance</h2>

        <?php if ($message): ?>
            <p class="alert"><?= htmlspecialchars($message) ?></p>
        <?php endif; ?>

        <form method="POST">
            <label>Class Code</label>
            <input type="text" name="class_code" required>

            <input type="hidden" id="latitude" name="latitude">
            <input type="hidden" id="longitude" name="longitude">

            <button type="submit">Mark Attendance</button>
        </form>
    </div>
</div>

</body>
</html>
