<?php
require_once __DIR__ . '/../init.php';

if (empty($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: ../login/');
    exit;
}

if (($_SESSION['role'] ?? '') !== 'seller') {
    header('Location: ../');
    exit;
}

$account = $query->select(
    'accounts',
    'name, business_name, seller_status, kyc_note',
    'WHERE id = "' . (int) $_SESSION['id'] . '"'
);
$account = $account[0] ?? [];
$status = $account['seller_status'] ?? 'pending';
$businessName = $account['business_name'] ?? '';

if ($status === 'approved') {
    header('Location: ./');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seller Review | Cypadi Shop</title>
    <link rel="icon" href="../favicon.ico">
    <link rel="stylesheet" href="../src/css/bootstrap.min.css">
    <link rel="stylesheet" href="../src/css/style.css">
</head>

<body>
    <?php include '../includes/header.php'; ?>

    <section class="shoping-cart spad">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <h3><?= $status === 'rejected' ? 'Seller application not approved' : 'Seller account under review'; ?></h3>

                    <p class="mt-3">
                        Hello <?= htmlspecialchars($account['name'] ?? 'there'); ?>,
                    </p>

                    <?php if ($status === 'rejected'): ?>
                        <p>
                            We could not approve the seller account<?= $businessName !== '' ? ' for <strong>' . htmlspecialchars($businessName) . '</strong>' : ''; ?>.
                        </p>
                        <?php if (!empty($account['kyc_note'])): ?>
                            <p><strong>Reason:</strong> <?= htmlspecialchars($account['kyc_note']); ?></p>
                        <?php endif; ?>
                        <p>
                            You can reply to <a href="mailto:info@cypadi.com">info@cypadi.com</a> with the requested
                            information and we will review your application again.
                        </p>
                    <?php else: ?>
                        <p>
                            Your account has been created. Before any product can be listed, Cypadi reviews the seller
                            details you supplied (business name, country of operation and identity document).
                            This review is normally completed within one business day.
                        </p>
                        <p>
                            We will email you as soon as your account is approved. If you need to send additional
                            documents, or if the review is taking longer than expected, contact
                            <a href="mailto:info@cypadi.com">info@cypadi.com</a> or call +250 784 801 809.
                        </p>
                    <?php endif; ?>

                    <ul class="list-unstyled mt-4">
                        <li><strong>Business name:</strong> <?= htmlspecialchars($businessName !== '' ? $businessName : '—'); ?></li>
                        <li><strong>Account status:</strong> <?= $status === 'rejected' ? 'Not approved' : 'Awaiting review'; ?></li>
                    </ul>

                    <div class="mt-4">
                        <a href="../index.php" class="primary-btn">Continue shopping</a>
                        <a href="../logout/" class="btn btn-link">Log out</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include '../includes/footer.php'; ?>
</body>

</html>
