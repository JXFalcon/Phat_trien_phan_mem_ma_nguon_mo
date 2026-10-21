<?php

declare(strict_types=1);
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit;
}
if (!extension_loaded('mysqli')) {
    fwrite(STDERR, "THIEU_MYSQLI\n");
    exit(2);
}
mysqli_report(MYSQLI_REPORT_OFF);
function ask(string $label, string $default): string
{
    echo "$label [$default]: ";
    $line = fgets(STDIN);
    if ($line === false) {
        throw new RuntimeException('Khong doc duoc du lieu');
    }
    $value = trim($line);
    return $value === '' ? $default : $value;
}
function connectLab(array $c, bool $withDatabase): mysqli|false
{
    return @mysqli_connect(
        $c['MYSQL_HOST'],
        $c['MYSQL_USER'],
        $c['MYSQL_PWD'],
        $withDatabase ? $c['MYSQL_DATABASE'] : null,
        (int)$c['MYSQL_PORT']
    );
}
try {
    $c = [
        'MYSQL_HOST' => ask('Máy chủ', '127.0.0.1'),
        'MYSQL_PORT' => ask('Cổng', '3306'),
        'MYSQL_DATABASE' => ask('CSDL lab riêng', 'communitypass_lab_v5'),
        'MYSQL_USER' => ask('Tài khoản lab', 'communitypass_lab_v5')
    ];
    if (!preg_match('/^[A-Za-z0-9_]+$/', $c['MYSQL_DATABASE'])) {
        throw new RuntimeException('Ten CSDL chi duoc gom chu, so va dau gach duoi');
    }
    if (!preg_match('/^[A-Za-z0-9_]+$/', $c['MYSQL_USER'])) {
        throw new RuntimeException('Ten tai khoan chi duoc gom chu, so va dau gach duoi');
    }
    if (!ctype_digit($c['MYSQL_PORT']) || (int)$c['MYSQL_PORT'] < 1 || (int)$c['MYSQL_PORT'] > 65535) {
        throw new RuntimeException('Cong khong hop le');
    }
    if (!stream_isatty(STDIN)) {
        throw new RuntimeException('Mo terminal tuong tac de nhap mat khau');
    }
    echo 'Mật khẩu CSDL (không hiển thị): ';
    exec('stty -echo', $out, $rc);
    if ($rc !== 0) {
        throw new RuntimeException('Khong an duoc mat khau');
    }
    try {
        $password = fgets(STDIN);
    } finally {
        exec('stty echo');
        echo "\n";
    }
    if ($password === false || rtrim($password, "\r\n") === '') {
        throw new RuntimeException('Mat khau rong');
    }
    $c['MYSQL_PWD'] = rtrim($password, "\r\n");
    $conn = connectLab($c, true);
    if (!$conn instanceof mysqli) {
        $errno = mysqli_connect_errno();
        $error = mysqli_connect_error();
        if ($errno === 1049) {
            echo 'CSDL chưa tồn tại. Tao moi? (y/N) ';
            $answer = fgets(STDIN);
            if ($answer === false || !in_array(strtolower(trim($answer)), ['y', 'yes'], true)) {
                throw new RuntimeException(
                    "Tao CSDL bang tay:\n"
                    . '  CREATE DATABASE `' . $c['MYSQL_DATABASE'] . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;'
                    . "\n  GRANT ALL PRIVILEGES ON `" . $c['MYSQL_DATABASE'] . "`.* TO '" . $c['MYSQL_USER'] . "'@'localhost';"
                    . "\nRoi chay lai: bash lab.sh db-config"
                );
            }
            $server = connectLab($c, false);
            if (!$server instanceof mysqli) {
                throw new RuntimeException('Khong ket noi duoc may chu de tao CSDL (ma ' . mysqli_connect_errno() . '); chua luu cau hinh');
            }
            $create = 'CREATE DATABASE IF NOT EXISTS `' . $c['MYSQL_DATABASE'] . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci';
            if (!mysqli_query($server, $create)) {
                $createError = mysqli_error($server);
                mysqli_close($server);
                throw new RuntimeException('Khong tao duoc CSDL: ' . $createError . '; chua luu cau hinh');
            }
            mysqli_close($server);
            $conn = connectLab($c, true);
            if (!$conn instanceof mysqli) {
                throw new RuntimeException('Khong ket noi lai duoc CSDL (ma ' . mysqli_connect_errno() . '); chua luu cau hinh');
            }
        } else {
            throw new RuntimeException('Ket noi that bai (ma ' . $errno . '): ' . $error . '; chua luu cau hinh');
        }
    }
    mysqli_set_charset($conn, 'utf8mb4');
    mysqli_close($conn);
    $dir = __DIR__ . '/../.local';
    $old = umask(0077);
    try {
        if (!is_dir($dir) && !mkdir($dir, 0700, true)) {
            throw new RuntimeException('Khong tao duoc thu muc');
        }
        @chmod($dir, 0700);
        $json = json_encode($c, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        if (file_put_contents($dir . '/db.json.tmp', $json) === false) {
            throw new RuntimeException('Khong ghi duoc cau hinh');
        }
        chmod($dir . '/db.json.tmp', 0600);
        if (!rename($dir . '/db.json.tmp', $dir . '/db.json')) {
            throw new RuntimeException('Khong luu duoc cau hinh');
        }
    } finally {
        umask($old);
    }
    echo "CAU_HINH=DA_LUU | Chỉ lưu trên máy này. Không gửi thư mục .local.\n";
} catch (Throwable $e) {
    fwrite(STDERR, $e->getMessage() . "\n");
    exit(2);
}
