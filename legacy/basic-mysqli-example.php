<?php
// Step 1: Connect to MySQL database
$servername = "localhost";
$username = "root"; // Default username for MySQL
$password = ""; // Default password for MySQL in XAMPP
$dbname = "gis_project"; // Your database name

$conn = new mysqli($servername, $username, $password, $dbname, 3306);


// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// Step 2: Perform a query
$sql = "SELECT id, name, latitude, longitude FROM locations";
$result = $conn->query($sql);

// Step 3: Fetch and display data
if ($result->num_rows > 0) {
  while($row = $result->fetch_assoc()) {
    echo "id: " . $row["id"]. " - Name: " . $row["name"]. " - Location: " . $row["latitude"]. ", " . $row["longitude"]. "<br>";
  }
} else {
  echo "0 results";
}

// Close connection
$conn->close();
?>
