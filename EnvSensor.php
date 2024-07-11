<?php
class Read {
 public $link = '';

 function __construct($temperature, $humidity, $airQuality) {
  $this->connect();
  $this->storeInDB($temperature, $humidity, $airQuality);
 }

 function connect() {
  $this->link = mysqli_connect('localhost', 'root', '', 'environmentsensor') or die('Cannot connect to the DB');
 }

 function storeInDB($temperature, $humidity, $airQuality){
    // Get current timestamp
    $timestamp = date('Y-m-d H:i:s');
    // Escape values to prevent SQL injection
    $temperature = mysqli_real_escape_string($this->link, $temperature);
    $humidity = mysqli_real_escape_string($this->link, $humidity);
    $airQuality = mysqli_real_escape_string($this->link, $airQuality);
    // Insert query with timestamp
    $query = "INSERT INTO `read` (temperature, humidity, airQuality, timestamp)
    VALUES ('$temperature', '$humidity', '$airQuality', '$timestamp')";
    $result = mysqli_query($this->link, $query) or die('Errant query: '.mysqli_error($this->link));
 }

}

// Check if the required parameters are set
if (isset($_GET['temperature']) && isset($_GET['humidity']) && isset($_GET['airQuality'])) {
 $temperature = $_GET['temperature'];
 $humidity = $_GET['humidity'];
 $airQuality = $_GET['airQuality'];
 $read = new Read($temperature, $humidity, $airQuality);
}
?>
