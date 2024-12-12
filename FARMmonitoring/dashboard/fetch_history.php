<?php
include("db_conn.php");

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sensor_id = isset($_GET['sensor_id']) ? $_GET['sensor_id'] : '';
    $last_id = isset($_GET['last_id']) ? intval($_GET['last_id']) : 0;

    // Check for invalid sensor_id
    if (empty($sensor_id)) {
        echo json_encode(["error" => "Invalid sensor_id"]);
        exit;
    }

    // Verify that the table exists
    $stmt = $pdo->prepare("SHOW TABLES LIKE :sensor_id");
    $stmt->bindParam(':sensor_id', $sensor_id, PDO::PARAM_STR);
    $stmt->execute();
    if ($stmt->rowCount() === 0) {
        echo json_encode(["error" => "Table does not exist"]);
        exit;
    }

    // Fetch data
    $sql = "SELECT * FROM `$sensor_id` WHERE id > :last_id ORDER BY id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':last_id', $last_id, PDO::PARAM_INT);
    $stmt->execute();

    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($data);
} catch (PDOException $e) {
    echo json_encode(["error" => "Connection failed: " . $e->getMessage()]);
}
?>
