<?php
include 'check.php';

$orderNumber = trim($_GET['order'] ?? '');
$depositId = trim($_GET['deposit'] ?? '');
if ($orderNumber === '' || $depositId === '') {
    header('Location: payment.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cypadi Shop | Confirming Payment</title>
    <?php include __DIR__ . '/includes/favicon.php'; ?>
    <link rel="stylesheet" href="./src/css/bootstrap.min.css">
    <link rel="stylesheet" href="./src/css/style.css">
</head>
<body>
<?php include './includes/header.php'; ?>
<section class="shoping-cart spad">
    <div class="container text-center">
        <h3 id="status-title">Check your phone</h3>
        <p id="status-message">Approve the mobile money PIN prompt for order <strong><?= htmlspecialchars($orderNumber); ?></strong>.</p>
        <p class="text-muted">This page updates automatically.</p>
        <div id="action-area" style="display:none;" class="mt-3">
            <a href="payment.php" class="primary-btn">Try Again</a>
            <a href="my-orders.php" class="btn btn-link">My Orders</a>
        </div>
    </div>
</section>
<script>
(function(){
    var pollUrl = 'pawapay-poll.php?order=<?= urlencode($orderNumber); ?>&deposit=<?= urlencode($depositId); ?>';
    function poll(){
        fetch(pollUrl, { credentials:'same-origin' }).then(r=>r.json()).then(function(data){
            if (data.status === 'COMPLETED') { location.href = data.redirect || 'my-orders.php'; return; }
            if (data.status === 'FAILED') {
                document.getElementById('status-title').textContent = 'Payment failed';
                document.getElementById('status-message').textContent = data.message || 'Payment was not completed.';
                document.getElementById('action-area').style.display = 'block';
                return;
            }
        }).catch(function(){});
    }
    poll(); setInterval(poll, 3000);
})();
</script>
<?php include './includes/footer.php'; ?>
</body>
</html>
