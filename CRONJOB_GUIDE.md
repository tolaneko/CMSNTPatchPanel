# CMSNT Patch Panel - Auto Patch Cronjob Guide

## Tổng quan
Hệ thống tự động patch đã được tích hợp vào `patch.php` với các tính năng:
- Lưu trữ cấu hình trong file `patch_config.json`
- Tự động theo dõi phiên bản hiện tại và phiên bản mới nhất
- Tự động patch khi có phiên bản mới
- Hỗ trợ cronjob thông qua HTTP request

## Cấu hình JSON (patch_config.json)
File này sẽ được tạo tự động với cấu trúc:
```json
{
    "current_project": "TÊN_PROJECT",
    "current_version": "PHIÊN_BẢN_HIỆN_TẠI",
    "last_check": "2025-01-31 10:30:00",
    "last_update": "2025-01-31 10:30:00",
    "auto_patch_enabled": true,
    "projects": {
        "SHOPCLONE7_ENCRYPTION": {
            "installed_version": "1.0.0",
            "latest_version": "1.0.1",
            "last_patched": "2025-01-31 10:30:00"
        }
    }
}
```

## URL Cronjob
**URL chính cho auto patch:**
```
https://yourdomain.com/path/to/patch.php?action=auto_patch
```

**URL kiểm tra phiên bản:**
```
https://yourdomain.com/path/to/patch.php?action=get_versions
```

## Cài đặt Cronjob

### Linux/Unix (Crontab)
```bash
# Chạy mỗi giờ
0 * * * * curl -s "https://yourdomain.com/path/to/patch.php?action=auto_patch" >/dev/null 2>&1

# Chạy mỗi 6 giờ
0 */6 * * * curl -s "https://yourdomain.com/path/to/patch.php?action=auto_patch" >/dev/null 2>&1

# Chạy hàng ngày lúc 2 giờ sáng
0 2 * * * curl -s "https://yourdomain.com/path/to/patch.php?action=auto_patch" >/dev/null 2>&1
```

### Windows (Task Scheduler)
Tạo task mới với command:
```powershell
powershell -Command "Invoke-WebRequest -Uri 'https://yourdomain.com/path/to/patch.php?action=auto_patch' -UseBasicParsing"
```

### cPanel Cron Jobs
1. Vào cPanel → Cron Jobs
2. Thêm cron job mới:
   - Minute: 0
   - Hour: */6 (mỗi 6 giờ)
   - Command: `curl -s "https://yourdomain.com/patch.php?action=auto_patch"`

## Luồng hoạt động
1. Cronjob gọi URL `?action=auto_patch`
2. Hệ thống kiểm tra phiên bản mới nhất từ API
3. So sánh với phiên bản hiện tại trong `patch_config.json`
4. Nếu có phiên bản mới:
   - Tải code mới từ GitHub
   - Thay thế các hàm cần update
   - Cập nhật `patch_config.json`
5. Trả về kết quả JSON

## Response JSON
```json
{
    "status": "completed",
    "current_project": "SHOPCLONE7_ENCRYPTION",
    "results": {
        "message": "Successfully patched SHOPCLONE7_ENCRYPTION"
    },
    "timestamp": "2025-01-31 10:30:00"
}
```

## Troubleshooting
1. **Kiểm tra file config:** Đảm bảo `patch_config.json` được tạo và có quyền ghi
2. **Kiểm tra kết nối:** Đảm bảo server có thể truy cập GitHub và API
3. **Kiểm tra quyền file:** Đảm bảo có quyền ghi vào file cần patch
4. **Kiểm tra log:** Xem response JSON để debug lỗi

## Bảo mật
- Không cần authentication cho cronjob (chỉ patch khi có update thực sự)
- Backup tự động được tạo trước khi patch
- Chỉ patch các project được config sẵn

## Liên hệ
- Email: contact@maihuybao.dev
- Telegram: @Mo_Ho_Bo
- Website: maihuybao.dev
