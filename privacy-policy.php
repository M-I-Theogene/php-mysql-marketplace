<?php include './init.php'; $currentPage = basename($_SERVER['PHP_SELF']); ?>
<!DOCTYPE html>
<html lang="en"><head><meta charset="UTF-8"><title>Privacy Policy | Cypadi Shop</title>
<?php include __DIR__ . '/includes/favicon.php'; ?>
<link rel="stylesheet" href="./src/css/bootstrap.min.css"><link rel="stylesheet" href="./src/css/style.css"></head>
<body><?php include './includes/header.php'; ?>
<section class="shoping-cart spad"><div class="container">
<h3>Privacy Policy</h3>
<p><strong>Cypadi Ltd</strong>, Kigali, Rwanda. Effective July 2026. This policy explains what personal data Cypadi Shop collects, why, and your rights over it.</p>

<h5>1. Who is responsible for your data</h5>
<p>Cypadi Ltd is the data controller for personal data processed through <?= COMPANY_WEBSITE; ?>. You can reach us at <a href="mailto:<?= COMPANY_EMAIL; ?>"><?= COMPANY_EMAIL; ?></a> or <?= COMPANY_PHONE_SUPPORT; ?>.</p>

<h5>2. What we collect</h5>
<ul>
<li><strong>Account data:</strong> your full name, phone number, email address and username when you register.</li>
<li><strong>Order data:</strong> the products you buy, the amounts, the delivery address, city, province and country, and the date and time.</li>
<li><strong>Payment references:</strong> the mobile money network used, the number that paid, the PawaPay deposit reference and the payment status.</li>
<li><strong>Seller data (if you apply as a supplier):</strong> business name, country of operation, national ID or passport number and the identity document you send us.</li>
<li><strong>Technical data:</strong> limited server logs needed to keep the site secure and working.</li>
</ul>
<p>We do <strong>not</strong> collect or store your mobile money PIN, your bank credentials, or full card numbers. We never ask for them, and you should never share them with anyone claiming to represent Cypadi.</p>

<h5>3. Why we use it</h5>
<ul>
<li>To create and manage your account.</li>
<li>To process your order, take payment and arrange delivery.</li>
<li>To answer your questions and handle refunds and complaints.</li>
<li>To prevent fraud and money laundering, and to meet our legal record-keeping obligations.</li>
<li>To review and verify suppliers before their products are listed.</li>
</ul>
<p>We do not sell your personal data, and we do not use it for third-party advertising.</p>

<h5>4. Who we share it with</h5>
<ul>
<li><strong>PawaPay Ltd</strong>, our payment processor, and the licensed mobile money operators (MTN Mobile Money Rwanda, Airtel Money Rwanda), to collect your payment and confirm it.</li>
<li>Our delivery partners, limited to the address and contact details needed to deliver your order.</li>
<li>A competent authority, where the law requires it, or where we are obliged to report suspected fraud or money laundering.</li>
<li>A professional adviser or successor entity, where strictly necessary and under confidentiality.</li>
</ul>

<h5>5. How long we keep it</h5>
<p>We keep account and order records, payment references and supplier verification records for <strong>five years</strong> after the end of our relationship with you, as required for accounting and anti-money-laundering purposes. After that period they are deleted or anonymised.</p>

<h5>6. How we protect it</h5>
<p>Access to customer and seller data is limited to staff who need it. The site is served over HTTPS. Payment credentials are never stored on our servers, and administrative access is password protected and restricted. If a data breach occurs that affects your rights, we will notify you and the relevant authority as required by law.</p>

<h5>7. Your rights</h5>
<p>You may ask us to confirm what personal data we hold about you, to correct anything inaccurate, or to delete data we no longer need to keep. Write to <a href="mailto:info@cypadi.com">info@cypadi.com</a> and we will respond within 30 days. If you are not satisfied, you may complain to the National Cyber Security Authority of Rwanda.</p>

<h5>8. Cookies</h5>
<p>We use a session cookie to keep you logged in and a cookie to remember your username on this device. These are necessary for the site to work. We do not use advertising or tracking cookies.</p>

<h5>9. Changes</h5>
<p>If we change this policy, we will publish the new version on this page with a new effective date.</p>
</div></section><?php include './includes/footer.php'; ?></body></html>
