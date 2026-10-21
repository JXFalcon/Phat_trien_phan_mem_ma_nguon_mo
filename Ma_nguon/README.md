# Lab 05 — Lưu, truy vấn và thống kê đăng ký với MySQL/MariaDB

Mã mẫu cho phần hướng dẫn và mã khởi đầu cho bài tự làm.

## Mở bài

Mở terminal Ubuntu/WSL tại thư mục chứa `lab.sh`. Dùng PHP từ 8.1 và Git. Hướng dẫn chi tiết, sơ đồ, đoạn mã cần sửa, dữ liệu đối chiếu và rubric nằm trong tài liệu của bài lab.

Lab05 dùng MySQL/MariaDB và phần mở rộng `mysqli`. Cài gói `php8.5-mysql` khớp với `php -v` (máy thực hành đang dùng PHP 8.5); kiểm tra bằng `php -m | grep mysqli`. Tạo CSDL lab riêng, không dùng CSDL production và không xóa CSDL có sẵn.

Tại thư mục này, chạy lần lượt:

```bash
bash lab.sh db-config
bash lab.sh check
bash lab.sh sql
```

Khi `db-config` hỏi, nhấn Enter để dùng máy chủ `127.0.0.1`, cổng `3306`, CSDL và tài khoản `communitypass_lab_v5`, hoặc nhập thông tin được cấp. Mật khẩu không hiện khi gõ. Công cụ chỉ lưu cấu hình sau khi kết nối thành công vào `.local/db.json` với quyền `0600`; không commit hoặc nộp thư mục `.local`.

Trong phiên SQL vừa mở, nạp schema rồi kiểm tra:

```sql
source database/schema.sql
SELECT id, title FROM events ORDER BY id;
quit
```

`source` và `\i` là lệnh của công cụ, không phải câu SQL, nên không thêm dấu `;` sau đường dẫn tệp. Có thể dùng `\i database/schema.sql` thay cho `source database/schema.sql`. Nếu gõ `source database/schema.sql;`, công cụ coi dấu `;` là một phần tên tệp và báo `Khong doc duoc tep`; bỏ dấu `;` rồi thử lại. Cần có 3 sự kiện mẫu.

Sau đó chạy Web:

```bash
bash lab.sh run
```

Mở http://127.0.0.1:8105. Giữ terminal máy chủ hoạt động; dùng terminal thứ hai để kiểm thử. Nhấn Ctrl+C để dừng.

## Tệp và nhiệm vụ

- Sinh viên hoàn thiện `src/RegistrationStats.php`: viết truy vấn thống kê theo sự kiện bằng `LEFT JOIN`; sự kiện chưa có đăng ký trả `total=0`, ID không tồn tại trả mảng rỗng.
- Phần mẫu có hướng dẫn: `src/Database.php`, `src/RegistrationRepository.php`, `database/schema.sql`, `demo-db.php`, `public/complete.php`.

Giữ cấu trúc bảng và ràng buộc duy nhất `(event_id, email)` trong `database/schema.sql`. Truy vấn phải dùng tham số `?`, không ghép ID hoặc dữ liệu người dùng trực tiếp vào SQL.

## Kiểm tra bài

```bash
bash lab.sh test
bash lab.sh extend
```

`test` kiểm tra phần mẫu, gồm `tests/run.php` và `tests/integration.php`: kỳ vọng `TESTS=6 FAILED=0` và `TESTS=7 FAILED=0`. `extend` kiểm tra phần tự làm: kỳ vọng `TESTS=4 FAILED=0`. Các vị trí TODO là nhiệm vụ cần hoàn thiện; không sửa test để bỏ qua lỗi.

`bash lab.sh check` chỉ kiểm tra môi trường. Nếu kết quả có `NOT_RUN`, kiểm thử chưa thực thi do thiếu kết nối hoặc cấu hình; không coi đó là PASS.

Kết quả mở rộng cần đạt: `TESTS=4 FAILED=0`. Có luồng Form lưu đăng ký thật, chống trùng theo sự kiện–email, tra cứu danh sách và thống kê cả sự kiện chưa có người đăng ký.

## Khác biệt với đề bài gốc (PostgreSQL)

Bộ kiểm thử trong `tests/` của đề bài gốc dùng dialect PostgreSQL (`pg_query`, tham số `$1..$n`). Vì mã nguồn chạy trên MySQL/MariaDB, các tệp kiểm thử đã được đổi **duy nhất cú pháp** sang `mysqli` (`mysqli_query`, tham số `?`); toàn bộ khẳng định `same(...)` và giá trị kỳ vọng giữ nguyên tuyệt đối, không có khẳng định nào bị nới lỏng, xóa hay thêm. Số ca kiểm thử vẫn là: `tests/run.php` = 6, `tests/integration.php` = 7, `tests/extension.php` = 4. Việc đổi cú pháp không nhằm bỏ qua lỗi: `NOT_RUN` vẫn là `NOT_RUN`, và một lần sửa lỗi trong mã ứng dụng vẫn phải làm `FAILED` tăng.

Các tệp nằm trong danh sách "bạn đọc và sửa" của đề bài: `src/Database.php`, `src/RegistrationRepository.php`, `database/schema.sql`, `demo-db.php`, `public/complete.php`; tệp bài tự làm: `src/RegistrationStats.php`.

## Tệp nộp

Giữ mã nguồn, dữ liệu mẫu và kiểm thử. Ghi lại lệnh chạy trong README. Không đưa `.local`, mật khẩu hoặc dữ liệu cá nhân thật vào kho mã và gói nộp. Nộp đúng commit theo yêu cầu của bài; nếu dùng Actions, đính kèm lần chạy tương ứng.


## Phần trình bày giao diện

Lab này giữ CSS cục bộ tại `public/assets/css/app.css`. HTML nằm trong `public/`; các lab có nhiều trang dùng `templates/header.php` và `templates/footer.php`. Có thể lần từ các lớp `form-field`, `event-item`, `error-summary` sang CSS để đọc cách trình bày; Lab01 chỉ dùng phần trang giới thiệu.

Các lớp, nhãn và bố cục chỉ phục vụ hiển thị. Giữ nguyên khối PHP, GET/POST, tên trường, giá trị option, hidden input và thuộc tính kiểm tra đầu vào khi thực hành. Không cần JavaScript, thư viện hoặc bước build mới để chạy giao diện.


## Giao diện Material 3

Màu và kiểu chữ nằm trong `public/assets/css/tokens.css`; bố cục trong `app.css`. Không có bước build hoặc thư viện mới.

Ba lớp được tách rõ:

- `/`: sự kiện gốc của lab; tập kết quả, bộ lọc và form nghiệp vụ giữ nguyên.
- `/event.php?id=1`: chi tiết chỉ đọc, bổ sung metadata hư cấu từ `data/event-content.php`. CTA mở `/register.php` không kèm tham số mới.
- `/ui-demo.php`: sáu sự kiện hư cấu, ID `demo-...` từ `data/ui-demo-events.php`. `/ticket-preview.php?id=demo-open-source` chỉ thử vé/ghế trong bộ nhớ trang, không có form hoặc API ghi dữ liệu.

Khu thử có 96 ghế cố định, 68 khả dụng (16 VIP và 52 Tiêu chuẩn). Hai vé VIP mẫu 350.000 đồng cho tổng 700.000 đồng. Đổi hạng, đổi sự kiện hoặc tải lại sẽ bỏ lựa chọn không phù hợp; không có trạng thái đã giữ chỗ.

Ảnh SVG tự tạo, lưu cục bộ và ghi quyền CC0 trong `public/assets/images/LICENSE.txt`. `presentation.js` chỉ xử lý ảnh lỗi; `ticket-preview.js` chỉ chạy trên trang thử. Khi tắt JavaScript, vẫn đọc được nội dung và dùng form lab như cũ; khu chọn thử được khóa kèm giải thích.

Không đưa ID demo, vé hoặc ghế vào form, Session, Cookie, CSDL hoặc test cũ. TODO sinh viên và đáp án giữ nguyên khác biệt. Không có backend bán vé, thanh toán hoặc gửi email.
