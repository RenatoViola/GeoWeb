<?php

require_once __DIR__ . '/api.php';

geoweb_endpoint('POST', function (): array {
    $values = geoweb_marker($_POST) + geoweb_mortality_values($_POST);
    $statement = geoweb_db()->prepare(<<<'SQL'
        UPDATE public.avc_mortalidade AS records
        SET "Regiao" = :region, "Instituicao" = :institution,
            "Mortalidade" = :hemorragic, "Mortalidade_1" = :ischemic
        FROM public.avc_mortalidade AS marker
        WHERE marker.id = :marker_id
          AND marker."Periodo" LIKE :marker_period
          AND records."Periodo" LIKE :record_period
          AND records."Instituicao" IS NOT DISTINCT FROM marker."Instituicao"
          AND records."Regiao" IS NOT DISTINCT FROM marker."Regiao"
          AND records.geom = marker.geom
        SQL
    );
    $statement->execute($values);
    return geoweb_marker_result($statement, 'Successful edit');
});
