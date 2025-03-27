<?php
// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require 'db.php';

// Check if user is logged in and is a student
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Student') {
    header('Location: index.php');
    exit;
}

// Check database connection
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->error);
}

$rollno = $_SESSION['user'];

// Get student details
$student_stmt = $conn->prepare("SELECT name, profile_photo FROM students WHERE rollno=?");
if (!$student_stmt) {
    die("Error preparing student query: " . $conn->error);
}
$student_stmt->bind_param("s", $rollno);
$student_stmt->execute();
$student_result = $student_stmt->get_result();
$student = ($student_result->num_rows > 0) ? $student_result->fetch_assoc() : ['name' => 'Unknown', 'profile_photo' => 'uploads/default.png'];

// Get all leave applications for this student - modified to match your schema
$leave_query = "SELECT 
    la.id, 
    la.student_rollno, 
    la.student_name, 
    la.professor_name, 
    la.explanation, 
    la.document, 
    la.status,
    la.created_at
FROM leave_applications la
WHERE la.student_rollno = ? 
ORDER BY la.created_at DESC";

$leave_stmt = $conn->prepare($leave_query);
if (!$leave_stmt) {
    die("Error preparing leave query: " . $conn->error);
}
$leave_stmt->bind_param("s", $rollno);
$leave_stmt->execute();
$leave_applications = $leave_stmt->get_result();

// Filter handling
$filter_status = isset($_GET['filter_status']) ? $conn->real_escape_string($_GET['filter_status']) : '';
$filter_professor = isset($_GET['filter_professor']) ? $conn->real_escape_string($_GET['filter_professor']) : '';

// Build filtered query if needed
if (!empty($filter_status) || !empty($filter_professor)) {
    $leave_query = "SELECT 
        la.id, 
        la.student_rollno, 
        la.student_name, 
        la.professor_name, 
        la.explanation, 
        la.document, 
        la.status,
        la.created_at
    FROM leave_applications la
    WHERE la.student_rollno = ?";
    
    if (!empty($filter_status)) {
        $leave_query .= " AND la.status = ?";
    }
    
    if (!empty($filter_professor)) {
        $leave_query .= " AND la.professor_name = ?";
    }
    
    $leave_query .= " ORDER BY la.created_at DESC";
    
    $leave_stmt = $conn->prepare($leave_query);
    
    // Bind parameters based on which filters are set
    if (!empty($filter_status) && !empty($filter_professor)) {
        $leave_stmt->bind_param("sss", $rollno, $filter_status, $filter_professor);
    } elseif (!empty($filter_status)) {
        $leave_stmt->bind_param("ss", $rollno, $filter_status);
    } elseif (!empty($filter_professor)) {
        $leave_stmt->bind_param("ss", $rollno, $filter_professor);
    } else {
        $leave_stmt->bind_param("s", $rollno);
    }
    
    $leave_stmt->execute();
    $leave_applications = $leave_stmt->get_result();
}

// Get unique professors for filter dropdown
$professors_query = $conn->query("SELECT DISTINCT professor_name FROM leave_applications WHERE student_rollno = '$rollno'");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - Leave Applications</title>
    <style>
        :root {
            --primary: #2c3e50;
            --secondary: #3498db;
            --light: #ecf0f1;
            --dark: #34495e;
            --success: #2ecc71;
            --danger: #e74c3c;
            --warning: #f39c12;
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
            width: 100%;
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
            padding: 12px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
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
        }

        tr:hover {
            background-color: #f9f9f9;
        }

        .status-pending {
            color: var(--warning);
            font-weight: 600;
        }

        .status-approved {
            color: var(--success);
            font-weight: 600;
        }

        .status-rejected {
            color: var(--danger);
            font-weight: 600;
        }

        .alert {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
        }

        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
        }

        .document-link {
            color: var(--secondary);
            text-decoration: none;
        }

        .document-link:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>
    <div class="dashboard-container">
        <div class="sidebar">
            <div class="sidebar-header">
                <h2>Student Dashboard</h2>
            </div>
            <div class="sidebar-profile">
                <img src="<?php echo htmlspecialchars($student['profile_photo']); ?>" alt="Profile" class="profile-photo">
                <div>
                    <p><?php echo htmlspecialchars($student['name']); ?></p>
                    <small>Student</small>
                </div>
            </div>
            <ul class="sidebar-menu">
            <li><a href="student_dashboard.php">Home</a></li>
            <li><a href="view_profile.php">View Profile</a></li>
            <li><a href="mark_attendance.php">Mark Attendance</a></li>
            <li><a href="view_attendance.php">View Attendance</a></li>
            <li><a href="submit_leave.php">Submit Leave</a></li>
            <!-- <li><a href="leaveStatus.php">Leave Status</a></li> -->
            <li><a href="logout.php">Logout</a></li>
        </ul>
        </div>

        <div class="main-content">
            <h1>My Leave Applications</h1>

            <!-- Filter Form -->
            <div class="card">
                <h3>Filter Applications</h3>
                <form method="GET">
                    <div class="form-group">
                        <label for="filter_status">Status</label>
                        <select name="filter_status" id="filter_status">
                            <option value="">All Statuses</option>
                            <option value="Pending" <?php echo ($filter_status == 'Pending') ? 'selected' : ''; ?>>Pending</option>
                            <option value="Approved" <?php echo ($filter_status == 'Approved') ? 'selected' : ''; ?>>Approved</option>
                            <option value="Rejected" <?php echo ($filter_status == 'Rejected') ? 'selected' : ''; ?>>Rejected</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="filter_professor">Professor</label>
                        <select name="filter_professor" id="filter_professor">
                            <option value="">All Professors</option>
                            <?php while ($professor = $professors_query->fetch_assoc()) : ?>
                                <option value="<?php echo htmlspecialchars($professor['professor_name']); ?>" 
                                    <?php echo ($filter_professor == $professor['professor_name']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($professor['professor_name']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <button type="submit">Apply Filters</button>
                    <?php if (!empty($filter_status) || !empty($filter_professor)) : ?>
                        <a href="student_dashboard.php" class="btn" style="margin-left: 10px; background-color: var(--danger);">Clear Filters</a>
                    <?php endif; ?>
                </form>
            </div>

            <!-- Leave Applications Table -->
            <div class="card">
                <h3>Application History</h3>
                <?php if ($leave_applications->num_rows > 0) : ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Professor</th>
                                <th>Application Date</th>
                                <th>Explanation</th>
                                <th>Document</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($application = $leave_applications->fetch_assoc()) : ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($application['professor_name']); ?></td>
                                    <td><?php echo htmlspecialchars(date('M d, Y', strtotime($application['created_at']))); ?></td>
                                    <td><?php echo htmlspecialchars($application['explanation']); ?></td>
                                    <td>
                                        <?php if (!empty($application['document'])) : ?>
                                            <a href="<?php echo htmlspecialchars($application['document']); ?>" class="document-link" target="_blank">View Document</a>
                                        <?php else : ?>
                                            <em>No document</em>
                                        <?php endif; ?>
                                    </td>
                                    <td class="status-<?php echo strtolower($application['status']); ?>">
                                        <?php echo htmlspecialchars($application['status']); ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else : ?>
                    <p>No leave applications found.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>