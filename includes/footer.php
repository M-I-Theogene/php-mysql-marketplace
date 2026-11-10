<?php
// Root-relative base, so these links also resolve when the footer is included
// from a subdirectory such as seller/pending.php.
$footerBase = appBaseUrl() . '/';
?>
<footer class="footer spad">

    <div class="container">

        <div class="row">

            <div class="col-lg-3 col-md-6 col-sm-6">

                <div class="footer__about">

                    <div class="footer__about__logo">

                        <a href="<?= $footerBase; ?>"><img src="<?= $footerBase; ?>assets/images/cypadi-shop.png" alt="Cypadi Shop" style="height: 110px; width: auto; max-width: 100%;"></a>

                    </div>

                    <ul>

                        <li>Address: Kigali, Rwanda</li>

                        <li>Phone: +250 784 801 809</li>

                        <li>Email: info@cypadi.com</li>

                    </ul>

                </div>

            </div>

            <div class="col-lg-4 col-md-6 col-sm-6 offset-lg-1">

                <div class="footer__widget">

                    <h6>Information</h6>

                    <ul>

                        <li><a href="<?= $footerBase; ?>about-us.php">About Us</a></li>

                        <li><a href="<?= $footerBase; ?>contact-us.php">Contact</a></li>

                        <li><a href="<?= $footerBase; ?>terms.php">Terms & Conditions</a></li>

                    </ul>

                    <ul>

                        <li><a href="<?= $footerBase; ?>privacy-policy.php">Privacy Policy</a></li>

                        <li><a href="<?= $footerBase; ?>refund-policy.php">Refund & Returns</a></li>

                        <li><a href="<?= $footerBase; ?>signup/">Become a Seller</a></li>

                    </ul>

                </div>

            </div>

            <div class="col-lg-4 col-md-12">

                <div class="footer__widget">

                    <h6>About Cypadi</h6>

                    <p>Cypadi Shop is a Rwandan online store operated by Cypadi Ltd. Customers pay by MTN MoMo or Airtel Money at checkout.</p>

                    <p class="mt-2">Cypadi Ltd &middot; Kigali, Rwanda</p>

                    <p>&copy; <?= date('Y'); ?> Cypadi Ltd. All rights reserved.</p>

                </div>

            </div>

        </div>

    </div>

</footer>
