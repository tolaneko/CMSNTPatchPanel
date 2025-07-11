# **Giới thiệu về CMSNT Patch Panel**

Chào mừng bạn đến với **CMSNT Patch Panel**, một công cụ được thiết kế để hỗ trợ việc "null" các sản phẩm của CMSNT.

## **Mục đích**

Dự án này chuyên về việc điều chỉnh và cập nhật các chức năng cụ thể trong mã nguồn của các sản phẩm CMSNT, cho phép chúng hoạt động mà không cần xác minh giấy phép gốc. Điều này hữu ích cho mục đích phát triển, thử nghiệm hoặc sử dụng cá nhân khi bạn cần linh hoạt hơn với các sản phẩm của mình.

## **Cách thức hoạt động**

CMSNT Patch Panel hoạt động dựa trên một script PHP duy nhất, được tích hợp cả giao diện người dùng và logic xử lý backend:

1. **Cấu hình Dự án:** Bạn định cấu hình dự án mục tiêu (ví dụ: SHOPCLONE7_ENCRYPTION, SMMPANELV1, v.v.) trong tệp config.php.
2. **Kiểm tra Phiên bản:** Khi bạn truy cập trang web, nó sẽ tự động kết nối đến các API của CMSNT (hoặc các nguồn cập nhật khác) để lấy thông tin phiên bản mới nhất của tất cả các sản phẩm được hỗ trợ và hiển thị chúng dưới dạng các "hộp" thông tin.
3. **Cập nhật Chức năng:**
    - Script sẽ xác định các hàm cần được "null" hoặc thay thế cho dự án đã cấu hình.
    - Nó sau đó sẽ tải mã nguồn mới của các hàm này từ các Gist công khai (hoặc các URL được định nghĩa trước).
    - Cuối cùng, script sẽ tự động tìm và thay thế các hàm cũ trong tệp mã nguồn của sản phẩm CMSNT bằng các phiên bản mới đã được "null" hoặc sửa đổi.

Quá trình này giúp bạn dễ dàng duy trì và điều chỉnh các sản phẩm CMSNT mà không cần can thiệp thủ công vào từng dòng mã.

_Được phát triển bởi @Mo_Ho_Bo_