<?php include './init.php'; $currentPage = basename($_SERVER['PHP_SELF']); ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Terms and Conditions for shopping on Cypadi Shop, operated by Cypadi Ltd, Kigali, Rwanda.">
    <title>Terms and Conditions | Cypadi Shop</title>
    <?php include __DIR__ . '/includes/favicon.php'; ?>
    <link rel="stylesheet" href="./src/css/bootstrap.min.css">
    <link rel="stylesheet" href="./src/css/style.css">
</head>

<body>
    <?php include './includes/header.php'; ?>
    <section class="shoping-cart spad">
        <div class="container">
            <h3>Terms and Conditions</h3>

            <p class="mt-3">
                These terms govern your use of <strong><?= COMPANY_WEBSITE; ?></strong> and any order you place through it.
                The site is operated by <strong><?= COMPANY_LEGAL_NAME; ?></strong>, a company registered in Rwanda, with
                its registered office at <?= companyAddress(); ?>. By placing an order you accept these terms.
            </p>
            <p><strong>Last updated:</strong> <?= date('F Y'); ?></p>

            <h5>1. Who you are contracting with</h5>
            <p>
                <?= COMPANY_LEGAL_NAME; ?> is the seller of record for every product ordered on this website. Your contract
                of sale is with <?= COMPANY_LEGAL_NAME; ?>, and payment is made to <?= COMPANY_LEGAL_NAME; ?>.
                Products are supplied by vetted suppliers whom we review before allowing them to list, but the sale itself
                is ours: if something goes wrong with an order, you deal with us, not with the supplier.
            </p>

            <h5>2. Accounts</h5>
            <p>
                You must give accurate information when you register and keep it up to date. You are responsible for keeping
                your password confidential and for activity carried out under your account. We may suspend or close an
                account that is used fraudulently, that breaches these terms, or that we are required to restrict by law.
            </p>

            <h5>3. Orders and payment</h5>
            <p>
                Prices are shown in Rwandan francs (RWF) and include applicable taxes. Payment is made at checkout by MTN
                Mobile Money or Airtel Money, processed through PawaPay and the licensed mobile money operator. We never ask
                for your mobile money PIN, and we never store it.
            </p>
            <p>
                An order becomes binding on us once payment is confirmed by the mobile money operator. If a payment fails,
                is reversed, or is not confirmed, the order is not binding and no product will be dispatched.
            </p>
            <p>
                We may refuse or cancel an order where the product is unavailable, where the price was listed in error, or
                where we suspect fraud. If we cancel an order you have already paid for, we refund the full amount to the
                number that paid.
            </p>

            <h5>4. Delivery</h5>
            <p>
                You must give a valid delivery address and a reachable phone number inside Rwanda. Delivery times shown on
                the site are estimates, not guarantees. Risk in the goods passes to you on delivery. If a product turns out
                to be unavailable after payment, we will contact you and refund the amount paid.
            </p>

            <h5>5. Refunds and returns</h5>
            <p>
                Refunds and returns are handled under our <a href="refund-policy.php">Refund and Returns Policy</a>, which
                forms part of these terms. Refunds are made to the original paying mobile money number.
            </p>

            <h5>6. Suppliers and product listings</h5>
            <p>
                Suppliers who wish to list products through <?= COMPANY_LEGAL_NAME; ?> apply for a seller account. We verify
                the business, the owner's identity and the product categories before any product is published, and we may
                refuse or withdraw a listing at any time. Suppliers must not list illegal goods, counterfeits,
                pharmaceuticals, wildlife products, weapons, or anything prohibited by Rwandan law or by our platform
                policies.
            </p>

            <h5>7. Acceptable use</h5>
            <p>
                You must not use the site to commit fraud, to test stolen payment instruments, to interfere with its
                operation, or to place orders you do not intend to pay for. We may cancel orders and report suspected fraud
                to the relevant authorities, including the Rwanda Investigation Bureau and the Financial Intelligence Centre
                where a suspicious transaction is involved.
            </p>

            <h5>8. Your data</h5>
            <p>
                We process personal data as described in our <a href="privacy-policy.php">Privacy Policy</a>, in accordance
                with Rwanda's Law No. 058/2021 on the protection of personal data and privacy. We keep records for the
                period required by Rwandan tax and anti-money-laundering law.
            </p>

            <h5>9. Limitation of liability</h5>
            <p>
                To the extent permitted by Rwandan law, <?= COMPANY_LEGAL_NAME; ?> is not liable for indirect or
                consequential loss arising from use of the site. Nothing in these terms limits liability that cannot
                lawfully be limited, and nothing here affects your statutory rights as a consumer.
            </p>

            <h5>10. Changes to these terms</h5>
            <p>
                We may update these terms. The version published on this page at the time of your order applies to that
                order. The date at the top of this page shows when the current version took effect.
            </p>

            <h5>11. Governing law and disputes</h5>
            <p>
                These terms are governed by the laws of Rwanda. If you have a complaint, contact us first at
                <a href="mailto:<?= COMPANY_EMAIL; ?>"><?= COMPANY_EMAIL; ?></a> or <?= COMPANY_PHONE_SUPPORT; ?> and we
                will try to resolve it within 10 business days. Unresolved disputes are subject to the competent courts of
                Rwanda.
            </p>

            <h5>12. Company details</h5>
            <p>
                <?= COMPANY_LEGAL_NAME; ?><br>
                <?php if (COMPANY_TIN !== ''): ?>TIN: <?= COMPANY_TIN; ?><br><?php endif; ?>
                Registered office: <?= companyAddress(); ?><br>
                Email: <a href="mailto:<?= COMPANY_EMAIL; ?>"><?= COMPANY_EMAIL; ?></a><br>
                Support: <?= COMPANY_PHONE_SUPPORT; ?>
            </p>
        </div>
    </section>
    <?php include './includes/footer.php'; ?>
</body>

</html>
