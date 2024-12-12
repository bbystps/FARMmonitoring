<?php
include("db_conn.php");

//Will fetch json format region without duplicate for sidebar data

$nav = isset($_GET['nav']) ? $_GET['nav'] : '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Fetch unique site locations
    $stmt = $pdo->query("SELECT sensor_id FROM $nav");
    $siteLocations = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Return as JSON
    echo json_encode($siteLocations);
} catch (PDOException $e) {
    echo 'Connection failed: ' . $e->getMessage();
}
?>
