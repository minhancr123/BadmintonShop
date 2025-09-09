<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Hóa đơn <?php echo e($invoice_number); ?></title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; }
        .header { text-align: center; margin-bottom: 20px; }
        .company, .user, .order-items { margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; }
        table, th, td { border: 1px solid #000; }
        th, td { padding: 8px; text-align: left; }
    </style>
</head>
<body>
    <div class="header">
        <h2>HÓA ĐƠN BÁN HÀNG</h2>
        <p>Số hóa đơn: <?php echo e($invoice_number); ?></p>
        <p>Ngày: <?php echo e($invoice_date); ?></p>
    </div>

    <div class="company">
        <h4>Công ty: <?php echo e($company['name']); ?></h4>
        <p>Địa chỉ: <?php echo e($company['address']); ?></p>
        <p>Điện thoại: <?php echo e($company['phone']); ?></p>
        <p>Email: <?php echo e($company['email']); ?></p>
        <p>Mã số thuế: <?php echo e($company['tax_code']); ?></p>
    </div>

    <div class="user">
        <h4>Khách hàng: <?php echo e($user->name); ?></h4>
        <p>Email: <?php echo e($user->email); ?></p>
    </div>

    <div class="order-items">
        <h4>Chi tiết đơn hàng</h4>
        <table>
            <thead>
                <tr>
                    <th>Sản phẩm</th>
                    <th>Số lượng</th>
                    <th>Đơn giá</th>
                    <th>Thành tiền</th>
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $orderItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td><?php echo e($item->product->name); ?></td>
                        <td><?php echo e($item->quantity); ?></td>
                        <td><?php echo e(number_format($item->price, 0, ',', '.')); ?> đ</td>
                        <td><?php echo e(number_format($item->quantity * $item->price, 0, ',', '.')); ?> đ</td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
        <h3>Tổng cộng: <?php echo e(number_format($order->total_amount, 0, ',', '.')); ?> đ</h3>
    </div>
</body>
</html>
<?php /**PATH D:\badminton-shop\resources\views/orders/invoice.blade.php ENDPATH**/ ?>