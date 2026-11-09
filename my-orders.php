<?php
include 'check.php';

$userId = (int) $_SESSION['id'];
$orders = mysqli_query(
    $con,
    "SELECT * FROM orders WHERE user_id='$userId' AND orderNumber IS NOT NULL ORDER BY created_at DESC"
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cypadi Shop | My Orders</title>
    <link rel="stylesheet" href="./src/css/bootstrap.min.css">
    <link rel="stylesheet" href="./src/css/style.css">
</head>
<body>
<?php include './includes/header.php'; ?>
<section class="shoping-cart spad">
    <div class="container">
        <h3>My Orders</h3>
        <table class="table table-bordered">
            <thead><tr><th>Order</th><th>Date</th><th>Total</th><th>Payment</th><th>Status</th></tr></thead>
            <tbody>
            <?php if ($orders && mysqli_num_rows($orders) > 0): while ($row = mysqli_fetch_assoc($orders)): ?>
                <tr>
                    <td><?= htmlspecialchars($row['orderNumber']); ?></td>
                    <td><?= htmlspecialchars($row['created_at']); ?></td>
                    <td>RWF <?= number_format((float) $row['totalAmount']); ?></td>
                    <td><?= htmlspecialchars($row['paymentStatus']); ?></td>
                    <td><?= htmlspecialchars($row['orderStatus']); ?></td>
                </tr>
            <?php endwhile; else: ?>
                <tr><td colspan="5">No orders yet.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>
<?php include './includes/footer.php'; ?>
</body>
</html>
