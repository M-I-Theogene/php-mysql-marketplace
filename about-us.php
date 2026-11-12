<?php include './init.php'; $currentPage = basename($_SERVER['PHP_SELF']); ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="About Cypadi Shop — a Rwandan online store operated by Cypadi Ltd, Kigali. Pay by MTN MoMo or Airtel Money.">
    <title>About Us | Cypadi Shop</title>
    <?php include __DIR__ . '/includes/favicon.php'; ?>
    <link rel="stylesheet" href="./src/css/bootstrap.min.css">
    <link rel="stylesheet" href="./src/css/style.css">
</head>

<body>
    <?php include './includes/header.php'; ?>
    <section class="shoping-cart spad">
        <div class="container">
            <h3>About Cypadi Shop</h3>

            <p class="mt-3">
                Cypadi Shop is an online store operated by <strong><?= COMPANY_LEGAL_NAME; ?></strong> in Rwanda. We source
                products from vetted suppliers and sell them directly to customers as the seller of record: your order is
                with <?= COMPANY_LEGAL_NAME; ?> from checkout to delivery.
            </p>

            <p>
                Customers browse the catalogue, place an order, and pay in Rwandan francs by MTN Mobile Money or Airtel Money
                at checkout. Payments are processed through PawaPay and the licensed mobile money operator. We never ask
                for, see or store your mobile money PIN.
            </p>

            <h5>How we work with suppliers</h5>
            <p>
                Suppliers who wish to sell through Cypadi apply for a seller account. Before any item is listed we verify the
                business name, country of operation, the owner's identity document and the product categories, and we review
                listings against our platform policies. A new seller account cannot publish a product until that review is
                complete; products from unverified suppliers are never published.
            </p>

            <h5>Orders and delivery</h5>
            <p>
                Orders are confirmed once payment is successful and are delivered to the address you provide at checkout
                inside Rwanda. If a product cannot be delivered, the payment is refunded to the number that paid. See our
                <a href="refund-policy.php">Refund and Returns Policy</a> for how that works.
            </p>

            <h5>What we do with your data</h5>
            <p>
                We keep the details needed to fulfil your order and to meet our record-keeping obligations. We do not sell
                your data. See our <a href="privacy-policy.php">Privacy Policy</a>, <a href="terms.php">Terms and
                Conditions</a> and <a href="refund-policy.php">Refund and Returns Policy</a>.
            </p>

            <h5>Registered details</h5>
            <p>
                <?= COMPANY_LEGAL_NAME; ?><br>
                <?php if (COMPANY_TIN !== ''): ?>TIN: <?= COMPANY_TIN; ?><br><?php endif; ?>
                Registered office: <?= companyAddress(); ?>
            </p>

            <h5>Contact</h5>
            <p>
                Support: <a href="tel:<?= str_replace(' ', '', COMPANY_PHONE_SUPPORT); ?>"><?= COMPANY_PHONE_SUPPORT; ?></a><br>
                Email: <a href="mailto:<?= COMPANY_EMAIL; ?>"><?= COMPANY_EMAIL; ?></a><br>
                Compliance: <a href="mailto:<?= COMPANY_EMAIL_COMPLIANCE; ?>"><?= COMPANY_EMAIL_COMPLIANCE; ?></a>
            </p>
        </div>
    </section>
    <?php include './includes/footer.php'; ?>
</body>

</html>
