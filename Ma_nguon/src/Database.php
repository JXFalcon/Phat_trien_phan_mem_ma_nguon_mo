<?php

declare(strict_types=1);
if (extension_loaded('mysqli')) {
    mysqli_report(MYSQLI_REPORT_OFF);
}
// Dùng lại biến môi trường cho các tiến trình con. Không nạp mã PHP cấu hình.
$configPath = __DIR__ . '/../.local/db.json';
if (is_file($configPath)) {
    $config = json_decode((string)file_get_contents($configPath), true);
    if (is_array($config)) {
        foreach (['MYSQL_HOST', 'MYSQL_PORT', 'MYSQL_DATABASE', 'MYSQL_USER', 'MYSQL_PWD'] as $key) {
            if (isset($config[$key]) && is_string($config[$key])) {
                putenv($key . '=' . $config[$key]);
            }
        }
    }
}
function dbConfig(): array
{
    $configPath = __DIR__ . '/../.local/db.json';
    if (!is_file($configPath)) {
        return [];
    }
    $config = json_decode((string)file_get_contents($configPath), true);
    if (!is_array($config)) {
        throw new RuntimeException('DATABASE_CONFIG_INVALID');
    }
    foreach (['MYSQL_HOST', 'MYSQL_PORT', 'MYSQL_DATABASE', 'MYSQL_USER', 'MYSQL_PWD'] as $key) {
        if (!isset($config[$key]) || !is_string($config[$key])) {
            throw new RuntimeException('DATABASE_CONFIG_INVALID');
        }
    }
    return $config;
}
function db(): mysqli
{
    if (!extension_loaded('mysqli')) {
        throw new RuntimeException('MYSQLI_EXTENSION_MISSING');
    }
    $config = dbConfig();
    foreach (['MYSQL_HOST', 'MYSQL_PORT', 'MYSQL_DATABASE', 'MYSQL_USER', 'MYSQL_PWD'] as $key) {
        if (isset($config[$key])) {
            putenv($key . '=' . $config[$key]);
        }
    }
    foreach (['MYSQL_HOST', 'MYSQL_DATABASE', 'MYSQL_USER'] as $key) {
        if (!getenv($key)) {
            throw new RuntimeException('DATABASE_CONFIG_MISSING');
        }
    }
    $connection = mysqli_init();
    if (!$connection) {
        throw new RuntimeException('DATABASE_UNAVAILABLE');
    }
    mysqli_options($connection, MYSQLI_OPT_CONNECT_TIMEOUT, 3);
    $portEnv = getenv('MYSQL_PORT');
    $port = (ctype_digit((string)$portEnv) ? (int)$portEnv : 3306);
    try {
        $ok = @mysqli_real_connect(
            $connection,
            (string)getenv('MYSQL_HOST'),
            (string)getenv('MYSQL_USER'),
            (string)(getenv('MYSQL_PWD') ?: ''),
            (string)getenv('MYSQL_DATABASE'),
            $port
        );
    } catch (Throwable $e) {
        throw new RuntimeException('DATABASE_UNAVAILABLE: ' . mysqli_connect_errno());
    }
    if (!$ok) {
        throw new RuntimeException('DATABASE_UNAVAILABLE: ' . mysqli_connect_errno());
    }
    if (!mysqli_set_charset($connection, 'utf8mb4')) {
        throw new RuntimeException('DATABASE_UNAVAILABLE: ' . mysqli_errno($connection));
    }
    return $connection;
}
function dbOperationFailure(mysqli $connection): RuntimeException
{
    return new RuntimeException('DATABASE_OPERATION_FAILED: ' . mysqli_errno($connection) . ': ' . mysqli_error($connection));
}
function queryParams(mysqli $connection, string $sql, array $params): mysqli_result|mysqli_stmt
{
    try {
        $statement = mysqli_prepare($connection, $sql);
        if (!$statement) {
            throw dbOperationFailure($connection);
        }
        if ($params) {
            $types = '';
            foreach ($params as $p) {
                $types .= is_int($p) ? 'i' : (is_float($p) ? 'd' : 's');
            }
            if (!mysqli_stmt_bind_param($statement, $types, ...$params)) {
                throw dbOperationFailure($connection);
            }
        }
        if (!mysqli_stmt_execute($statement)) {
            throw dbOperationFailure($connection);
        }
        if (mysqli_stmt_field_count($statement) > 0) {
            $result = mysqli_stmt_get_result($statement);
            if (!$result) {
                throw dbOperationFailure($connection);
            }
            return $result;
        }
        return $statement;
    } catch (Throwable $e) {
        if ($e instanceof RuntimeException && str_starts_with($e->getMessage(), 'DATABASE_OPERATION_FAILED')) {
            throw $e;
        }
        throw dbOperationFailure($connection);
    }
}
