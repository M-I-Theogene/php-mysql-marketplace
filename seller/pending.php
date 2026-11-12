<?php
require_once __DIR__ . '/../init.php';
require_once __DIR__ . '/../includes/kyc-upload.php';

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
    'name, business_name, seller_status, kyc_note, id_number, id_document',
    'WHERE id = "' . (int) $_SESSION['id'] . '"'
);
$account = $account[0] ?? [];
$status = $account['seller_status'] ?? 'pending';
$businessName = $account['business_name'] ?? '';
$identityNumber = $account['id_number'] ?? '';
$documentName = $account['id_document'] ?? '';
// The row outlives the file when it was restored from an older dump, so the
// page states which of the two is missing instead of offering a dead link.
$documentAvailable = $documentName !== '' && kycDocumentAvailable($documentName);
$documentUrl = $documentAvailable ? kycDocumentUrl($documentName) : '';

// One-shot notices written by upload-document.php.
$documentError = $_SESSION['kyc_document_error'] ?? '';
$documentSuccess = $_SESSION['kyc_document_success'] ?? '';
unset($_SESSION['kyc_document_error'], $_SESSION['kyc_document_success']);

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

                    <?php if ($documentSuccess !== ''): ?>
                        <div class="alert alert-success"><?= htmlspecialchars($documentSuccess); ?></div>
                    <?php endif; ?>
                    <?php if ($documentError !== ''): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($documentError); ?></div>
                    <?php endif; ?>

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
                            <a href="mailto:<?= COMPANY_EMAIL; ?>"><?= COMPANY_EMAIL; ?></a> or call
                            <a href="tel:<?= str_replace(' ', '', COMPANY_PHONE_SUPPORT); ?>"><?= COMPANY_PHONE_SUPPORT; ?></a>.
                        </p>
                    <?php endif; ?>

                    <ul class="list-unstyled mt-4">
                        <li><strong>Business name:</strong> <?= htmlspecialchars($businessName !== '' ? $businessName : '—'); ?></li>
                        <li><strong>ID / passport number:</strong> <?= htmlspecialchars($identityNumber !== '' ? $identityNumber : '—'); ?></li>
                        <li>
                            <strong>Identity document:</strong>
                            <?php if ($documentUrl !== ''): ?>
                                <a href="<?= htmlspecialchars($documentUrl); ?>" target="_blank" rel="noopener">View what you sent</a>
                            <?php elseif ($documentName !== ''): ?>
                                <span class="text-danger">
                                    The file you sent is no longer stored on our server — please upload it again below.
                                </span>
                            <?php else: ?>
                                <span class="text-danger">Not provided — please upload it below.</span>
                            <?php endif; ?>
                        </li>
                        <li><strong>Account status:</strong> <?= $status === 'rejected' ? 'Not approved' : 'Awaiting review'; ?></li>
                    </ul>

                    <div class="card mt-4">
                        <div class="card-body">
                            <h5 class="card-title">
                                <?= $documentAvailable ? 'Send a different identity document' : 'Upload your identity document'; ?>
                            </h5>
                            <p class="text-muted">
                                A clear photo or scan of the national ID or passport shown above. JPG, PNG, WEBP or
                                PDF, up to 5 MB. Sending a document puts your application back in the review queue.
                            </p>
                            <form method="post" action="upload-document.php" enctype="multipart/form-data">
                                <div class="form-group">
                                    <label for="id_document">Identity document *</label>
                                    <input type="file" name="id_document" id="id_document" class="form-control-file"
                                        accept="image/jpeg,image/png,image/webp,application/pdf" required>
                                </div>
                                <button type="submit" class="btn btn-dark">Upload document</button>
                            </form>
                        </div>
                    </div>

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
