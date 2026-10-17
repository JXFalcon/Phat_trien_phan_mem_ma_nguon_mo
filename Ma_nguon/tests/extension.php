<?php
require __DIR__ . '/helper.php';
require __DIR__ . '/../src/RegistrationPolicy.php';
$p = new RegistrationPolicy();
same($p->decide(10, 9, true, true), 'confirmed', 'available');
same($p->decide(10, 10, true, true), 'waiting', 'full-wait');
same($p->decide(10, 10, true, false), 'rejected', 'full-no-wait');
same($p->decide(10, 2, false, true), 'rejected', 'closed');
same($p->decide(0, 0, true, true), 'rejected', 'invalid-capacity');
same($p->decide(10, -1, true, true), 'rejected', 'invalid-count');
same($p->decide(10, 11, true, true), 'rejected', 'corrupt-over-count');
same($p->decide(1, 0, true, false), 'confirmed', 'first-seat');
// Ba ca mở rộng, bổ sung nhằm làm rõ thứ tự ưu tiên của RegistrationPolicy:
// - one-seat-no-wait: khi vẫn còn một chỗ trống, kết quả phải là confirmed
//   bất kể cờ danh sách chờ, cho thấy nhánh "còn chỗ" không phụ thuộc waitingEnabled.
// - closed-full: sự kiện đã đóng phải bị rejected ngay cả khi đã hết chỗ và
//   có bật danh sách chờ, chứng minh nhánh "đóng" được xét trước nhánh "hết chỗ".
// - closed-invalid-data: dữ liệu không hợp lệ vẫn bị rejected kể cả khi sự kiện
//   đồng thời đóng, hợp nhất hai nhánh từ chối độc lập trong cùng một ca.
same($p->decide(10, 9, true, false), 'confirmed', 'one-seat-no-wait');
same($p->decide(10, 10, false, true), 'rejected', 'closed-full');
same($p->decide(0, 0, false, true), 'rejected', 'closed-invalid-data');
finish();
