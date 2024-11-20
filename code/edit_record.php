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
    // Query Parameters
    $year = $_GET['year'];
    $latitude = $_GET['latitude'];
    $longitude = $_GET['longitude'];

    // Received Parameters
    $region = $_POST['region'];
    $institution = $_POST['institution'];
    $hemorragic = $_POST['hemorragic'];
    $ischemic = $_POST['ischemic'];
    
    // Get Geographic Location
    $geom = "POINT($longitude $latitude)";

    // Edit Value
    $stmt = $pdo->prepare("
        UPDATE $table 
        SET 
            regiao = :region, 
            instituicao = :institution, 
            mortalidade = :hemorragic, 
            mortalidade_1 = :ischemic 
        WHERE tempo LIKE :year
        AND ST_DWithin(geom, ST_GeomFromText(:geom, 4326), 0.0001)
    ");

    $yearParam = "%{$year}%";

    $stmt->bindParam(':year', $yearParam);
    $stmt->bindParam(':region', $region);
    $stmt->bindParam(':institution', $institution);
    $stmt->bindParam(':hemorragic', $hemorragic);
    $stmt->bindParam(':ischemic', $ischemic);
    $stmt->bindParam(':geom', $geom);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Successful edit']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Could not edit']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}
?>
