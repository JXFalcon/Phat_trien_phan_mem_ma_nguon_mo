# Lab 02 — Xây dựng module php kiểm tra điều kiện đăng ký

Mã mẫu cho phần hướng dẫn và mã khởi đầu cho bài tự làm.

## Mở bài

Mở terminal Ubuntu/WSL tại thư mục chứa `lab.sh`. Yêu cầu môi trường:

- Terminal Ubuntu/WSL.
- PHP từ 8.1 trở lên.
- Git.

Hướng dẫn chi tiết, sơ đồ, đoạn mã cần sửa, dữ liệu đối chiếu và rubric nằm trong tài liệu của bài lab. Chương trình in kết quả ngay trong terminal.

Bài này không cần PostgreSQL, Composer hoặc framework.

## Lệnh chạy và kiểm thử

Script `lab.sh` điều khiển toàn bộ bài:

```bash
bash lab.sh check     # kiểm tra môi trường
bash lab.sh run       # chạy chương trình mẫu, in kết quả ra terminal
bash lab.sh test      # kiểm tra phần mẫu
bash lab.sh extend    # kiểm tra phần tự mở rộng
```

Có thể kiểm tra cú pháp và chạy kiểm thử trực tiếp bằng PHP:

```bash
php -l src/CapacityPolicy.php
php -l src/RegistrationPolicy.php
php tests/run.php
php tests/extension.php
```

## Kết quả kiểm thử mong đợi

Phần mẫu:

- `bash lab.sh run` in `AVAILABLE` với đầu vào (10, 9).
- `bash lab.sh test` cho `TESTS=5 FAILED=0`.

Phần tự mở rộng:

- `bash lab.sh extend` cho `TESTS=11 FAILED=0` (8 ca được cấp + 3 ca bổ sung).

## Cấu trúc mã nguồn

| Tệp | Vai trò |
| --- | --- |
| `src/CapacityPolicy.php` | Quy tắc mẫu xét còn chỗ, trả Boolean |
| `src/RegistrationPolicy.php` | Phần tự mở rộng, trả `confirmed` / `waiting` / `rejected` |
| `demo.php` | Chương trình gọi lớp mẫu, in kết quả ra terminal |
| `tests/run.php` | Bộ kiểm thử phần mẫu |
| `tests/extension.php` | Bộ kiểm thử phần tự mở rộng |
| `tests/helper.php` | Hàm so sánh kết quả, không sửa |
| `lab.sh` | Script điều khiển bài |

## Quy tắc nghiệp vụ

`RegistrationPolicy::decide()` trả một trong ba trạng thái `confirmed`, `waiting` hoặc `rejected`. Thứ tự ưu tiên: kiểm tra tính hợp lệ dữ liệu và trạng thái đóng trước, rồi mới xét số chỗ.

| Điều kiện | Kết quả |
| --- | --- |
| Sức chứa ≤ 0, số đã đăng ký < 0 hoặc > sức chứa | rejected |
| Dữ liệu hợp lệ nhưng sự kiện đã đóng | rejected |
| Sự kiện mở và còn chỗ | confirmed |
| Sự kiện mở, hết chỗ, có danh sách chờ | waiting |
| Sự kiện mở, hết chỗ, không có danh sách chờ | rejected |

## Kiểm tra bài

```bash
bash lab.sh test
bash lab.sh extend
```

`test` kiểm tra phần mẫu; `extend` kiểm tra phần tự làm. Các vị trí TODO là nhiệm vụ cần hoàn thiện; không sửa test để bỏ qua lỗi.

Kết quả mở rộng cần đạt: `TESTS=11 FAILED=0`. Có lớp kiểm tra sức chứa, chương trình gọi lớp và lớp mở rộng trả confirmed, waiting hoặc rejected theo quy tắc đã công bố.

## Tệp nộp

Giữ mã nguồn, dữ liệu mẫu và kiểm thử. Ghi lại lệnh chạy trong README. Không đưa `.local`, mật khẩu hoặc dữ liệu cá nhân thật vào kho mã và gói nộp. Nộp đúng commit theo yêu cầu của bài; nếu dùng Actions, đính kèm lần chạy tương ứng.
