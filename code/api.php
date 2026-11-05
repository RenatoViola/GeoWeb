<?php

function geoweb_db(): PDO
{
    $configPath = getenv('GEOWEB_DB_CONFIG') ?: dirname(__DIR__, 2) . '/.geoweb-db.php';
    if (!is_readable($configPath)) {
        throw new RuntimeException('GeoWeb database configuration is missing.');
    }
    $config = require $configPath;

    $dsn = 'pgsql:host=postgresql-renato-viola.alwaysdata.net;port=5432;dbname=renato-viola_geoweb_db';
    return new PDO($dsn, $config['username'], $config['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
}

function geoweb_endpoint(string $method, callable $handle): void
{
    header('Content-Type: application/json; charset=utf-8');
    try {
        if ($_SERVER['REQUEST_METHOD'] !== $method) {
            header('Allow: ' . $method);
            http_response_code(405);
            $response = ['status' => 'error', 'message' => 'Invalid request method.'];
        } elseif ($method === 'POST' && getenv('GEOWEB_READ_ONLY') === '1') {
            http_response_code(403);
            $response = ['status' => 'error', 'message' => 'The local server is in read-only mode.'];
        } else {
            $response = $handle();
        }
        echo json_encode($response, JSON_THROW_ON_ERROR);
    } catch (InvalidArgumentException $error) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => $error->getMessage()]);
    } catch (Throwable $error) {
        error_log('GeoWeb: ' . $error->getMessage());
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'The database request failed.']);
    }
}

function geoweb_year(array $input): string
{
    $year = $input['year'] ?? '';
    if (!is_string($year) || !preg_match('/\A[0-9]{4}\z/', $year)) {
        throw new InvalidArgumentException('Provide a four-digit year.');
    }
    return $year;
}

function geoweb_number(array $input, string $field, float $min, float $max): float
{
    $value = filter_var($input[$field] ?? null, FILTER_VALIDATE_FLOAT);
    if ($value === false || !is_finite($value) || $value < $min || $value > $max) {
        throw new InvalidArgumentException('Invalid value for ' . $field . '.');
    }
    return $value;
}

function geoweb_mortality_values(array $input): array
{
    $values = [];
    foreach (['institution', 'region'] as $field) {
        $value = $input[$field] ?? '';
        if (!is_string($value) || !preg_match('/\A.{1,255}\z/us', trim($value))) {
            throw new InvalidArgumentException('Provide a valid ' . $field . '.');
        }
        $values[$field] = trim($value);
    }
    $values['hemorragic'] = geoweb_number($input, 'hemorragic', 0, 100);
    $values['ischemic'] = geoweb_number($input, 'ischemic', 0, 100);
    return $values;
}

function geoweb_marker(array $input): array
{
    $year = geoweb_year($input);
    $id = filter_var($input['marker_id'] ?? null, FILTER_VALIDATE_INT, [
        'options' => ['min_range' => 1, 'max_range' => 2147483647],
    ]);
    if ($id === false) {
        throw new InvalidArgumentException('Provide a valid marker ID.');
    }
    return ['marker_id' => $id, 'marker_period' => $year . '%', 'record_period' => $year . '%'];
}

function geoweb_marker_result(PDOStatement $statement, string $message): array
{
    $count = $statement->rowCount();
    if ($count === 0) {
        http_response_code(404);
        return ['status' => 'error', 'message' => 'The selected marker was not found for this year.'];
    }
    return ['status' => 'success', 'message' => $message, 'records_affected' => $count];
}
