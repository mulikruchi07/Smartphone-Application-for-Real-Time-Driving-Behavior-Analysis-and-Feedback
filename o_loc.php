<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');


$servername = "localhost";
$username = "root"; // Your XAMPP MySQL username
$password = "";     // Your XAMPP MySQL password (empty by default)
$dbname = "owner_registration"; // Your Database Name

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection Failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $owner_id = $_POST['owner_id'];
    $car_number = $_POST['car_number'];
    $source = $_POST['source'];
    $destination = $_POST['destination'];
    $latitude = $_POST['latitude'];
    $longitude = $_POST['longitude'];
    $timestamp = date("Y-m-d H:i:s");

    if (!empty($owner_id) && !empty($car_number) && !empty($source) && !empty($destination) && !empty($latitude) && !empty($longitude)) {
        $stmt = $conn->prepare("SELECT * FROM cars WHERE registration_number = ? AND owner_id = ?");
        $stmt->bind_param("si", $car_number, $owner_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $stmt = $conn->prepare("INSERT INTO owner_locations (owner_id, car_registration_number, source_location, destination_location, latitude, longitude, timestamp) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("isssdds", $owner_id, $car_number, $source, $destination, $latitude, $longitude, $timestamp);

            if ($stmt->execute()) {
                echo json_encode(["status" => "success", "message" => "Trip Saved Successfully"]);
            } else {
                echo json_encode(["status" => "error", "message" => "Failed to Save Trip"]);
            }
        } else {
            echo json_encode(["status" => "error", "message" => "Car Registration Number Not Linked with this Owner"]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Incomplete Data"]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid Request"]);
}
?>
