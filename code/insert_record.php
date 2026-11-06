<?php

require_once __DIR__ . '/api.php';

geoweb_endpoint('POST', function (): array {
    $values = geoweb_mortality_values($_POST) + [
        'year' => geoweb_year($_POST),
        'latitude' => geoweb_number($_POST, 'latitude', -90, 90),
        'longitude' => geoweb_number($_POST, 'longitude', -180, 180),
    ];
    $statement = geoweb_db()->prepare(<<<'SQL'
        INSERT INTO public.avc_mortalidade ("Periodo", "Regiao", "Instituicao", "Mortalidade", "Mortalidade_1", geom)
        VALUES (:year, :region, :institution, :hemorragic, :ischemic, ST_SetSRID(ST_MakePoint(:longitude, :latitude), 4326))
        SQL
    );
    $statement->execute($values);
    return ['status' => 'success', 'message' => 'Successful insertion'];
});
