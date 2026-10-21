<?php

declare(strict_types=1);
require_once __DIR__ . '/Database.php';
final class RegistrationRepository
{
    public function __construct(private mysqli $connection) {}
    public function save(int $eventId, string $name, string $email): string
    {
        $email = strtolower(trim($email));
        $name = trim($name);
        if (
            $eventId <= 0 || $name === '' || strlen($name) > 120
            || str_contains($name, "\0") || strlen($email) > 200
            || !filter_var($email, FILTER_VALIDATE_EMAIL)
        ) {
            throw new InvalidArgumentException('REGISTRATION_INVALID');
        }
        $statement = queryParams(
            $this->connection,
            'INSERT INTO registrations (event_id,name,email) VALUES (?,?,?)
             ON DUPLICATE KEY UPDATE event_id = event_id',
            [$eventId, $name, $email]
        );
        if (!$statement instanceof mysqli_stmt) {
            throw new RuntimeException('DATABASE_OPERATION_FAILED');
        }
        // MySQL/MariaDB: chèn mới trả affected_rows = 1; trùng (event_id,email)
        // nhưng giá trị không đổi trả 0. Lỗi khác (ví dụ khoá ngoại) đã ném từ
        // queryParams nên không bao giờ bị quy thành DUPLICATE.
        return mysqli_stmt_affected_rows($statement) === 1 ? 'CREATED' : 'DUPLICATE';
    }
    public function listForEvent(int $eventId): array
    {
        $result = queryParams(
            $this->connection,
            'SELECT id,event_id,name,email FROM registrations WHERE event_id=? ORDER BY id',
            [$eventId]
        );
        return mysqli_fetch_all($result, MYSQLI_ASSOC) ?: [];
    }
}
