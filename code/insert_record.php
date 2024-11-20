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
    //Received Parameters
    $year = $_POST['year'];
    $region = $_POST['region'];
    $institution = $_POST['institution'];
    $hemorragic = $_POST['hemorragic'];
    $ischemic = $_POST['ischemic'];
    $latitude = $_POST['latitude'];
    $longitude = $_POST['longitude'];
    
    $geom = "POINT($longitude $latitude)";

    $stmt = $pdo->prepare("
        INSERT INTO $table (tempo, regiao, instituicao, mortalidade, mortalidade_1, geom) 
        VALUES (:year, :region, :institution, :hemorragic, :ischemic, ST_GeomFromText(:geom, 4326))
    ");

    $stmt->bindParam(':year', $year);
    $stmt->bindParam(':region', $region);
    $stmt->bindParam(':institution', $institution);
    $stmt->bindParam(':hemorragic', $hemorragic);
    $stmt->bindParam(':ischemic', $ischemic);
    $stmt->bindParam(':geom', $geom);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Successful insertion']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Could not insert']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}
?>
