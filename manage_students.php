<?php
session_start();
require 'db.php';

if ($_SESSION['role'] !== 'Admin') {
    header('Location: index.php');
    exit;
}

// Handle adding a new student
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_student'])) {
    $rollno = $_POST['rollno'];
    $name = $_POST['name'];
    $password = $_POST['password'];
    $class = $_POST['class'];
    $department = $_POST['department'];
    
    $conn->query("INSERT INTO students (rollno, name, password, class, department) VALUES ('$rollno', '$name', '$password', '$class', '$department')");
    header("Location: manage_students.php");
    exit;
}

// Handle updating a student
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_student'])) {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $class = $_POST['class'];
    $department = $_POST['department'];
    
    $conn->query("UPDATE students SET name='$name', class='$class', department='$department' WHERE id=$id");
    header("Location: manage_students.php");
    exit;
}

// Handle deleting a student
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $conn->query("DELETE FROM students WHERE id=$id");
    header("Location: manage_students.php");
    exit;
}

// Fetch all students
$students = $conn->query("SELECT * FROM students");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Manage Students</title>
    <link rel="stylesheet" href="css/styles.css">
    <script>
        function openEditModal(id, rollno, name, class_name, department) {
            document.getElementById('student_id').value = id;
            document.getElementById('student_rollno').value = rollno;
            document.getElementById('student_name').value = name;
            document.getElementById('student_class').value = class_name;
            document.getElementById('student_department').value = department;
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
            background: #2c3e50;
            color: #fff;
            padding: 1.5rem;
            position: fixed;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
        }

        .sidebar a {
            display: block;
            color: #ecf0f1;
            padding: 0.75rem;
            text-decoration: none;
            margin-bottom: 0.5rem;
            border-radius: 4px;
            transition: all 0.2s ease;
        }

        .sidebar a:hover {
            background: #34495e;
            transform: translateX(5px);
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
        /* Replace your existing table styles with this */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
            border-radius: 8px;
        }

        table th {
            background: #2c3e50;
            color: white;
            padding: 12px 15px;
            text-align: left;
        }

        table td {
            padding: 12px 15px;
            border-bottom: 1px solid #e0e0e0;
        }

        table tr:last-child td {
            border-bottom: none;
        }

        table tr:hover {
            background-color: #f5f5f5;
        }

        /* Button styles for table actions */
        table button, table .delete-btn {
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.2s;
        }

        table button {
            background: #3498db;
            color: white;
            margin-right: 5px;
        }

        table button:hover {
            background: #2980b9;
        }

        table .delete-btn {
            background: #e74c3c;
            color: white;
            text-decoration: none;
            display: inline-block;
        }

        table .delete-btn:hover {
            background: #c0392b;
        }

        /* Responsive table for smaller screens */
        @media (max-width: 768px) {
            table {
                display: block;
                overflow-x: auto;
            }
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
    <h2>Manage Students</h2>

    <button onclick="openAddModal()">Add Student</button>

    <table border="1">
        <tr>
            <th>Roll No</th>
            <th>Name</th>
            <th>Class</th>
            <th>Department</th>
            <th>Actions</th>
        </tr>
        <?php while ($row = $students->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($row['rollno']) ?></td>
                <td><?= htmlspecialchars($row['name']) ?></td>
                <td><?= htmlspecialchars($row['class']) ?></td>
                <td><?= htmlspecialchars($row['department']) ?></td>
                <td>
                    <button onclick="openEditModal('<?= $row['id'] ?>', '<?= htmlspecialchars($row['rollno']) ?>', '<?= htmlspecialchars($row['name']) ?>', '<?= htmlspecialchars($row['class']) ?>', '<?= htmlspecialchars($row['department']) ?>')">Edit</button>
                    <a href="manage_students.php?delete=<?= $row['id'] ?>" class="delete-btn" onclick="return confirm('Are you sure you want to delete?');">Delete</a>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>

    <!-- Add Student Modal -->
    <div id="addModal" class="modal">
        <div class="modal-content">
            <h3>Add Student</h3>
            <form method="POST">
                <label>Roll No:</label>
                <input type="text" name="rollno" required>

                <label>Name:</label>
                <input type="text" name="name" required>

                <label>Password:</label>
                <input type="password" name="password" required>

                <label>Class:</label>
                <input type="text" name="class" required>

                <label>Department:</label>
                <input type="text" name="department" required>

                <button type="submit" name="add_student">Add</button>
                <button type="button" class="close-btn" onclick="closeModal()">Close</button>
            </form>
        </div>
    </div>

    <!-- Edit Student Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <h3>Edit Student</h3>
            <form method="POST">
                <input type="hidden" id="student_id" name="id">

                <label>Roll No:</label>
                <input type="text" id="student_rollno" name="rollno" readonly>

                <label>Name:</label>
                <input type="text" id="student_name" name="name" required>

                <label>Class:</label>
                <input type="text" id="student_class" name="class" required>

                <label>Department:</label>
                <input type="text" id="student_department" name="department" required>

                <button type="submit" name="update_student">Update</button>
                <button type="button" class="close-btn" onclick="closeModal()">Close</button>
            </form>
        </div>
    </div>
</div>

</body>
</html>
