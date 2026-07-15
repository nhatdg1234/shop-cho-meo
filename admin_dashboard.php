<?php
session_start();
require_once "db_conn.php";

if (!isset($_SESSION['id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: index.php");
    exit();
}

$stmt = $conn->query("SELECT * FROM products ORDER BY id DESC");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>PetShop Admin - Quản lý</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f7f6; padding: 20px; margin: 0; }
        .container { max-width: 1100px; margin: auto; background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
        .header-flex { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #024262; padding-bottom: 10px; margin-bottom: 20px; gap: 15px; }
        h1, h2 { color: #024262; }
        .btn { padding: 8px 15px; border: none; border-radius: 5px; cursor: pointer; color: white; text-decoration: none; display: inline-block; font-weight: bold; }
        .btn-add { background: #02c0fa; }
        .btn-add:hover { background: #029bca; }
        .btn-del { background: #ff4d4d; }
        .btn-del:hover { background: #d93636; }
        .btn-edit { background: #ffc107; color: #000; }
        .btn-logout { background: #333; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 12px; text-align: left; vertical-align: middle; }
        th { background-color: #024262; color: white; }
        tr:nth-child(even) { background: #f9f9f9; }
        .form-wrap { background: #e9f5f9; padding: 20px; border-radius: 8px; margin-bottom: 30px; }
        .form-group { margin-bottom: 15px; }
        label { font-weight: bold; display: block; margin-bottom: 5px; color: #333; }
        input, select, textarea { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        #message-box { padding: 10px; margin-bottom: 15px; border-radius: 5px; display: none; }
        .success { background: #d4edda; color: #155724; }
        .error { background: #f8d7da; color: #721c24; }
        .product-img { width: 70px; height: 55px; object-fit: cover; border-radius: 5px; border: 1px solid #ddd; }

        /* CSS cho Modal (Hộp thoại sửa sản phẩm) */
        .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); }
        .modal-content { background-color: #fff; margin: 5% auto; padding: 20px; border-radius: 8px; width: 80%; max-width: 600px; box-shadow: 0 5px 15px rgba(0,0,0,0.3); }
        .close-btn { color: #aaa; float: right; font-size: 28px; font-weight: bold; cursor: pointer; }
        .close-btn:hover { color: #000; }
        .empty-row { text-align: center; color: #666; }
        @media (max-width: 768px) {
            .header-flex { flex-direction: column; align-items: flex-start; }
            table { font-size: 13px; }
            th, td { padding: 8px; }
        }
    </style>
</head>
<body>
<div class="container">
    <div class="header-flex">
        <h1>🐾 Quản lý Cửa Hàng PetShop</h1>
        <div>
            <span>Xin chào, <b><?= htmlspecialchars($_SESSION['name'] ?? 'Admin') ?></b></span>
            <a href="logout.php" class="btn btn-logout">Đăng xuất</a>
        </div>
    </div>

    <div id="message-box"></div>

    <div class="form-wrap">
        <h2>Thêm Thú cưng / Sản phẩm mới</h2>
        <form id="addProductForm">
            <div class="form-group">
                <label for="product_name">Tên Thú cưng / Phụ kiện:</label>
                <input type="text" id="product_name" required>
            </div>

            <div class="form-group">
                <label for="category">Phân loại:</label>
                <select id="category" required>
                    <option value="">-- Chọn danh mục --</option>
                    <optgroup label="Danh mục Chó">
                        <option value="Chó - Giống mới về">Chó - Giống mới về</option>
                        <option value="Chó - Giống lớn">Chó - Giống lớn</option>
                        <option value="Chó Shiba">Chó Shiba</option>
                        <option value="Chó Phốc">Chó Phốc</option>
                    </optgroup>
                    <optgroup label="Danh mục Mèo">
                        <option value="Mèo - Giống mới về">Mèo - Giống mới về</option>
                        <option value="Mèo đen">Mèo đen</option>
                        <option value="Mèo lai">Mèo lai</option>
                        <option value="Mèo nước ngoài">Mèo nước ngoài</option>
                    </optgroup>
                    <optgroup label="Danh mục và Phụ kiện khác">
                        <option value="Vật nuôi đang lớn">Vật nuôi đang lớn</option>
                        <option value="Flash sale">Flash sale</option>
                        <option value="Hot pet">Hot pet</option>
                        <option value="Bộ sưu tập">Bộ sưu tập</option>
                        <option value="Thức ăn">Thức ăn</option>
                        <option value="Vật dụng nuôi">Vật dụng nuôi</option>
                    </optgroup>
                </select>
            </div>

            <div class="form-group" style="background: #fff5e6; padding: 15px; border-radius: 8px; border: 1px dashed #ff8a00;">
                <label style="display: flex; align-items: center; cursor: pointer; margin: 0; color: #d84315;">
                    <input type="checkbox" id="is_spotlight" value="1" style="width: 20px; height: 20px; margin-right: 12px; accent-color: #ff8a00;">
                    <span>🌟 <b>Đặt làm Offer Đặc Quyền</b> (Sẽ hiển thị to ở trang chủ + Tự động AI tách nền ảnh)</span>
                </label>
            </div>
            <div class="form-group">
                <label for="price">Giá bán (VNĐ):</label>
                <input type="number" id="price" min="0" required>
            </div>

            <div class="form-group">
                <label for="stock_quantity">Tồn kho:</label>
                <input type="number" id="stock_quantity" min="0" value="1" required>
            </div>

            <div class="form-group">
                <label for="image_url">Ảnh sản phẩm:</label>
                <input type="file" id="image_url" accept="image/*" required>
            </div>

            <div class="form-group">
                <label for="description">Giới thiệu chi tiết:</label>
                <textarea id="description" rows="4"></textarea>
            </div>

            <button type="submit" class="btn btn-add">+ Thêm Sản Phẩm</button>
        </form>
    </div>

    <h2>Danh sách đang bán</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Hình ảnh</th>
                <th>Tên</th>
                <th>Phân loại</th>
                <th>Giá</th>
                <th>Kho</th>
                <th>Thao tác</th>
            </tr>
        </thead>
        <tbody id="product-table-body">
            <?php if (count($products) > 0): ?>
                <?php foreach ($products as $p): ?>
                    <tr id="row-<?= (int)$p['id'] ?>">
                        <td><?= (int)$p['id'] ?></td>
                        <td>
                            <?php if (!empty($p['image_url'])): ?>
                                <img src="shop/User/anh/<?= htmlspecialchars($p['image_url']) ?>" class="product-img" alt="Ảnh sản phẩm">
                            <?php else: ?>
                                Trống
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($p['product_name']) ?></td>
                        <td><?= htmlspecialchars($p['category']) ?></td>
                        <td><?= number_format((float)$p['price'], 0, ',', '.') ?> VNĐ</td>
                        <td><?= (int)$p['stock_quantity'] ?></td>
                        <td>
                            <button class="btn btn-edit" onclick="openEditModal(<?= (int)$p['id'] ?>, '<?= htmlspecialchars($p['product_name'], ENT_QUOTES) ?>', '<?= htmlspecialchars($p['category'], ENT_QUOTES) ?>', <?= (float)$p['price'] ?>, <?= (int)$p['stock_quantity'] ?>, '<?= htmlspecialchars($p['description'] ?? '', ENT_QUOTES) ?>')">Sửa</button>
                            <button class="btn btn-del" onclick="deleteProduct(<?= (int)$p['id'] ?>)">Xóa</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr id="empty-row">
                    <td colspan="7" class="empty-row">Chưa có sản phẩm nào trong cửa hàng.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<script>
    const msgBox = document.getElementById('message-box');

    function showMsg(type, text) {
        msgBox.style.display = 'block';
        msgBox.className = type;
        msgBox.innerText = text;
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    document.getElementById('addProductForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const imageInput = document.getElementById('image_url');

        if (!imageInput.files[0]) {
            showMsg('error', 'Vui lòng chọn ảnh sản phẩm!');
            return;
        }

        const formData = new FormData();
        formData.append('product_name', document.getElementById('product_name').value.trim());
        formData.append('category', document.getElementById('category').value);
        formData.append('price', document.getElementById('price').value);
        formData.append('stock_quantity', document.getElementById('stock_quantity').value);
        formData.append('description', document.getElementById('description').value.trim());
        formData.append('image_url', imageInput.files[0]);
        
        // CHÈN THÊM 1 DÒNG NÀY ĐỂ BẮT GIÁ TRỊ CHECKBOX:
        formData.append('is_spotlight', document.getElementById('is_spotlight').checked ? '1' : '0');

        fetch('api/api_add_product.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                showMsg('success', data.message);
                document.getElementById('addProductForm').reset();
                setTimeout(() => location.reload(), 1000);
            } else {
                showMsg('error', data.message);
            }
        })
        .catch(() => {
            showMsg('error', 'Lỗi kết nối máy chủ!');
        });
    });

    function deleteProduct(id) {
        if (!confirm('Bạn có chắc chắn muốn xóa sản phẩm này?')) {
            return;
        }

        fetch('api/api_delete_product.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: id })
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                showMsg('success', data.message);
                const row = document.getElementById('row-' + id);
                if (row) {
                    row.remove();
                }
            } else {
                showMsg('error', data.message);
            }
        })
        .catch(() => {
            showMsg('error', 'Lỗi kết nối máy chủ!');
        });
    }
</script>
<div id="editModal" class="modal">
    <div class="modal-content">
        <span class="close-btn" onclick="closeModal()">&times;</span>
        <h2>Sửa thông tin Thú cưng / Sản phẩm</h2>
        <form id="editProductForm">
            <input type="hidden" id="edit_id">
            
            <div class="form-group"><label>Tên:</label><input type="text" id="edit_product_name" required></div>
            
            <div class="form-group"><label>Phân loại:</label>
                <select id="edit_category" required>
                    <option value="">-- Chọn danh mục --</option>
                    <optgroup label="Danh mục Chó">
                        <option value="Chó - Giống mới về">Chó - Giống mới về</option>
                        <option value="Chó - Giống lớn">Chó - Giống lớn</option>
                        <option value="Chó Shiba">Chó Shiba</option>
                        <option value="Chó Phốc">Chó Phốc</option>
                    </optgroup>
                    <optgroup label="Danh mục Mèo">
                        <option value="Mèo - Giống mới về">Mèo - Giống mới về</option>
                        <option value="Mèo đen">Mèo đen</option>
                        <option value="Mèo lai">Mèo lai</option>
                        <option value="Mèo nước ngoài">Mèo nước ngoài</option>
                    </optgroup>
                    <optgroup label="Danh mục và Phụ kiện khác">
                        <option value="Vật nuôi đang lớn">Vật nuôi đang lớn</option>
                        <option value="Flash sale">Flash sale</option>
                        <option value="Hot pet">Hot pet</option>
                        <option value="Bộ sưu tập">Bộ sưu tập</option>
                        <option value="Thức ăn">Thức ăn</option>
                        <option value="Vật dụng nuôi">Vật dụng nuôi</option>
                    </optgroup>
                </select>
            </div>
            <div class="form-group"><label>Giá (VNĐ):</label><input type="number" id="edit_price" required></div>
            <div class="form-group"><label>Tồn kho:</label><input type="number" id="edit_stock" required></div>
            <div class="form-group">
                <label>Ảnh mới (Bỏ trống nếu muốn giữ nguyên ảnh cũ):</label>
                <input type="file" id="edit_image_url" accept="image/*">
            </div>
            <div class="form-group"><label>Mô tả:</label><textarea id="edit_description" rows="3"></textarea></div>
            
            <button type="submit" class="btn btn-edit" style="width: 100%; margin-top: 10px;">Lưu Thay Đổi</button>
        </form>
    </div>
</div>

<script>
    function openEditModal(id, name, category, price, stock, description) {
        document.getElementById('edit_id').value = id;
        document.getElementById('edit_product_name').value = name;
        document.getElementById('edit_category').value = category;
        document.getElementById('edit_price').value = price;
        document.getElementById('edit_stock').value = stock;
        document.getElementById('edit_description').value = description;
        document.getElementById('edit_image_url').value = '';
        document.getElementById('editModal').style.display = 'block';
    }

    function closeModal() {
        document.getElementById('editModal').style.display = 'none';
        document.getElementById('editProductForm').reset();
    }

    document.getElementById('editProductForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        let formData = new FormData();
        formData.append('id', document.getElementById('edit_id').value);
        formData.append('product_name', document.getElementById('edit_product_name').value);
        formData.append('category', document.getElementById('edit_category').value);
        formData.append('price', document.getElementById('edit_price').value);
        formData.append('stock_quantity', document.getElementById('edit_stock').value);
        formData.append('description', document.getElementById('edit_description').value);
        
        let imgFile = document.getElementById('edit_image_url').files[0];
        if (imgFile) {
            formData.append('image_url', imgFile);
        }

        fetch('api/api_update_product.php', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                showMsg('success', data.message);
                closeModal();
                setTimeout(() => location.reload(), 1000);
            } else {
                alert("Lỗi: " + data.message);
            }
        })
        .catch(() => alert('Lỗi kết nối máy chủ!'));
    });
</script>
</body>
</html>