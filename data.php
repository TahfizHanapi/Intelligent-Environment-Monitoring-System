<?php
header('Access-Control-Allow-Origin: *'); // Allow cross-origin requests
header('Content-Type: application/json'); // Set content type to JSON

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "environmentsensor";

// Create connection
$connect = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($connect->connect_error) {
    die("Connection failed: " . $connect->connect_error);
}

// Fetch the last 100 records ordered by date
$query = "SELECT temperature, humidity, airQuality, timestamp FROM `read` ORDER BY timestamp";
$result = $connect->query($query);

if (!$result) {
    die('Error executing query: ' . $connect->error);
}

// Prepare data array
$data = array();
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

// Return data as JSON
echo json_encode($data);

// Close the database connection
$connect->close();
?>
