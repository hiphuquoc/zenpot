<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Mời nâng cấp hồ sơ doanh nghiệp</title>
</head>
<body style="font-family: Arial, sans-serif; font-size: 15px; line-height: 1.6; color: #333;">
    <p>Xin chào {{ $company->name }},</p>

    <p>Hoptackinhdoanh.com là nền tảng kết nối hơn 1 triệu doanh nghiệp trên toàn quốc.</p>

    <p>Chúng tôi nhận thấy hồ sơ doanh nghiệp của bạn đã có mặt trên hệ thống. Tuy nhiên, để tăng khả năng **kết nối đầu tư – tìm kiếm đối tác**, bạn có thể <strong>nâng cấp hồ sơ</strong> với nhiều quyền lợi:</p>

    <ul>
        <li>✅ Hiển thị ưu tiên trong danh mục</li>
        <li>✅ Gắn nhãn "Đối tác ưu tiên"</li>
        <li>✅ Được đề xuất trong các chiến dịch email & marketing nội bộ</li>
        <li>✅ Đăng bài miễn phí giới thiệu dự án/hợp tác</li>
    </ul>

    <p>
        👉 <a href="https://hoptackinhdoanh.com/nang-cap-ho-so?email={{ urlencode($company->email) }}">Nhấn vào đây để nâng cấp ngay</a>
    </p>

    <p>Nếu bạn cần hỗ trợ trực tiếp, vui lòng liên hệ: <br>
    📞 Hotline: 0968.6171.68 <br>
    📧 Email: contact@hoptackinhdoanh.com</p>

    <hr>
    <p style="font-size: 13px; color: #777;">Email này được gửi từ hệ thống Hoptackinhdoanh.com theo dữ liệu công khai trên cổng thông tin doanh nghiệp.</p>
</body>
</html>
