<?php
$conn = new mysqli('sql105.infinityfree.com:3306','if0_38641676','devanand2003','if0_38641676_attendance_db');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
