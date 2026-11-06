<?php

require_once __DIR__ . '/api.php';

geoweb_endpoint('GET', function (): array {
    $district = $_GET['district'] ?? '';
    if (!is_string($district) || !preg_match('/\A.{1,80}\z/u', trim($district))) {
        throw new InvalidArgumentException('Selecione um distrito.');
    }

    $statement = geoweb_db()->prepare(<<<'SQL'
        SELECT ST_YMin(bounds) AS south, ST_XMin(bounds) AS west,
               ST_YMax(bounds) AS north, ST_XMax(bounds) AS east
        FROM (
            SELECT ST_Extent(geom) AS bounds
            FROM public.portugal_distritos
            WHERE "Official_Na" = :district AND "Type" = 'district'
        ) AS district_extent
        SQL
    );
    $statement->execute(['district' => trim($district)]);
    $row = $statement->fetch();

    if ($row === false || $row['south'] === null) {
        http_response_code(404);
        return ['status' => 'error', 'message' => 'Distrito não encontrado.'];
    }

    return [
        'bounds' => [
            [(float) $row['south'], (float) $row['west']],
            [(float) $row['north'], (float) $row['east']],
        ],
    ];
});
