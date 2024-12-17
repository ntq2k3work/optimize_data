# Tối ưu
## Truy vấn 200k query kết hợp tìm kiếm phân trang
## Sử dụng queue để xử lý ngầm dữ liệu nặng. Cụ thể demo là inventory
## Dùng redis xử lý trường hợp 10 request mua 10 sản phẩm cùng lúc mà tồn kho chỉ còn 12 sản phẩm.Ta dùng queue để tiếp nhận các request và lưu các request vào redis.Chỉ xử lý những request thoả mãn tồn kho và huỷ những request không thoả mãn
