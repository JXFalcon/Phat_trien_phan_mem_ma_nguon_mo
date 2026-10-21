<?php

declare(strict_types=1);
function printResult(mysqli_result $result): void
{
    $headers = [];
    foreach (mysqli_fetch_fields($result) as $field) {
        $headers[] = $field->name;
    }
    echo implode("\t", $headers) . "\n";
    $count = 0;
    while (is_array($row = mysqli_fetch_row($result))) {
        echo implode("\t", array_map(
            static fn(mixed $value): string => $value === null ? 'NULL' : (string)$value,
            $row
        )) . "\n";
        $count++;
    }
    mysqli_free_result($result);
    echo '(' . $count . " dong)\n";
}
function drainResults(mysqli $connection): void
{
    while (mysqli_more_results($connection)) {
        mysqli_next_result($connection);
        $result = mysqli_store_result($connection);
        if ($result instanceof mysqli_result) {
            mysqli_free_result($result);
        }
    }
}
function printError(mysqli $connection): void
{
    fwrite(STDERR, 'ERROR ' . mysqli_errno($connection) . ': ' . mysqli_error($connection) . "\n");
}
function runSql(mysqli $connection, string $sql): void
{
    if (trim($sql) === '') {
        return;
    }
    if (!mysqli_multi_query($connection, $sql)) {
        printError($connection);
        drainResults($connection);
        return;
    }
    do {
        if (mysqli_field_count($connection) > 0) {
            $result = mysqli_store_result($connection);
            if ($result instanceof mysqli_result) {
                printResult($result);
            }
        } else {
            echo 'OK. ' . mysqli_affected_rows($connection) . " dong anh huong.\n";
        }
    } while (mysqli_more_results($connection) && mysqli_next_result($connection));
    if (mysqli_errno($connection) > 0) {
        printError($connection);
    }
    drainResults($connection);
}
function resolvePath(string $path, string $root): string
{
    if ($path === '') {
        return $path;
    }
    if ($path[0] === '/' || preg_match('/^[A-Za-z]:[\\\\\/]/', $path)) {
        return $path;
    }
    return $root . '/' . $path;
}
function reportUnreadable(string $path, string $root): void
{
    fwrite(STDERR, 'Khong doc duoc tep: ' . $path . "\n");
    $dir = $root . '/database';
    if (!is_dir($dir)) {
        return;
    }
    $files = glob($dir . '/*.sql');
    if (!is_array($files) || $files === []) {
        return;
    }
    $names = array_map(static fn(string $file): string => basename($file), $files);
    sort($names);
    fwrite(STDERR, 'Cac tep SQL co san trong database/: ' . implode(', ', $names) . "\n");
}
try {
    require __DIR__ . '/../src/Database.php';
    if (function_exists('mysqli_report')) {
        mysqli_report(MYSQLI_REPORT_OFF);
    }
    $connection = db();
} catch (Throwable $e) {
    fwrite(STDERR, "Chưa kết nối được CSDL.\n");
    exit(2);
}
$dbName = '';
$dbResult = mysqli_query($connection, 'SELECT DATABASE() AS db');
if ($dbResult instanceof mysqli_result) {
    $dbRow = mysqli_fetch_assoc($dbResult);
    $dbName = is_array($dbRow) ? (string)($dbRow['db'] ?? '') : '';
    mysqli_free_result($dbResult);
}
echo 'CSDL: ' . $dbName . "\n";
echo "Gõ câu lệnh SQL kết thúc bằng ';'. Dùng 'source <đường dẫn tệp>' hoặc '\\i <đường dẫn>' để nạp tệp SQL. Gõ 'quit' để thoát.\n";
$root = dirname(__DIR__);
$buffer = '';
while (($line = fgets(STDIN)) !== false) {
    $trimmed = trim($line);
    $lower = strtolower($trimmed);
    if ($lower === 'quit' || $lower === '\\q' || $lower === 'exit') {
        exit(0);
    }
    if ($trimmed === '') {
        continue;
    }
    if (preg_match('/^(?:source|\x5ci)\s+(.+)$/i', $trimmed, $match)) {
        $rawPath = rtrim(trim($match[1]), " \t;");
        $path = resolvePath($rawPath, $root);
        if (!is_file($path) || !is_readable($path)) {
            reportUnreadable($path, $root);
            continue;
        }
        $content = file_get_contents($path);
        if ($content === false) {
            reportUnreadable($path, $root);
            continue;
        }
        $buffer = '';
        runSql($connection, $content);
        echo 'Da nap: ' . $rawPath . "\n";
        continue;
    }
    $buffer .= $line;
    if (str_ends_with(rtrim($buffer), ';')) {
        $sql = trim($buffer);
        $buffer = '';
        if ($sql !== ';') {
            runSql($connection, $sql);
        }
    }
}
if (trim($buffer) !== '') {
    runSql($connection, $buffer);
}
exit(0);
