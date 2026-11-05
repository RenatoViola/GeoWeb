<?php

require_once __DIR__ . '/api.php';

geoweb_endpoint('GET', function (): array {
    $year = geoweb_year($_GET);
    $statement = geoweb_db()->prepare(<<<'SQL'
        SELECT
            MIN(id) AS marker_id,
            "Instituicao" AS instituicao,
            "Regiao" AS regiao,
            AVG("Mortalidade") AS avg_mortalidade_hemorragica,
            AVG("Mortalidade_1") AS avg_mortalidade_isquemica,
            ST_AsGeoJSON(ST_GeometryN(geom, 1), 5) AS geojson
        FROM public.avc_mortalidade
        WHERE "Periodo" LIKE :period
        GROUP BY "Instituicao", "Regiao", geom
        SQL
    );
    $statement->execute(['period' => $year . '%']);

    $features = [];
    foreach ($statement as $row) {
        $geometry = json_decode($row['geojson'], false, 512, JSON_THROW_ON_ERROR);
        unset($row['geojson']);
        $features[] = ['type' => 'Feature', 'geometry' => $geometry, 'properties' => $row];
    }
    return ['type' => 'FeatureCollection', 'features' => $features];
});
