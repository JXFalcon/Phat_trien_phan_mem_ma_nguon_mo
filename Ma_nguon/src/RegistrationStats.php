<?php

declare(strict_types=1);
require_once __DIR__ . '/Database.php';
function registrationStats(mysqli $connection, int $eventId): array
{
    if ($eventId <= 0) {
        return [];
    }
    // LEFT JOIN để sự kiện chưa có đăng ký vẫn xuất hiện; COUNT(r.id) chỉ đếm
    // khoá đăng ký thật nên trả 0 thay vì 1 khi chưa có ai đăng ký.
    $result = queryParams(
        $connection,
        'SELECT e.id AS event_id, e.title AS title, COUNT(r.id) AS total
         FROM events e
         LEFT JOIN registrations r ON r.event_id = e.id
         WHERE e.id = ?
         GROUP BY e.id, e.title',
        [$eventId]
    );
    if (!$result instanceof mysqli_result) {
        throw new RuntimeException('DATABASE_OPERATION_FAILED');
    }
    $row = mysqli_fetch_assoc($result);
    if (!is_array($row)) {
        return [];
    }
    return [
        'event_id' => (int)$row['event_id'],
        'title' => (string)$row['title'],
        'total' => (int)$row['total']
    ];
}
