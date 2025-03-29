<?php
session_start();
require 'db.php';

// Ensure student is logged in
if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'Student') {
    header('Location: index.php');
    exit;
}

$rollno = $_SESSION['user'];
$message = "";
$messageType = ""; // For styling success/error messages

// Fetch logged-in student's details
$studentQuery = $conn->query("SELECT * FROM students WHERE rollno='$rollno'");
$student = $studentQuery->fetch_assoc();
$student_name = $student['name'];  // Needed for leave_applications table

// Handle leave application form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $professor = $conn->real_escape_string($_POST['professor']);
    $reason = $conn->real_escape_string($_POST['reason']);
    $documentPath = "";

    // Handle file upload (optional)
    if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] == UPLOAD_ERR_OK) {
        $uploadDir = "uploads/";

        // Ensure the uploads directory exists
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $fileName = time() . "_" . basename($_FILES['attachment']['name']); // Prevent overwriting
        $documentPath = $uploadDir . $fileName;

        if (!move_uploaded_file($_FILES['attachment']['tmp_name'], $documentPath)) {
            $message = "Error moving uploaded file.";
            $messageType = "error";
            $documentPath = ""; // Reset the path if file move fails
        }
    } elseif ($_FILES['attachment']['error'] !== UPLOAD_ERR_NO_FILE) {
        $message = "File upload error: " . $_FILES['attachment']['error'];
        $messageType = "error";
    }

    // Insert into leave_applications table
    $sql = "INSERT INTO leave_applications (student_rollno, student_name, professor_name, explanation, document, status)
            VALUES ('$rollno', '$student_name', '$professor', '$reason', '$documentPath', 'Pending')";

    if ($conn->query($sql)) {
        $message = "Leave application submitted successfully!";
        $messageType = "success";
    } else {
        $message = "Error submitting leave application: " . $conn->error;
        $messageType = "error";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit Leave Application</title>
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
            flex-direction: column;
            align-items: center;
            padding: 20px;
            margin-bottom: 20px;
            text-align: center;
        }

        .profile-photo {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 15px;
            border: 3px solid var(--secondary);
        }

        .sidebar-menu {
            list-style: none;
            padding: 0;
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
            padding: 2rem;
            width: calc(100% - 250px);
            min-height: 100vh;
            background-color: #f8f9fa;
            display: flex;
            flex-direction: column;
        }

        .card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            padding: 2rem;
            width: 100%;
            max-width: 1000px; /* Increased from 800px */
            margin: 0 auto; /* Center the card */
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

        select, textarea, input[type="file"] {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
            margin-bottom: 15px;
            background-color: white;
        }

        textarea {
            min-height: 120px;
            resize: vertical;
        }

        button {
            background-color: var(--secondary);
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            transition: background 0.3s;
        }

        button:hover {
            background-color: #2980b9;
        }

        .alert {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        @media (max-width: 768px) {
            .dashboard-container {
                flex-direction: column;
            }
            .sidebar {
                width: 100%;
                position: relative;
                height: auto;
            }
            .main-content {
                margin-left: 0;
                width: 100%;
                padding: 1.5rem;
            }
        }
        .card {
            width: 100%;
            max-width: none; /* Removes the 800px restriction */
            /* ... keep other existing card styles ... */
        }
    </style>
</head>
<body>

<div class="dashboard-container">
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-profile">
            <img src="<?= htmlspecialchars($student['profile_photo'] ?? 'uploads/default.png') ?>" 
                 class="profile-photo" 
                 alt="Profile Photo"
                 onerror="this.src='uploads/default.png'">
            <h3><?= htmlspecialchars($student['name'] ?? '') ?></h3>
            <p>Roll No: <?= htmlspecialchars($student['rollno'] ?? '') ?></p>
        </div>
        <ul class="sidebar-menu">
            <li><a href="student_dashboard.php">Home</a></li>
            <li><a href="view_profile.php">View Profile</a></li>
            <li><a href="mark_attendance.php">Mark Attendance</a></li>
            <li><a href="view_attendance.php">View Attendance</a></li>
            <!-- <li><a href="submit_leave.php" class="active">Submit Leave</a></li> -->
            <li><a href="leaveStatus.php">Leave Status</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="card">
            <h2>Submit Leave Application</h2>
            
            <?php if ($message): ?>
                <div class="alert alert-<?= $messageType ?>"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>
            
            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="professor">Select Professor</label>
                    <select name="professor" id="professor" required>
                        <option value="">Select Professor</option>
                        <?php
                        $facultyQuery = $conn->query("SELECT name FROM faculty");
                        while ($row = $facultyQuery->fetch_assoc()) {
                            echo "<option value='" . htmlspecialchars($row['name']) . "'>" . htmlspecialchars($row['name']) . "</option>";
                        }
                        ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="reason">Reason for Leave</label>
                    <textarea name="reason" id="reason" rows="4" required></textarea>
                </div>
                
                <div class="form-group">
                    <label for="attachment">Upload Attachment (optional)</label>
                    <input type="file" name="attachment" id="attachment">
                </div>

                <button type="submit">Submit Leave Application</button>
            </form>
        </div>
    </div>
</div>

</body>
</html>