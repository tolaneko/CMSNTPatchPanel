# **Hướng dẫn sử dụng CMSNT Patch Panel**

Chào mừng bạn đến với **CMSNT Patch Panel**, một công cụ được thiết kế để hỗ trợ việc "null" các sản phẩm của CMSNT.

## **1\. Mục đích**

Dự án này chuyên về việc điều chỉnh và cập nhật các chức năng cụ thể trong mã nguồn của các sản phẩm CMSNT, cho phép chúng hoạt động mà không cần xác minh giấy phép gốc. Điều này hữu ích cho mục đích phát triển, thử nghiệm hoặc sử dụng cá nhân khi bạn cần linh hoạt hơn với các sản phẩm của mình.

## **2\. Cách thức hoạt động**

CMSNT Patch Panel hoạt động dựa trên một script PHP duy nhất, được tích hợp cả giao diện người dùng và logic xử lý backend:

1. **Cấu hình Dự án:** Bạn định cấu hình dự án mục tiêu (ví dụ: SHOPCLONE7_ENCRYPTION, SMMPANELV1, v.v.) trong tệp config.php.
2. **Kiểm tra Phiên bản:** Khi bạn truy cập trang web, nó sẽ tự động kết nối đến các API của CMSNT (hoặc các nguồn cập nhật khác) để lấy thông tin phiên bản mới nhất của tất cả các sản phẩm được hỗ trợ và hiển thị chúng dưới dạng các "hộp" thông tin.
3. **Cập nhật Chức năng:**
    - Script sẽ xác định các hàm cần được "null" hoặc thay thế cho dự án đã cấu hình.
    - Nó sau đó sẽ tải mã nguồn mới của các hàm này từ các Gist công khai (hoặc các URL được định nghĩa trước).
    - Cuối cùng, script sẽ tự động tìm và thay thế các hàm cũ trong tệp mã nguồn của sản phẩm CMSNT bằng các phiên bản mới đã được "null" hoặc sửa đổi.

Quá trình này giúp bạn dễ dàng duy trì và điều chỉnh các sản phẩm CMSNT mà không cần can thiệp thủ công vào từng dòng mã.

## **3\. Cài đặt và Cấu hình**

1. **Giải nén tệp patch.php:**
    - Đối với các dự án **SMMPANEL2_ENCRYPTION**, **SHOPCLONE6**, **SHOPCLONE7_ENCRYPTION**: Giải nén tệp patch.php vào **thư mục gốc** của sản phẩm CMSNT của bạn.
    - Đối với các dự án **SMMPANELV1**, **SHOPNICK3** (dự án Laravel): Giải nén tệp patch.php vào thư mục **/public** của sản phẩm CMSNT của bạn.
2. **Tiến hành chạy bằng cách nhấp vào nút Run**

## **5\. Lưu ý quan trọng**

- **Sao lưu trước khi cập nhật:** Luôn sao lưu toàn bộ mã nguồn sản phẩm CMSNT của bạn trước khi chạy công cụ này. Mặc dù công cụ được thiết kế để hoạt động an toàn, nhưng việc sao lưu sẽ bảo vệ bạn khỏi mọi sự cố không mong muốn.
- **Quyền truy cập Internet:** Máy chủ của bạn cần có quyền truy cập internet để tải mã nguồn các hàm từ Gist và kiểm tra phiên bản từ các API.
- **Khắc phục sự cố:** Nếu bạn gặp lỗi, hãy kiểm tra thông báo trên giao diện. Nếu thông báo không rõ ràng, hãy kiểm tra nhật ký lỗi của máy chủ web (Apache/Nginx error logs) để biết thêm chi tiết.

Chúc bạn sử dụng CMSNT Patch Panel hiệu quả!

_Được phát triển bởi @Mo_Ho_Bo_