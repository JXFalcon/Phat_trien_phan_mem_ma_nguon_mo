# Lab05 — Bài thực hành dành cho sinh viên

Nhánh này chỉ chứa một bài lab. Dùng Ubuntu/WSL, PHP từ 8.1 và Git.

**Lưu ý về cơ sở dữ liệu:** `HUONG_DAN.md` và `Huong_dan.docx` là tài liệu đề bài gốc viết cho PostgreSQL. Mã nguồn trong `Ma_nguon/` đã chuyển sang **MySQL/MariaDB** dùng `mysqli` để phù hợp với máy thực hành. Các bước nghiệp vụ trong đề bài vẫn áp dụng; chỉ công cụ và cú pháp CSDL thay đổi. Xem mục [Khác biệt với đề bài gốc](#khác-biệt-với-đề-bài-gốc-postgresql) bên dưới.

## Chuẩn bị trên Ubuntu/WSL

```bash
sudo apt update
sudo apt install php-cli git unzip mariadb-server php8.5-mysql
sudo service mariadb start
php -v
php -m | grep mysqli
git --version
```

PHP cần từ 8.1 trở lên. Gói `php8.5-mysql` phải khớp phiên bản `php -v`; máy thực hành đang dùng PHP 8.5. Nếu `php -v` là phiên bản khác, cài gói `phpX.Y-mysql` tương ứng. Kết quả `php -m | grep mysqli` phải có dòng `mysqli`. Các lệnh bên dưới chạy trong Ubuntu/WSL. Khi tải nhiều bài, trở về thư mục cha chứa các thư mục lab trước mỗi lệnh clone; không clone bài tiếp theo bên trong `Ma_nguon` của bài trước.

## Tải bài

```bash
git clone --single-branch --branch lab5 https://github.com/FIT-DNU/Open-Source-Software-Development.git lab5
cd lab5/Ma_nguon
```

## Tạo CSDL MySQL/MariaDB riêng trước lần chạy đầu

Tại `lab5/Ma_nguon`, khởi động dịch vụ nếu chưa chạy:

```bash
sudo service mariadb start
```

Mở trình khách quản trị cục bộ bằng `sudo mariadb` (hoặc `sudo mysql` nếu máy dùng MySQL). Chỉ chạy các lệnh `CREATE` khi tên chưa tồn tại; không dùng CSDL production và không xóa CSDL có sẵn.

```sql
CREATE DATABASE communitypass_lab_v5 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'communitypass_lab_v5'@'localhost' IDENTIFIED BY 'mat-khau-cua-ban';
GRANT ALL PRIVILEGES ON communitypass_lab_v5.* TO 'communitypass_lab_v5'@'localhost';
FLUSH PRIVILEGES;
```

Khi gõ, thay `mat-khau-cua-ban` bằng mật khẩu riêng. Chỉ dùng mật khẩu ở lời nhắc hoặc lệnh tạo tài khoản; không ghi mật khẩu thật vào tài liệu, ảnh chụp, tệp `.sql` hay Git. Nếu CSDL hoặc tài khoản đã có, hỏi giảng viên trước khi dùng lại hoặc tạo tên riêng cho nhóm.

## Cấu hình kết nối và tạo schema

Tại `lab5/Ma_nguon`, chạy:

```bash
bash lab.sh db-config
bash lab.sh check
bash lab.sh sql
```

Khi `db-config` hỏi, nhấn Enter để dùng máy chủ `127.0.0.1`, cổng `3306`, CSDL và tài khoản `communitypass_lab_v5`, hoặc nhập thông tin được cấp. Công cụ chỉ lưu cấu hình sau khi kết nối thành công. Cấu hình nằm ở `.local/db.json` với quyền `0600`; không commit thư mục này.

Trong phiên SQL vừa mở, nạp schema rồi kiểm tra dữ liệu mẫu:

```sql
source database/schema.sql
SELECT id, title FROM events ORDER BY id;
quit
```

`source` và `\i` là lệnh của công cụ `bash lab.sh sql`, không phải câu SQL, nên không thêm dấu `;` sau đường dẫn tệp. Có thể dùng `\i database/schema.sql` thay cho `source database/schema.sql`. Nếu gõ `source database/schema.sql;`, công cụ coi dấu `;` là một phần tên tệp và báo `Khong doc duoc tep`; bỏ dấu `;` rồi thử lại. Kết quả phải có 3 sự kiện.

## Chạy demo dòng lệnh

Sau khi đã cấu hình và tạo schema, chạy hai lần:

```bash
php demo-db.php
php demo-db.php
```

Với CSDL mới, lần đầu in `CREATED`, lần hai in `DUPLICATE`. Nếu dữ liệu demo đã tồn tại từ lần trước, cả hai lần có thể là `DUPLICATE`; đó không phải lỗi.

Kiểm chứng bằng SQL, không chỉ nhìn thông báo PHP:

```bash
bash lab.sh sql
```

```sql
SELECT event_id,email,COUNT(*) FROM registrations GROUP BY event_id,email;
quit
```

Cần thấy đúng một hàng cho cặp sự kiện–email của `demo@example.test`.

## Chạy bài

```bash
bash lab.sh check
bash lab.sh run
```

Mở [danh sách sự kiện](http://127.0.0.1:8105/) hoặc [form đăng ký](http://127.0.0.1:8105/register.php). Giữ terminal mở; Ctrl+C để dừng.

## Kiểm tra

Mở terminal thứ hai tại `Ma_nguon`, hoặc dừng máy chủ trước:

```bash
bash lab.sh test
bash lab.sh extend
```

`test` kiểm tra phần mẫu, gồm `tests/run.php` và `tests/integration.php`: kỳ vọng `TESTS=6 FAILED=0` và `TESTS=7 FAILED=0`. `extend` kiểm tra phần tự làm: kỳ vọng `TESTS=4 FAILED=0` sau khi hoàn thiện `src/RegistrationStats.php`. Ở bản khởi đầu, `extend` có thể còn ca chưa đạt vì TODO.

`bash lab.sh check` chỉ kiểm tra môi trường, không thay cho `test` và `extend`. Nếu kết quả có `NOT_RUN`, nghĩa là kiểm thử chưa thực thi do thiếu kết nối hoặc cấu hình; không coi đó là PASS. Không sửa test để bỏ qua lỗi.

## Nhập mật khẩu trong db-config

Chạy `bash lab.sh db-config` trực tiếp trong terminal Ubuntu/WSL tương tác, tại `Ma_nguon`. Không dùng pipe hoặc chuyển hướng đầu vào cho lệnh này.

- Nhấn Enter để dùng máy chủ `127.0.0.1` và cổng `3306`, hoặc nhập giá trị được cấp.
- Tên CSDL và tài khoản phải khớp tài khoản đã tạo; mặc định là `communitypass_lab_v5`.
- Khi nhập mật khẩu, terminal **không hiện ký tự hoặc dấu sao**. Gõ mật khẩu rồi nhấn Enter; đây là hành vi bình thường.
- Kết nối thành công mới có `CAU_HINH=DA_LUU`. Sau đó chạy `bash lab.sh check`, tạo schema theo hướng dẫn rồi chạy Web.
- Sai mật khẩu: công cụ báo kết nối thất bại và không lưu cấu hình mới. Cấu hình đã lưu từ trước được giữ nguyên; chạy lại `db-config` để sửa thông tin.
- Nếu báo `Mo terminal tuong tac de nhap mat khau`, mở terminal tương tác thực sự rồi chạy lại. Không đưa mật khẩu vào câu lệnh, ảnh chụp, Git hoặc bài nộp.

Chỉ cần cấu hình lại khi thông tin kết nối thay đổi. Mỗi lần chạy `check` đạt không thay thế bước tạo schema ban đầu.

## Tự kiểm tra luồng trên trình duyệt

1. Mở `/register.php`, gửi form trống: thấy lỗi họ tên/email.
2. Chọn sự kiện, nhập tên và email thử nghiệm, nhấn **Tiếp tục**: chuyển sang trang xác nhận.
3. Chọn **Sửa lại**: sự kiện, tên và email của bản nháp vẫn còn. Thử sửa rồi tiếp tục.
4. Chọn **Hủy bản nháp**: quay về form trống. Nhập lại thông tin và xác nhận để kiểm tra luồng hoàn tất.
5. Tại form đăng ký, chọn **Đầy đủ**, nhấn **Lưu tùy chọn**: dòng `Tùy chọn Cookie` hiển thị `full`.
6. Dùng một phiên trình duyệt riêng để mở `/confirm.php`: phiên mới chưa có bản nháp nên được chuyển về form nhập. Không dùng hai tab cùng phiên để kiểm tra cách ly Session.

Khi so sánh Lab04 và Lab05 trên `127.0.0.1`, dùng hai phiên trình duyệt riêng: Cookie tùy chọn `view` dùng chung theo host, không tách theo cổng. Dữ liệu Session của từng lab dùng tên và thư mục riêng do `lab.sh` thiết lập.

Hoàn tất lần đầu cần thấy `CREATED`. Nhập lại cùng sự kiện và email (kể cả đổi chữ hoa/thường): cần thấy `DUPLICATE`. Đổi sang một sự kiện khác là một đăng ký khác.

Đóng máy chủ bằng Ctrl+C rồi chạy lại `bash lab.sh run`: dữ liệu trong CSDL vẫn còn. Thử cùng cặp sự kiện–email vẫn trả `DUPLICATE`.

Nếu mất kết nối khi xác nhận, ứng dụng trả lỗi 503 và thông báo giữ bản nháp. Khôi phục kết nối, chọn **Quay lại xác nhận** rồi thử lại; không coi lỗi kết nối là `DUPLICATE`. Danh sách sự kiện vẫn hiển thị số chỗ mẫu; không dùng số đó để kết luận số đăng ký thực trong CSDL. Thống kê mở rộng vẫn là TODO.

## Khác biệt với đề bài gốc (PostgreSQL)

`HUONG_DAN.md` và `Huong_dan.docx` không được sửa. Các bước trong đó vẫn áp dụng về mặt nghiệp vụ; bảng dưới đây chỉ đối chiếu phần cú pháp CSDL đã đổi trong mã nguồn.

### Chuyển dialect của bộ kiểm thử

Bộ kiểm thử trong `Ma_nguon/tests/` của đề bài gốc dùng dialect PostgreSQL (`pg_query`, tham số `$1..$n`). Vì mã nguồn chạy trên MySQL/MariaDB, các tệp kiểm thử đã được đổi **duy nhất cú pháp** sang `mysqli` (`mysqli_query`, tham số `?`); toàn bộ khẳng định `same(...)` và giá trị kỳ vọng giữ nguyên tuyệt đối, không có khẳng định nào bị nới lỏng, xóa hay thêm. Số ca kiểm thử vẫn là: `tests/run.php` = 6, `tests/integration.php` = 7, `tests/extension.php` = 4. Việc đổi cú pháp không nhằm bỏ qua lỗi: `NOT_RUN` vẫn là `NOT_RUN`, và một lần sửa lỗi trong mã ứng dụng vẫn phải làm `FAILED` tăng.

Các tệp nằm trong danh sách "bạn đọc và sửa" của đề bài: `src/Database.php`, `src/RegistrationRepository.php`, `database/schema.sql`, `demo-db.php`, `public/complete.php`; tệp bài tự làm: `src/RegistrationStats.php`.

| Đề bài gốc (PostgreSQL) | Mã nguồn hiện tại (MySQL/MariaDB) | Ghi chú |
| --- | --- | --- |
| Phần mở rộng `pgsql`, trình khách `psql` | Phần mở rộng `mysqli`; không cần client CLI | `bash lab.sh sql` chạy bằng PHP |
| Tham số `$1,$2,$3` | Tham số `?` | `mysqli_prepare` + `mysqli_stmt_bind_param` |
| `ON CONFLICT (event_id,email) DO NOTHING RETURNING id` | `ON DUPLICATE KEY UPDATE event_id = event_id` + `mysqli_stmt_affected_rows` | Chèn mới trả `1` → `CREATED`; trùng trả `0` → `DUPLICATE` |
| `bigserial` | `BIGINT UNSIGNED NOT NULL AUTO_INCREMENT` | Khóa chính của `registrations` |
| `timestamptz NOT NULL DEFAULT now()` | `TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP` | Cột `created_at` |
| `INSERT ... ON CONFLICT (id) DO NOTHING` | `INSERT ... ON DUPLICATE KEY UPDATE id=id` | Nạp 3 sự kiện mẫu |
| `current_database()` | `DATABASE()` | `tools/db-check.php` dùng thêm `CURRENT_USER()` và `VERSION()` |
| `\i file.sql` | `source file.sql` | Công cụ cũng nhận `\i file.sql`; không thêm `;` sau đường dẫn |
| `pg_query_params`, `pg_fetch_all`, `pg_num_rows` | `mysqli_prepare`/`mysqli_stmt_bind_param`, `mysqli_fetch_all`, `mysqli_stmt_affected_rows` | API truy vấn tham số của `mysqli` |
| `\q` | `quit` hoặc `\q` | Thoát phiên `bash lab.sh sql` |

## Bảng ánh xạ yêu cầu đề bài → cách kiểm chứng

| Yêu cầu | Cách kiểm chứng |
| --- | --- |
| Lần đầu `CREATED`, lặp cùng cặp sự kiện–email `DUPLICATE` | Chạy `php demo-db.php` hai lần; thử form trên trình duyệt; `bash lab.sh test`; đối chiếu SQL `GROUP BY event_id,email` |
| Tên chứa dấu nháy được lưu và đọc lại nguyên vẹn | `demo-db.php` dùng tên `O'Neil Demo`; ca `quote-preserved` trong `tests/integration.php` |
| Thống kê sự kiện chưa có người đăng ký trả `total=0` | `bash lab.sh extend`, ca `zero-included` |
| ID sự kiện không tồn tại trả mảng rỗng | `bash lab.sh extend`, ca `missing` |
| Dữ liệu còn sau khi khởi động lại PHP | Ctrl+C rồi chạy lại `bash lab.sh run`; cùng cặp sự kiện–email vẫn trả `DUPLICATE` |

## Giao diện Material 3 và khu thử riêng

Sau `bash lab.sh run`, mở [sự kiện mẫu](http://127.0.0.1:8105/ui-demo.php) hoặc [thử vé/ghế](http://127.0.0.1:8105/ticket-preview.php?id=demo-open-source). Sáu sự kiện hư cấu và ảnh SVG cục bộ có giấy phép CC0. Không cần cài thêm thư viện hoặc build.

Khu thử có 96 ghế cố định, 68 khả dụng (16 VIP và 52 Tiêu chuẩn); lựa chọn ghế/vé chỉ ở bộ nhớ trang.

Chi tiết sự kiện lab tại `/event.php?id=1` chỉ đọc; nút mở biểu mẫu vẫn dùng chức năng gốc. Lựa chọn demo chỉ ở bộ nhớ trang, tải lại sẽ xóa; chưa giữ ghế, tạo vé hoặc thanh toán. ID demo không đi vào Form/CSDL. TODO và các ca `extend` sinh viên vẫn giữ nguyên.

Xem [hướng dẫn phần trình bày](Ma_nguon/README.md#giao-diện-material-3).

Kiểm tra dữ liệu UI riêng (không thay test gốc, không cần CSDL): tại `Ma_nguon`, chạy `php tests/ui-demo.php`.

## Nộp bài

Lưu mã bài mẫu và bài tự làm trong `Ma_nguon`, kèm dữ liệu, kiểm thử và README. Chạy `bash lab.sh check`, `bash lab.sh test`, `bash lab.sh extend` và các phép kiểm tra trực tiếp trước khi nộp. Không sửa test để bỏ qua lỗi.

Không đưa `.local`, mật khẩu hoặc dữ liệu cá nhân thật vào bài nộp. Commit, đẩy mã lên GitHub và nộp liên kết `Ma_nguon` tại đúng commit. Kiểm tra quyền đọc của kho mã; đính kèm lần chạy Actions khi được yêu cầu. Hạn và địa chỉ nộp do giảng viên công bố.

## Các bài khác

[Lab01](https://github.com/FIT-DNU/Open-Source-Software-Development/tree/lab1) · [Lab02](https://github.com/FIT-DNU/Open-Source-Software-Development/tree/lab2) · [Lab03](https://github.com/FIT-DNU/Open-Source-Software-Development/tree/lab3) · [Lab04](https://github.com/FIT-DNU/Open-Source-Software-Development/tree/lab4) · [Lab05](https://github.com/FIT-DNU/Open-Source-Software-Development/tree/lab5)
