<?php
$table = 'mortalidade';
$dsn = "pgsql:host=postgresql-geoweb.alwaysdata.net;dbname=geoweb_db;port=5432";
$opt = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false
];
$pdo = new PDO($dsn, 'geoweb', '4fqZxzDxjJIpzA9', $opt);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Received Parameters
    $year = $_POST['year'];
    $latitude = $_POST['latitude'];
    $longitude = $_POST['longitude'];
    
    $geom = "POINT($longitude $latitude)";

    // Delete Value
    $stmt = $pdo->prepare("
        DELETE FROM $table 
        WHERE tempo LIKE :year 
        AND ST_DWithin(geom, ST_GeomFromText(:geom, 4326), 0.0001)
    ");

    $yearParam = "%{$year}%";

    $stmt->bindParam(':year', $yearParam);
    $stmt->bindParam(':geom', $geom);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Successful deletion']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Could not delete']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}
?>
