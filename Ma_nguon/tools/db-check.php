<?php

declare(strict_types=1);
try {
    require __DIR__ . '/../src/Database.php';
    $c = db();
    $r = queryParams($c, 'SELECT DATABASE() AS db, CURRENT_USER() AS u, VERSION() AS v', []);
    if (!$r instanceof mysqli_result) {
        throw new RuntimeException('DATABASE_OPERATION_FAILED');
    }
    $v = mysqli_fetch_assoc($r);
    if (!is_array($v)) {
        throw new RuntimeException('DATABASE_OPERATION_FAILED');
    }
    echo 'CSDL=' . $v['db'] . ' | USER=' . $v['u'] . ' | PHIEN_BAN=' . $v['v'] . "\n";
} catch (Throwable $e) {
    fwrite(STDERR, 'CHUA_SAN_SANG_CSDL: ' . $e->getMessage() . "\nChạy bash lab.sh db-config sau khi tạo CSDL lab.\n");
    exit(2);
}
