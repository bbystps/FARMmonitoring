<?php
include("db_conn.php");

try {
    // Create a new PDO instance
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Get data from the form
    $sensor_type = $_POST['sensor_type'];
    $sensor_id = $_POST['sensor_id'];

    // Check if the sensor_id already exists in the sensor_type table
    $checkSql = "SELECT COUNT(*) FROM $sensor_type WHERE sensor_id = :sensor_id";
    $checkStmt = $pdo->prepare($checkSql);
    $checkStmt->bindParam(':sensor_id', $sensor_id);
    $checkStmt->execute();

    $sensorExists = $checkStmt->fetchColumn();

    if ($sensorExists > 0) {
        // Sensor ID already exists
        throw new Exception("Sensor ID '$sensor_id' already exists in the '$sensor_type' table.");
    }

    if ($sensor_type === "sensor_env_soil") {
        $createTableQueries = [
            "CREATE TABLE IF NOT EXISTS {$sensor_id} (
                id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                temperature varchar(8) NOT NULL,
                humidity varchar(8) NOT NULL,
                heat_index varchar(8) NOT NULL,
                soil_moisture varchar(8) NOT NULL,
                uv_level varchar(8) NOT NULL,
                timestamp DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;"
        ];
    } else if ($sensor_type === "sensor_water") {
        $createTableQueries = [
            "CREATE TABLE IF NOT EXISTS {$sensor_id} (
                id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                ph_level varchar(8) NOT NULL,
                tds varchar(8) NOT NULL,
                timestamp DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;"
        ];
    }

    $queryResults = [];
    foreach ($createTableQueries as $query) {
        $result = $pdo->exec($query);
        $queryResults[] = ($result !== false);
        error_log("Executed query: " . $query);
    }

    // Check if all table creation queries succeeded
    if (in_array(false, $queryResults, true)) {
        throw new Exception('One or more table creation queries failed');
    }

    // Prepare and execute the insert statement
    $sql = "INSERT INTO $sensor_type (sensor_id) VALUES (:sensor_id)";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':sensor_id', $sensor_id);

    if (!$stmt->execute()) {
        throw new Exception('Failed to add device');
    }

    // Success
    echo json_encode(['status' => 'success', 'message' => 'Device added and tables created successfully']);
} catch (Exception $e) {
    error_log("Error: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
