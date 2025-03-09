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
            $documentPath = ""; // Reset the path if file move fails
        }
    } elseif ($_FILES['attachment']['error'] !== UPLOAD_ERR_NO_FILE) {
        $message = "File upload error: " . $_FILES['attachment']['error'];
    }

    // Insert into leave_applications table
    $sql = "INSERT INTO leave_applications (student_rollno, student_name, professor_name, explanation, document, status)
            VALUES ('$rollno', '$student_name', '$professor', '$reason', '$documentPath', 'Pending')";

    if ($conn->query($sql)) {
        $message = "Leave application submitted successfully!";
    } else {
        $message = "Error submitting leave application: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Submit Leave Application</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>

<div class="dashboard-container">

    <!-- Sidebar -->
    <div class="sidebar">
        <div class="profile-section">
            <img src="<?= htmlspecialchars($student['profile_photo']) ?>" class="profile-photo">
            <h3><?= htmlspecialchars($student['name']) ?></h3>
            <p>Roll No: <?= htmlspecialchars($student['rollno']) ?></p>
        </div>
        <ul class="sidebar-menu">
            <li><a href="student_dashboard.php">Home</a></li>
            <li><a href="view_profile.php">View Profile</a></li>
            <li><a href="mark_attendance.php">Mark Attendance</a></li>
            <li><a href="view_attendance.php">View Attendance</a></li>
            <li class="active"><a href="submit_leave.php">Submit Leave</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <h2>Submit Leave Application</h2>

        <?php if ($message): ?>
            <p class="alert"><?= htmlspecialchars($message) ?></p>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <label for="professor">Select Professor:</label>
            <select name="professor" required>
                <option value="">Select Professor</option>
                <?php
                $facultyQuery = $conn->query("SELECT name FROM faculty");
                while ($row = $facultyQuery->fetch_assoc()) {
                    echo "<option value='" . htmlspecialchars($row['name']) . "'>" . htmlspecialchars($row['name']) . "</option>";
                }
                ?>
            </select>

            <label for="reason">Reason for Leave:</label>
            <textarea name="reason" rows="4" required></textarea>

            <label for="attachment">Upload Attachment (optional):</label>
            <input type="file" name="attachment">

            <button type="submit">Submit Leave Application</button>
        </form>
    </div>

</div>

</body>
</html>
