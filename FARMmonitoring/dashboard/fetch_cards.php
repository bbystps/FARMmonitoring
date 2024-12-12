<?php
include("db_conn.php");

// $activeSensor = "ES001";
$activeSensor = isset($_GET['activeSensor']) ? $_GET['activeSensor'] : '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Fetch the latest record for the active sensor
    $stmt = $pdo->query("SELECT * FROM $activeSensor ORDER BY id DESC LIMIT 1");
    
    $sensorData = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // If no data is found, return 0
    if (empty($sensorData)) {
        echo json_encode(0);
    } else {
        echo json_encode($sensorData);
    }

} catch (PDOException $e) {
    echo 'Connection failed: ' . $e->getMessage();
}
?>
