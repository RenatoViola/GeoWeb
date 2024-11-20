<?php
$table='mortalidade';
$dsn = "pgsql:host=postgresql-geoweb.alwaysdata.net;dbname=geoweb_db;port=5432";
$opt = [
PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
PDO::ATTR_EMULATE_PREPARES => false
];
$pdo = new PDO($dsn, 'geoweb', '4fqZxzDxjJIpzA9', $opt);

$year=$_GET['year'];

$result = $pdo->query("
    SELECT 
        instituicao, regiao,
        AVG(mortalidade) AS avg_mortalidade_hemorragica,
        AVG(mortalidade_1) AS avg_mortalidade_isquemica,
        ST_AsGeoJSON(geom, 5) AS geojson
    FROM {$table}
    WHERE tempo LIKE '%{$year}%'
    GROUP BY instituicao, regiao, geom
");

$features=[];
foreach($result AS $row){
    unset($row['geom']);
    $geometry=json_decode($row['geojson']);
    unset($row['geojson']);
    $feature=["type"=>"Feature", "geometry"=> $geometry, "properties"=>$row];
    array_push($features, $feature);
}

$featureCollection=["type"=>"FeatureCollection", "features"=>$features];
echo json_encode($featureCollection);
?>