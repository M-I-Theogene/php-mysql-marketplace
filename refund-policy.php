<?php include './init.php'; $currentPage = basename($_SERVER['PHP_SELF']); ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Refund and Returns Policy for Cypadi Shop, operated by Cypadi Ltd, Kigali, Rwanda. How to return an item and how refunds are paid back.">
    <title>Refund and Returns Policy | Cypadi Shop</title>
    <?php include __DIR__ . '/includes/favicon.php'; ?>
    <link rel="stylesheet" href="./src/css/bootstrap.min.css">
    <link rel="stylesheet" href="./src/css/style.css">
</head>

<body>
    <?php include './includes/header.php'; ?>
    <section class="shoping-cart spad">
        <div class="container">
            <h3>Refund and Returns Policy</h3>

            <p class="mt-3">
                This policy applies to every order placed on <?= COMPANY_WEBSITE; ?>, which is operated by
                <strong><?= COMPANY_LEGAL_NAME; ?></strong>. It forms part of our
                <a href="terms.php">Terms and Conditions</a>.
            </p>
            <p><strong>Last updated:</strong> <?= date('F Y'); ?></p>

            <h5>1. Your right to cancel before dispatch</h5>
            <p>
                You may cancel an order at any time before it is dispatched. Contact us on <?= COMPANY_PHONE_SUPPORT; ?> or
                at <a href="mailto:<?= COMPANY_EMAIL; ?>"><?= COMPANY_EMAIL; ?></a> with your order number. We refund the
                full amount paid.
            </p>

            <h5>2. Damaged, defective or incorrect items</h5>
            <p>
                Tell us within <strong>7 days of delivery</strong> if the item arrived damaged, is defective, or is not what
                you ordered. Send the order number and photographs showing the problem. We will arrange a replacement, a
                repair, or a refund — whichever you prefer and whichever is possible for that product.
            </p>
            <p>
                Where the fault is ours, we cover the cost of returning the item. Do not send anything back before we have
                confirmed the return, because a parcel that arrives without a matching order cannot be traced.
            </p>

            <h5>3. Change of mind</h5>
            <p>
                We accept change-of-mind returns within <strong>7 days of delivery</strong> provided the item is unused, in
                its original packaging, with all accessories and manuals. You pay the return delivery cost in that case.
                Some items cannot be returned for hygiene or legal reasons: opened SIM cards and airtime, underwear,
                perishable goods, and any product that has been installed or activated.
            </p>

            <h5>4. Items we cannot refund</h5>
            <ul>
                <li>Products damaged after delivery through misuse, accident or modification.</li>
                <li>Products returned without their serial number or with a broken seal where the seal matters.</li>
                <li>Digital content and services once they have been delivered.</li>
                <li>Any item listed as non-returnable on its product page at the time of purchase.</li>
            </ul>

            <h5>5. How refunds are paid</h5>
            <p>
                Refunds are paid back to the <strong>original paying mobile money number</strong>, through the same payment
                channel used at checkout (MTN Mobile Money or Airtel Money, via PawaPay). We cannot refund to a different
                number, to a bank account, or in cash — the payment provider only allows the money to return along the path
                it came.
            </p>
            <p>
                Once we approve a refund we initiate it within <strong>3 business days</strong>. The mobile money operator
                then normally credits the money within a further 1–5 business days. If the number that paid has been closed
                or is no longer reachable, contact us and we will work out an alternative with you.
            </p>

            <h5>6. Failed, reversed and duplicate payments</h5>
            <p>
                If a payment is taken but the order is not created, or the same order is charged twice, the extra amount is
                refunded in full. Contact us with the mobile money transaction reference and the date of payment and we will
                trace it with the operator.
            </p>

            <h5>7. Orders cancelled by us</h5>
            <p>
                If a product becomes unavailable after you have paid, or we cannot deliver to your address, we cancel the
                order and refund the full amount, including any delivery charge. We will tell you before we cancel.
            </p>

            <h5>8. Delivery charges</h5>
            <p>
                Delivery charges are refunded only where the whole order is cancelled by us, or where the item was faulty or
                incorrect. For a change-of-mind return, the original delivery charge is not refunded.
            </p>

            <h5>9. Your statutory rights</h5>
            <p>
                Nothing in this policy excludes or limits any right you have under Rwandan consumer protection law. Where
                this policy and the law disagree, the law applies.
            </p>

            <h5>10. How to start a return or refund</h5>
            <p>
                Email <a href="mailto:<?= COMPANY_EMAIL; ?>"><?= COMPANY_EMAIL; ?></a> or call
                <a href="tel:<?= str_replace(' ', '', COMPANY_PHONE_SUPPORT); ?>"><?= COMPANY_PHONE_SUPPORT; ?></a> with:
            </p>
            <ul>
                <li>your order number;</li>
                <li>the product name;</li>
                <li>what is wrong, with photographs if the item is damaged or incorrect;</li>
                <li>whether you want a replacement, a repair or a refund.</li>
            </ul>
            <p>
                We reply within one business day on weekdays.
            </p>

            <h5>11. Company details</h5>
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
