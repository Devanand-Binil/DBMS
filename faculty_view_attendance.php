<?php
session_start();
require 'db.php';

if ($_SESSION['role'] !== 'Faculty') {
    header('Location: index.php');
    exit;
}

$message = "";
$attendance_records = [];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
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
    <title>View Attendance</title>
    <link rel="stylesheet" href="css/styles.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
        }
        .container {
            max-width: 900px;
            margin: 20px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 5px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        }
        h2 {
            text-align: center;
            margin-bottom: 20px;
        }
        .alert {
            padding: 10px;
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
            border-radius: 5px;
            text-align: center;
        }
        form {
            margin-bottom: 20px;
            text-align: center;
        }
        form select, form input, form button {
            padding: 10px;
            margin: 5px;
            width: 200px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        form button {
            background-color: #007bff;
            color: white;
            border: none;
            cursor: pointer;
        }
        form button:hover {
            background-color: #0056b3;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 10px;
            text-align: center;
        }
        th {
            background-color: #007bff;
            color: white;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>View Attendance Records</h2>

    <?php if ($message): ?>
        <p class="alert"><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

    <form method="POST">
        <label>Filter By:</label>
        <select name="filter_type" required id="filter_type" onchange="toggleInputs()">
            <option value="date">Date</option>
            <option value="rollno">Roll No</option>
            <option value="class">Class</option>
        </select>

        <input type="date" name="date" id="date_input">
        <input type="text" name="rollno" id="rollno_input" style="display:none;" placeholder="Enter Roll No">
        <input type="text" name="class_name" id="class_input" style="display:none;" placeholder="Enter Class Name">

        <button type="submit">Filter</button>
        <button type="submit" name="download_excel">Download Excel</button>
    </form>

    <table>
        <tr>
            <th>Roll No</th>
            <th>Name</th>
            <th>Class</th>
            <th>Date</th>
            <th>Time</th>
            <th>Status</th>
        </tr>
        <?php foreach ($attendance_records as $record): ?>
            <tr>
                <td><?= htmlspecialchars($record['rollno']) ?></td>
                <td><?= htmlspecialchars($record['name']) ?></td>
                <td><?= htmlspecialchars($record['class_name']) ?></td>
                <td><?= htmlspecialchars($record['date']) ?></td>
                <td><?= htmlspecialchars($record['time']) ?></td>
                <td><?= htmlspecialchars($record['status']) ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
</div>

<script>
    function toggleInputs() {
        var filterType = document.getElementById('filter_type').value;
        document.getElementById('date_input').style.display = 'none';
        document.getElementById('rollno_input').style.display = 'none';
        document.getElementById('class_input').style.display = 'none';

        if (filterType === 'date') {
            document.getElementById('date_input').style.display = 'inline-block';
        } else if (filterType === 'rollno') {
            document.getElementById('rollno_input').style.display = 'inline-block';
        } else if (filterType === 'class') {
            document.getElementById('class_input').style.display = 'inline-block';
        }
    }
</script>

</body>
</html>
