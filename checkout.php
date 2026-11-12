<?php
include 'check.php';

$user_id = (int) $_SESSION['id'];
$user = $query->executeQuery("SELECT * FROM accounts WHERE id = $user_id")->fetch_assoc();
$cartItems = $query->getCartItems($user_id);
$total = array_reduce($cartItems, fn($sum, $item) => $sum + $item['total_price'], 0);

if (empty($cartItems)) {
    header('Location: shoping-cart.php');
    exit;
}

if (isset($_POST['proceed_payment'])) {
    $_SESSION['delivery_address'] = $query->validate($_POST['delivery_address'] ?? '');
    $_SESSION['delivery_city'] = $query->validate($_POST['delivery_city'] ?? '');
    $_SESSION['delivery_province'] = $query->validate($_POST['delivery_province'] ?? '');
    $_SESSION['delivery_country'] = $query->validate($_POST['delivery_country'] ?? 'Rwanda');
    $_SESSION['gtotal'] = $total;
    header('Location: payment.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cypadi Shop | Checkout</title>
    <?php include __DIR__ . '/includes/favicon.php'; ?>
    <link rel="stylesheet" href="./src/css/bootstrap.min.css">
    <link rel="stylesheet" href="./src/css/style.css">
</head>
<body>
<?php include './includes/header.php'; ?>
<section class="shoping-cart spad">
    <div class="container">
        <h3 class="mb-4">Checkout</h3>
        <div class="row">
            <div class="col-lg-7">
                <h5>Delivery Address</h5>
                <form method="post">
                    <div class="form-group">
                        <label>Delivery Address *</label>
                        <textarea name="delivery_address" class="form-control" rows="3" required></textarea>
                    </div>
                    <div class="form-group">
                        <label>Delivery Province *</label>
                        <input type="text" name="delivery_province" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Delivery City *</label>
                        <input type="text" name="delivery_city" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Country</label>
                        <input type="text" name="delivery_country" class="form-control" value="Rwanda" required>
                    </div>
                    <button type="submit" name="proceed_payment" class="primary-btn">Proceed to Payment</button>
                </form>
            </div>
            <div class="col-lg-5">
                <h5>Order Summary</h5>
                <p><strong>Customer:</strong> <?= htmlspecialchars($user['name']); ?></p>
                <p><strong>Email:</strong> <?= htmlspecialchars($user['email']); ?></p>
                <p><strong>Phone:</strong> <?= htmlspecialchars($user['number']); ?></p>
                <ul class="list-unstyled mt-3">
                    <?php foreach ($cartItems as $item): ?>
                        <li><?= htmlspecialchars($item['name']); ?> × <?= (int) $item['number_of_products']; ?> — <?= formatRwf($item['total_price']); ?></li>
                    <?php endforeach; ?>
                </ul>
                <h4 class="mt-3">Total: <?= formatRwf($total); ?></h4>
            </div>
        </div>
    </div>
</section>
<?php include './includes/footer.php'; ?>
</body>
</html>
