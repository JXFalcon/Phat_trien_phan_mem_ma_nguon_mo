<?php
declare(strict_types=1);
final class RegistrationPolicy {
    public function decide(int $capacity, int $registered,
        bool $open, bool $waitingEnabled): string {
        // Ưu tiên 1: loại bỏ dữ liệu không hợp lệ trước khi xét bất cứ điều gì.
        // capacity phải dương, registered không âm và không vượt sức chứa.
        if ($capacity <= 0 || $registered < 0 || $registered > $capacity) {
            return 'rejected';
        }
        // Ưu tiên 2: sự kiện đã đóng thì luôn bị từ chối, kể cả khi còn chỗ.
        if ($open === false) {
            return 'rejected';
        }
        // Ưu tiên 3: sự kiện mở và còn chỗ trống thì xác nhận đăng ký.
        if ($registered < $capacity) {
            return 'confirmed';
        }
        // Sự kiện mở nhưng đã hết chỗ: phụ thuộc vào việc bật danh sách chờ.
        if ($waitingEnabled === true) {
            return 'waiting';
        }
        return 'rejected';
    }
}
