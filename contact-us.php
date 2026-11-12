<?php include './init.php'; $currentPage = basename($_SERVER['PHP_SELF']); ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Contact Cypadi Shop — phone, email, registered office and compliance contact for Cypadi Ltd, Kigali, Rwanda.">
    <title>Contact Us | Cypadi Shop</title>
    <?php include __DIR__ . '/includes/favicon.php'; ?>
    <link rel="stylesheet" href="./src/css/bootstrap.min.css">
    <link rel="stylesheet" href="./src/css/style.css">
</head>

<body>
    <?php include './includes/header.php'; ?>
    <section class="shoping-cart spad">
        <div class="container">
            <h3>Contact Us</h3>
            <p class="mt-3">
                <?= COMPANY_LEGAL_NAME; ?> runs Cypadi Shop from <?= companyAddress(); ?>. Use the contacts below and we
                will respond within one business day on weekdays.
            </p>

            <div class="row mt-4">
                <div class="col-lg-6">
                    <h5>Orders, delivery and refunds</h5>
                    <p>
                        Phone: <a href="tel:<?= str_replace(' ', '', COMPANY_PHONE_SUPPORT); ?>"><?= COMPANY_PHONE_SUPPORT; ?></a><br>
                        Email: <a href="mailto:<?= COMPANY_EMAIL; ?>"><?= COMPANY_EMAIL; ?></a>
                    </p>
                    <p class="text-muted">Quote your order number so we can find the order immediately.</p>

                    <h5 class="mt-4">Office</h5>
                    <p>
                        Address: <?= companyAddress(); ?>
                    </p>
                </div>
                <div class="col-lg-6">
                    <h5>Compliance and data protection</h5>
                    <p>
                        Email: <a href="mailto:<?= COMPANY_EMAIL_COMPLIANCE; ?>"><?= COMPANY_EMAIL_COMPLIANCE; ?></a>
                    </p>
                    <p class="text-muted">
                        Use this address for anti-money-laundering questions, data protection requests, or anything a
                        payment provider or regulator needs to raise with us.
                    </p>

                    <h5 class="mt-4">Registered details</h5>
                    <p>
                        <?= COMPANY_LEGAL_NAME; ?><br>
                        <?php if (COMPANY_TIN !== ''): ?>TIN: <?= COMPANY_TIN; ?><br><?php endif; ?>
                        <?= companyAddress(); ?>
                    </p>
                </div>
            </div>

            <p class="mt-4">
                Before writing, you may find the answer in our <a href="refund-policy.php">Refund and Returns Policy</a>,
                <a href="terms.php">Terms and Conditions</a> or <a href="privacy-policy.php">Privacy Policy</a>.
            </p>
        </div>
    </section>
    <?php include './includes/footer.php'; ?>
</body>

</html>
