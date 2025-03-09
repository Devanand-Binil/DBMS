<?php
session_start();
require 'db.php';

if ($_SESSION['role'] !== 'Admin') {
    header('Location: index.php');
    exit;
}

// Handle Add Faculty
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_faculty'])) {
    $rollno = $_POST['rollno'];
    $name = $_POST['name'];
    $password = $_POST['password'];
    $department = $_POST['department'];
    
    $conn->query("INSERT INTO faculty (rollno, name, password, department) VALUES ('$rollno', '$name', '$password', '$department')");
    header("Location: manage_faculty.php");
    exit;
}

// Handle Update Faculty
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_faculty'])) {
    $id = $_POST['faculty_id'];
    $rollno = $_POST['rollno'];
    $name = $_POST['name'];
    $password = $_POST['password'];
    $department = $_POST['department'];

    $conn->query("UPDATE faculty SET rollno='$rollno', name='$name', password='$password', department='$department' WHERE id='$id'");
    header("Location: manage_faculty.php");
    exit;
}

// Handle Delete Faculty
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $conn->query("DELETE FROM faculty WHERE id='$id'");
    header("Location: manage_faculty.php");
    exit;
}

// Fetch Faculty List
$faculty_list = $conn->query("SELECT * FROM faculty");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Manage Faculty</title>
    <link rel="stylesheet" href="css/styles.css">
    <script>
        function openEditModal(id, rollno, name, password, department) {
            document.getElementById('faculty_id').value = id;
            document.getElementById('faculty_rollno').value = rollno;
            document.getElementById('faculty_name').value = name;
            document.getElementById('faculty_password').value = password;
            document.getElementById('faculty_department').value = department;
            document.getElementById('editModal').style.display = 'block';
        }

        function closeModal() {
            document.getElementById('editModal').style.display = 'none';
            document.getElementById('addModal').style.display = 'none';
        }

        function openAddModal() {
            document.getElementById('addModal').style.display = 'block';
        }
    </script>
    <style>
        body {
            display: flex;
            margin: 0;
            font-family: Arial, sans-serif;
        }
        
        /* Sidebar Styling */
        .sidebar {
            width: 250px;
            height: 100vh;
            background: #333;
            color: white;
            padding: 20px;
            position: fixed;
        }

        .sidebar a {
            display: block;
            color: white;
            padding: 10px;
            text-decoration: none;
            margin-bottom: 5px;
        }

        .sidebar a:hover {
            background: #555;
        }

        .main-content {
            margin-left: 260px;
            padding: 20px;
            width: 100%;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            justify-content: center;
            align-items: center;
        }
        .modal-content {
            background: white;
            padding: 20px;
            border-radius: 10px;
            width: 300px;
        }
        .close-btn {
            background: red;
            color: white;
            padding: 5px 10px;
            border: none;
            cursor: pointer;
        }
        .delete-btn {
            background: red;
            color: white;
            padding: 5px 10px;
            border: none;
            cursor: pointer;
        }
    </style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
    <h2>Admin Panel</h2>
    <a href="admin_dashboard.php">Dashboard</a>
    <a href="manage_students.php">Manage Students</a>
    <a href="manage_faculty.php">Manage Faculty</a>
    <a href="manage_classes.php">Manage Classes</a>
    <a href="logout.php">Logout</a>
</div>

<!-- Main Content -->
<div class="main-content">
    <h2>Manage Faculty</h2>

    <button onclick="openAddModal()">Add Faculty</button>

    <table border="1">
        <tr>
            <th>ID</th>
            <th>Roll No</th>
            <th>Name</th>
            <th>Password</th>
            <th>Department</th>
            <th>Actions</th>
        </tr>
        <?php while ($faculty = $faculty_list->fetch_assoc()): ?>
            <tr>
                <td><?= $faculty['id'] ?></td>
                <td><?= htmlspecialchars($faculty['rollno']) ?></td>
                <td><?= htmlspecialchars($faculty['name']) ?></td>
                <td><?= htmlspecialchars($faculty['password']) ?></td>
                <td><?= htmlspecialchars($faculty['department']) ?></td>
                <td>
                    <button onclick="openEditModal('<?= $faculty['id'] ?>', '<?= htmlspecialchars($faculty['rollno']) ?>', '<?= htmlspecialchars($faculty['name']) ?>', '<?= htmlspecialchars($faculty['password']) ?>', '<?= htmlspecialchars($faculty['department']) ?>')">Edit</button>
                    <a href="manage_faculty.php?delete=<?= $faculty['id'] ?>" class="delete-btn" onclick="return confirm('Are you sure you want to delete?');">Delete</a>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>

    <!-- Add Faculty Modal -->
    <div id="addModal" class="modal">
        <div class="modal-content">
            <h3>Add Faculty</h3>
            <form method="POST">
                <label>Roll No:</label>
                <input type="text" name="rollno" required>

                <label>Name:</label>
                <input type="text" name="name" required>

                <label>Password:</label>
                <input type="password" name="password" required>

                <label>Department:</label>
                <input type="text" name="department" required>

                <button type="submit" name="add_faculty">Add</button>
                <button type="button" class="close-btn" onclick="closeModal()">Close</button>
            </form>
        </div>
    </div>

    <!-- Edit Faculty Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <h3>Edit Faculty</h3>
            <form method="POST">
                <input type="hidden" id="faculty_id" name="faculty_id">

                <label>Roll No:</label>
                <input type="text" id="faculty_rollno" name="rollno" required>

                <label>Name:</label>
                <input type="text" id="faculty_name" name="name" required>

                <label>Password:</label>
                <input type="text" id="faculty_password" name="password" required>

                <label>Department:</label>
                <input type="text" id="faculty_department" name="department" required>

                <button type="submit" name="update_faculty">Update</button>
                <button type="button" class="close-btn" onclick="closeModal()">Close</button>
            </form>
        </div>
    </div>
</div>

</body>
</html>
