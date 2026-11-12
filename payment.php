<?php
include 'check.php';

if (empty($_SESSION['gtotal']) || empty($_SESSION['delivery_address'])) {
    header('Location: checkout.php');
    exit;
}

$pawapay = new PawaPayClient();
$currency = pawapayCurrency();
$countryPrefix = pawapayCountryPrefix();
$providerOptions = [
    'MTN_MOMO_RWA' => ['name' => 'MTN MoMo', 'logo' => 'https://static-content.pawapay.io/company_logos/mtn.png'],
    'AIRTEL_RWA' => ['name' => 'Airtel Money', 'logo' => 'https://static-content.pawapay.io/company_logos/airtel.png'],
];
$errorMessage = '';
$redirectUrl = '';

if ($pawapay->isConfigured()) {
    $configResponse = $pawapay->getActiveConfig(pawapayCountry(), 'DEPOSIT');
    if ($configResponse['code'] === 200 && !empty($configResponse['body']['countries'][0]['providers'])) {
        $providerOptions = [];
        foreach ($configResponse['body']['countries'][0]['providers'] as $provider) {
            $code = $provider['provider'] ?? '';
            if ($code === '') continue;
            $providerOptions[$code] = [
                'name' => $provider['displayName'] ?? $code,
                'logo' => $provider['logo'] ?? '',
            ];
        }
        if (!empty($configResponse['body']['countries'][0]['prefix'])) {
            $countryPrefix = $configResponse['body']['countries'][0]['prefix'];
        }
    }
}

if (isset($_POST['submit'])) {
    if (!$pawapay->isConfigured()) {
        $errorMessage = 'Mobile money payments are not configured yet.';
    } else {
        $orderno = (string) mt_rand(100000000, 999999999);
        $userid = (int) $_SESSION['id'];
        // Whole francs: the orders column is an integer amount and PawaPay is
        // sent the same figure, so no rounding can happen between the two.
        $totalamount = rwfRound($_SESSION['gtotal']);
        $provider = trim($_POST['provider'] ?? '');
        $phoneNumber = pawapayNormalizePhone(trim($_POST['phone'] ?? ''), $countryPrefix);

        if ($phoneNumber === '' || !isset($providerOptions[$provider])) {
            $errorMessage = 'Please select your network and enter a valid phone number.';
        } else {
            $predict = $pawapay->predictProvider($phoneNumber);
            if ($predict['code'] === 200 && !empty($predict['body']['phoneNumber'])) {
                $phoneNumber = $predict['body']['phoneNumber'];
            }

            $depositId = pawapayGenerateUuid();
            $addr = mysqli_real_escape_string($con, $_SESSION['delivery_address']);
            $city = mysqli_real_escape_string($con, $_SESSION['delivery_city']);
            $province = mysqli_real_escape_string($con, $_SESSION['delivery_province']);
            $country = mysqli_real_escape_string($con, $_SESSION['delivery_country'] ?? 'Rwanda');
            $ordernoEsc = mysqli_real_escape_string($con, $orderno);
            $depositIdEsc = mysqli_real_escape_string($con, $depositId);
            $providerEsc = mysqli_real_escape_string($con, $provider);

            $ok = mysqli_query(
                $con,
                "INSERT INTO orders(orderNumber, user_id, delivery_address, delivery_city, delivery_province, delivery_country,
                    totalAmount, txnType, txnNumber, depositId, paymentStatus, orderStatus)
                 VALUES ('$ordernoEsc', '$userid', '$addr', '$city', '$province', '$country',
                    '$totalamount', 'Mobile Money', '$providerEsc', '$depositIdEsc', 'pending', 'Pending Payment')"
            );

            if ($ok) {
                $response = $pawapay->initiateDeposit(
                    $depositId,
                    $totalamount,
                    $currency,
                    $phoneNumber,
                    $provider,
                    ['orderNumber' => $orderno, 'userId' => (string) $userid, 'source' => 'cypadi-marketplace']
                );
                $body = $response['body'];
                $accepted = in_array($response['code'], [200, 201, 202], true)
                    && is_array($body)
                    && in_array($body['status'] ?? '', ['ACCEPTED', 'DUPLICATE_IGNORED'], true);

                if ($accepted) {
                    $redirectUrl = 'payment-waiting.php?order=' . urlencode($orderno) . '&deposit=' . urlencode($depositId);
                } else {
                    mysqli_query($con, "UPDATE orders SET paymentStatus='failed', orderStatus='Payment Failed' WHERE depositId='$depositIdEsc'");
                    $errorMessage = $body['failureReason']['failureMessage'] ?? 'Payment could not be started.';
                }
            } else {
                $errorMessage = 'Could not create your order. Please try again.';
            }
        }
    }

    if (!empty($_POST['ajax'])) {
        header('Content-Type: application/json');
        echo json_encode($redirectUrl !== ''
            ? ['ok' => true, 'redirect' => $redirectUrl]
            : ['ok' => false, 'message' => $errorMessage ?: 'Payment failed']);
        exit;
    }
    if ($redirectUrl !== '') {
        header('Location: ' . $redirectUrl);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cypadi Shop | Pay with Mobile Money</title>
    <?php include __DIR__ . '/includes/favicon.php'; ?>
    <link rel="stylesheet" href="./src/css/bootstrap.min.css">
    <link rel="stylesheet" href="./src/css/style.css">
    <link rel="stylesheet" href="./src/css/payment.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
</head>
<body>
<?php include './includes/header.php'; ?>
<section class="shoping-cart spad">
    <div class="container">
        <h3>Pay with Mobile Money</h3>
        <?php if ($errorMessage): ?><div class="alert alert-danger"><?= htmlspecialchars($errorMessage); ?></div><?php endif; ?>
        <?php if (!$pawapay->isConfigured()): ?>
            <div class="alert alert-warning">Mobile money is not configured.</div>
        <?php else: ?>
        <div class="row">
            <div class="col-md-4">
                <div class="payment-summary-box">
                    <p>Amount to pay</p>
                    <h2><?= formatRwf($_SESSION['gtotal']); ?></h2>
                </div>
            </div>
            <div class="col-md-8">
                <form method="post" id="paymentform">
                    <input type="hidden" name="submit" value="1">
                    <input type="hidden" name="ajax" value="1">
                    <p><strong>Choose network</strong></p>
                    <div class="provider-grid">
                        <?php foreach ($providerOptions as $code => $info): ?>
                        <label class="provider-card">
                            <input type="radio" name="provider" value="<?= htmlspecialchars($code); ?>" required>
                            <?php if (!empty($info['logo'])): ?><img src="<?= htmlspecialchars($info['logo']); ?>" alt="" height="32"><?php endif; ?>
                            <span><?= htmlspecialchars($info['name']); ?></span>
                        </label>
                        <?php endforeach; ?>
                    </div>
                    <div class="form-group mt-3">
                        <label>Mobile number (without +<?= htmlspecialchars($countryPrefix); ?>)</label>
                        <input type="text" name="phone" class="form-control" required placeholder="788123456">
                    </div>
                    <button type="submit" class="primary-btn mt-3" id="payNowBtn">Pay Now</button>
                    <a href="checkout.php" class="btn btn-link">Back</a>
                </form>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>
<div id="payment-loading" class="payment-loading-overlay"><div class="payment-loading-box"><p>Processing payment…</p></div></div>
<script>
document.getElementById('paymentform')?.addEventListener('submit', function(e) {
    e.preventDefault();
    var overlay = document.getElementById('payment-loading');
    overlay.style.display = 'flex';
    var fd = new FormData(this);
    fetch('payment.php', { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(r => r.json())
        .then(data => { if (data.ok && data.redirect) location.href = data.redirect; else { overlay.style.display='none'; alert(data.message || 'Payment failed'); } })
        .catch(() => { overlay.style.display='none'; alert('Server error'); });
});
</script>
<?php include './includes/footer.php'; ?>
</body>
</html>
