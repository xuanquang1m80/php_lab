<?php include 'app/views/shares/header.php'; ?>

<div class="container mt-5">
    <h1 class="text-center mb-4">Giỏ Hàng</h1>
    
    <?php if (!empty($cartItems)): ?>
        <div class="row">
            <?php foreach ($cartItems as $item): ?>
                <div class="col-md-4 mb-4">
                    <div class="card">
                        <?php if ($item['image']): ?>
                            <img src="/lab_1/<?php echo $item['image']; ?>" alt="Product Image" class="card-img-top" style="max-height: 200px; object-fit: cover;">
                        <?php endif; ?>
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8'); ?></h5>
                            <p class="card-text">
                                <strong>Giá:</strong> <?php echo htmlspecialchars($item['price'], ENT_QUOTES, 'UTF-8'); ?> VND<br>
                                <strong>Số lượng:</strong> <?php echo htmlspecialchars($item['quantity'], ENT_QUOTES, 'UTF-8'); ?>
                            </p>
                            <a href="/lab_1/Product/checkout" class="btn btn-primary">Thanh Toán</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="alert alert-warning" role="alert">
            Giỏ hàng của bạn đang trống.
        </div>
    <?php endif; ?>

    <div class="d-flex justify-content-between mt-4">
        <a href="/lab_1/Product" class="btn btn-secondary">Tiếp tục mua sắm</a>
        <?php if (!empty($cartItems)): ?>
            <a href="/lab_1/Product/checkout" class="btn btn-success">Thanh Toán</a>
        <?php endif; ?>
    </div>
</div>

<?php include 'app/views/shares/footer.php'; ?>
