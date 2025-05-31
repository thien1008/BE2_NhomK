# BE2_NhomK

## Cài đặt và chạy dự án

```bash
# Sao chép kho lưu trữ về máy
git clone https://github.com/thien1008/BE2_NhomK.git

# Chuyển vào thư mục dự án
cd BE2_NhomK

# Cài đặt các gói PHP
composer install

# Cài đặt các gói Node.js
npm install

# Chạy di trú cơ sở dữ liệu và tạo liên kết thư mục lưu trữ
php artisan migrate
php artisan storage:link

# Biên dịch tài nguyên frontend
npm run dev

# Khởi động máy chủ phát triển
php artisan serve
