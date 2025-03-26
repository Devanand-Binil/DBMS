<?php
// Enable error reporting for debugging (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start secure session
session_start([
    'cookie_httponly' => true,
    'cookie_secure' => isset($_SERVER['HTTPS']),
    'use_strict_mode' => true
]);

// Check if user is logged in as student
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Student') {
    header('Location: index.php');
    exit;
}

// Validate roll number
if (!isset($_SESSION['user']) || !preg_match('/^[a-zA-Z0-9]+$/', $_SESSION['user'])) {
    die("Invalid session data. Please login again.");
}
$rollno = $_SESSION['user'];

// Database connection
require 'db.php';
if ($conn->connect_error) {
    error_log("Database connection failed: " . $conn->connect_error);
    die("System temporarily unavailable. Please try again later.");
}

$message = "";

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die("Security verification failed. Please try again.");
    }

    // Validate required fields
    if (!isset($_POST['class_code'], $_POST['latitude'], $_POST['longitude'])) {
        $message = "All fields are required.";
    } else {
        // Sanitize and validate inputs
        $code = trim($conn->real_escape_string($_POST['class_code']));
        $lat = filter_var($_POST['latitude'], FILTER_VALIDATE_FLOAT);
        $long = filter_var($_POST['longitude'], FILTER_VALIDATE_FLOAT);
        $accuracy = isset($_POST['accuracy']) ? filter_var($_POST['accuracy'], FILTER_VALIDATE_FLOAT) : 0;

        // Validate coordinates
        if ($lat === false || $long === false) {
            $message = "Invalid location data received.";
        } elseif (abs($lat) > 90 || abs($long) > 180) {
            $message = "Invalid geographic coordinates.";
        } elseif ($lat == 0 && $long == 0) {
            $message = "Location not detected. Please enable location services.";
        } elseif ($accuracy > 100) {
            $message = "Location accuracy too low (".round($accuracy)."m). Try moving to an open area.";
        } else {
            // Begin transaction for atomic operations
            $conn->begin_transaction();
            
            try {
                // Get active class session (using your class_sessions table)
                $class_query = $conn->prepare("SELECT * FROM class_sessions WHERE code=? AND status='active'");
                if (!$class_query) {
                    throw new Exception("Database error: " . $conn->error);
                }
                
                $class_query->bind_param("s", $code);
                $class_query->execute();
                $result = $class_query->get_result();

                if ($result->num_rows > 0) {
                    $class = $result->fetch_assoc();
                    $class_lat = floatval($class['latitude']);
                    $class_long = floatval($class['longitude']);

                    // Calculate distance using Haversine formula
                    $distance = haversine($lat, $long, $class_lat, $class_long);

                    if ($distance <= 25) {
                        // Get class ID (from your classes table)
                        $class_id_query = $conn->prepare("SELECT id FROM classes WHERE class_name = ?");
                        $class_id_query->bind_param("s", $class['class_name']);
                        $class_id_query->execute();
                        $class_id_data = $class_id_query->get_result();
                        
                        if ($class_id_data->num_rows > 0) {
                            $class_id = $class_id_data->fetch_assoc()['id'];
                            
                            // Check for existing attendance record today (using your attendance table)
                            $check_attendance = $conn->prepare("SELECT id FROM attendance WHERE rollno=? AND class_id=? AND date=CURDATE()");
                            $check_attendance->bind_param("si", $rollno, $class_id);
                            $check_attendance->execute();
                            
                            if ($check_attendance->get_result()->num_rows > 0) {
                                $message = "Attendance already recorded today.";
                            } else {
                                // Record new attendance (matches your attendance table structure)
                                $insert = $conn->prepare("INSERT INTO attendance (rollno, class_id, date, time, status) 
                                                        VALUES (?, ?, CURDATE(), CURTIME(), 'Present')");
                                $insert->bind_param("si", $rollno, $class_id);
                                
                                if ($insert->execute()) {
                                    $conn->commit();
                                    $message = "Attendance marked successfully!";
                                } else {
                                    throw new Exception("Failed to record attendance: " . $conn->error);
                                }
                            }
                        } else {
                            $message = "Class not found in registry.";
                            $conn->rollback();
                        }
                    } else {
                        $message = "You are ".round($distance, 2)." meters away (must be within 25m).";
                    }
                } else {
                    $message = "Invalid or expired class code.";
                }
            } catch (Exception $e) {
                $conn->rollback();
                error_log("Attendance system error: " . $e->getMessage());
                $message = "System error. Please try again.";
            }
        }
    }
}

// Generate CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Haversine distance calculation (unchanged)
function haversine($lat1, $lon1, $lat2, $lon2) {
    $earth_radius = 6371000; // meters
    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);

    $a = sin($dLat/2) * sin($dLat/2) + 
         cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * 
         sin($dLon/2) * sin($dLon/2);
    $c = 2 * atan2(sqrt($a), sqrt(1-$a));
    return $earth_radius * $c;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mark Attendance</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
        }
        .dashboard-container {
            display: flex;
            min-height: 100vh;
        }
        .sidebar {
            width: 250px;
            background-color: #2c3e50;
            color: white;
            padding: 20px;
            box-shadow: 2px 0 5px rgba(0,0,0,0.1);
        }
        .main-content {
            flex: 1;
            padding: 30px;
            background-color: white;
        }
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
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
        .location-info {
            background-color: #e9ecef;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        form {
            max-width: 500px;
        }
        input, button {
            padding: 10px;
            margin: 5px 0;
            width: 100%;
            box-sizing: border-box;
        }
        button {
            background-color: #28a745;
            color: white;
            border: none;
            cursor: pointer;
        }
        button.secondary {
            background-color: #6c757d;
        }
        #map {
            height: 300px;
            width: 100%;
            margin-top: 20px;
            border-radius: 4px;
        }
    </style>
</head>
<body>
<div class="dashboard-container">
    <div class="sidebar">
        <h3>Student Dashboard</h3>
        <img src="uploads/default.png" style="width:80px;height:80px;border-radius:50%;margin-bottom:15px;">
        <p>Roll No: <?= htmlspecialchars($rollno) ?></p>
        <a href="student_dashboard.php">Home</a>
        <a href="view_profile.php">View Profile</a>
        <a href="mark_attendance.php" style="background:rgba(255,255,255,0.4);">Mark Attendance</a>
        <a href="view_attendance.php">View Attendance</a>
        <a href="submit_leave.php">Submit Leave</a>
        <a href="logout.php">Logout</a>
    </div>

    <div class="main-content">
        <h2>Mark Attendance</h2>

        <?php if ($message): ?>
            <div class="alert <?= strpos($message, 'successfully') !== false ? 'alert-success' : 'alert-error' ?>">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <div class="location-info">
            <p id="location-status">Detecting your location...</p>
            <p>Latitude: <span id="lat-display">-</span></p>
            <p>Longitude: <span id="long-display">-</span></p>
            <p>Accuracy: <span id="accuracy-display">-</span> meters</p>
            <p id="location-method">Using: -</p>
        </div>

        <div id="map"></div>

        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
            
            <label for="class_code">Class Code</label>
            <input type="text" id="class_code" name="class_code" required pattern="[A-Za-z0-9]{6,12}" title="6-12 alphanumeric characters">

            <input type="hidden" id="latitude" name="latitude">
            <input type="hidden" id="longitude" name="longitude">
            <input type="hidden" id="accuracy" name="accuracy">

            <button type="button" id="refresh-location" class="secondary">Refresh Location</button>
            <button type="submit">Mark Attendance</button>
        </form>
    </div>
</div>

<script>
// Location detection with multiple fallback methods
const locationMethods = [
    { name: "High Accuracy GPS", options: { enableHighAccuracy: true, timeout: 10000 } },
    { name: "Device Sensors", options: { enableHighAccuracy: false, timeout: 15000 } },
    { name: "Network Location", options: { enableHighAccuracy: false, timeout: 20000 } }
];

let currentMethodIndex = 0;
let bestPosition = null;
let watchId = null;

function getLocation() {
    document.getElementById('location-status').textContent = "Detecting your location...";
    document.getElementById('location-method').textContent = "Using: " + locationMethods[currentMethodIndex].name;
    
    if (!navigator.geolocation) {
        showError("Geolocation not supported by your browser");
        return;
    }

    // Clear any previous watcher
    if (watchId !== null) {
        navigator.geolocation.clearWatch(watchId);
    }

    // Try current method
    tryLocationMethod();
}

function tryLocationMethod() {
    watchId = navigator.geolocation.watchPosition(
        position => handlePositionSuccess(position),
        error => handlePositionError(error),
        locationMethods[currentMethodIndex].options
    );

    // Set timeout to try next method if this one fails
    setTimeout(() => {
        if (!bestPosition) {
            navigator.geolocation.clearWatch(watchId);
            currentMethodIndex = (currentMethodIndex + 1) % locationMethods.length;
            if (currentMethodIndex === 0) {
                showError("All location methods failed");
            } else {
                getLocation();
            }
        }
    }, locationMethods[currentMethodIndex].options.timeout + 2000);
}

function handlePositionSuccess(position) {
    // Validate position
    if (Math.abs(position.coords.latitude) < 0.0001 && 
        Math.abs(position.coords.longitude) < 0.0001) {
        return; // Ignore (0,0) coordinates
    }

    // Track best position found
    if (!bestPosition || position.coords.accuracy < bestPosition.coords.accuracy) {
        bestPosition = position;
        updateLocationDisplay(position);
    }

    // If we have good accuracy, stop trying other methods
    if (position.coords.accuracy <= 30) {
        navigator.geolocation.clearWatch(watchId);
    }
}

function handlePositionError(error) {
    const errors = {
        1: "Permission denied. Please enable location access.",
        2: "Location unavailable. Try moving to a different area.",
        3: "Timeout occurred. Trying alternative method..."
    };
    
    if (error.code === 3) { // Timeout
        currentMethodIndex = (currentMethodIndex + 1) % locationMethods.length;
        if (currentMethodIndex === 0 && !bestPosition) {
            showError("All location methods failed");
        } else {
            getLocation();
        }
    } else {
        showError(errors[error.code] || "Location error occurred");
    }
}

function updateLocationDisplay(position) {
    document.getElementById('latitude').value = position.coords.latitude;
    document.getElementById('longitude').value = position.coords.longitude;
    document.getElementById('accuracy').value = position.coords.accuracy;
    
    document.getElementById('lat-display').textContent = position.coords.latitude.toFixed(6);
    document.getElementById('long-display').textContent = position.coords.longitude.toFixed(6);
    document.getElementById('accuracy-display').textContent = Math.round(position.coords.accuracy);
    
    const status = document.getElementById('location-status');
    if (position.coords.accuracy <= 20) {
        status.textContent = "High accuracy location confirmed";
        status.style.color = "green";
    } else if (position.coords.accuracy <= 50) {
        status.textContent = "Medium accuracy location confirmed";
        status.style.color = "orange";
    } else {
        status.textContent = "Low accuracy location detected";
        status.style.color = "red";
    }
    
    updateMap(position.coords.latitude, position.coords.longitude);
}

function updateMap(lat, lng) {
    const mapElement = document.getElementById('map');
    if (typeof google === 'undefined') return;
    
    if (!window.map) {
        window.map = new google.maps.Map(mapElement, {
            center: { lat, lng },
            zoom: 18
        });
        window.marker = new google.maps.Marker({
            position: { lat, lng },
            map: window.map,
            title: "Your Location"
        });
    } else {
        window.map.setCenter({ lat, lng });
        window.marker.setPosition({ lat, lng });
    }
}

function showError(message) {
    document.getElementById('location-status').textContent = message;
    document.getElementById('location-status').style.color = "red";
}

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    getLocation();
    
    document.getElementById('refresh-location').addEventListener('click', function() {
        bestPosition = null;
        currentMethodIndex = 0;
        getLocation();
    });
});

// Optional: Load Google Maps if available
// function loadGoogleMaps() {
//     const script = document.createElement('script');
//     script.src = `https://maps.googleapis.com/maps/api/js?key=YOUR_API_KEY&callback=initMap`;
//     script.async = true;
//     script.defer = true;
//     document.head.appendChild(script);
// }

// Uncomment to enable maps
// loadGoogleMaps();
</script>
</body>
</html>