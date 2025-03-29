<?php
session_start();
require 'db.php';

if ($_SESSION['role'] !== 'Admin') {
    header('Location: index.php');
    exit;
}

// Handle adding a new class
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_class'])) {
    $class_name = $_POST['class_name'];
    $latitude = $_POST['latitude'];
    $longitude = $_POST['longitude'];

    $conn->query("INSERT INTO classes (class_name, latitude, longitude) VALUES ('$class_name', '$latitude', '$longitude')");
    header("Location: manage_classes.php");
    exit;
}

// Handle updating a class
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_class'])) {
    $id = $_POST['id'];
    $class_name = $_POST['class_name'];
    $latitude = $_POST['latitude'];
    $longitude = $_POST['longitude'];

    $conn->query("UPDATE classes SET class_name='$class_name', latitude='$latitude', longitude='$longitude' WHERE id=$id");
    header("Location: manage_classes.php");
    exit;
}

// Handle deleting a class
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $conn->query("DELETE FROM classes WHERE id=$id");
    header("Location: manage_classes.php");
    exit;
}

// Fetch all classes
$classes = $conn->query("SELECT * FROM classes");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Manage Classes</title>
    <link rel="stylesheet" href="css/styles.css">
    <script>
        function openEditModal(id, class_name, latitude, longitude) {
            document.getElementById('class_id').value = id;
            document.getElementById('class_name').value = class_name;
            document.getElementById('latitude').value = latitude;
            document.getElementById('longitude').value = longitude;
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
    <h2>Manage Classes</h2>

    <button onclick="openAddModal()">Add Class</button>

    <table border="1">
        <tr>
            <th>Class Name</th>
            <th>Latitude</th>
            <th>Longitude</th>
            <th>Actions</th>
        </tr>
        <?php while ($row = $classes->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($row['class_name']) ?></td>
                <td><?= htmlspecialchars($row['latitude']) ?></td>
                <td><?= htmlspecialchars($row['longitude']) ?></td>
                <td>
                    <button onclick="openEditModal('<?= $row['id'] ?>', '<?= htmlspecialchars($row['class_name']) ?>', '<?= htmlspecialchars($row['latitude']) ?>', '<?= htmlspecialchars($row['longitude']) ?>')">Edit</button>
                    <a href="manage_classes.php?delete=<?= $row['id'] ?>" class="delete-btn" onclick="return confirm('Are you sure you want to delete?');">Delete</a>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>

    <!-- Add Class Modal -->
    <div id="addModal" class="modal">
        <div class="modal-content">
            <h3>Add Class</h3>
            <form method="POST">
                <label>Class Name:</label>
                <input type="text" name="class_name" required>

                <label>Latitude:</label>
                <input type="text" name="latitude" required>

                <label>Longitude:</label>
                <input type="text" name="longitude" required>

                <button type="submit" name="add_class">Add</button>
                <button type="button" class="close-btn" onclick="closeModal()">Close</button>
            </form>
        </div>
    </div>

    <!-- Edit Class Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <h3>Edit Class</h3>
            <form method="POST">
                <input type="hidden" id="class_id" name="id">

                <label>Class Name:</label>
                <input type="text" id="class_name" name="class_name" required>

                <label>Latitude:</label>
                <input type="text" id="latitude" name="latitude" required>

                <label>Longitude:</label>
                <input type="text" id="longitude" name="longitude" required>

                <button type="submit" name="update_class">Update</button>
                <button type="button" class="close-btn" onclick="closeModal()">Close</button>
            </form>
        </div>
    </div>
</div>

</body>
</html>
