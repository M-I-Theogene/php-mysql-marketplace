<?php
/**
 * Favicon tags, shared by every page.
 *
 * The site mark is built from the real logo (the cart glyph on a brand-gold
 * tile) by _tmp_make_favicon.php, which writes:
 *   /favicon.ico                       multi-size icon for the browser tab
 *   /assets/images/favicon-16.png      16px tab icon
 *   /assets/images/favicon-32.png      32px tab icon
 *   /assets/images/favicon-48.png      48px shortcut icon
 *   /assets/images/favicon-192.png     Android home screen
 *   /assets/images/apple-touch-icon.png iOS home screen
 *
 * Pages in subdirectories (admin/, seller/, login/, signup/) include this file
 * too, so every URL is built from the application root rather than the page's
 * own directory.
 */
$faviconBase = function_exists('appBaseUrl') ? appBaseUrl() : '';
?>
<link rel="icon" href="<?= $faviconBase; ?>/favicon.ico" sizes="any">
<link rel="icon" type="image/png" sizes="16x16" href="<?= $faviconBase; ?>/assets/images/favicon-16.png">
<link rel="icon" type="image/png" sizes="32x32" href="<?= $faviconBase; ?>/assets/images/favicon-32.png">
<link rel="icon" type="image/png" sizes="48x48" href="<?= $faviconBase; ?>/assets/images/favicon-48.png">
<link rel="icon" type="image/png" sizes="192x192" href="<?= $faviconBase; ?>/assets/images/favicon-192.png">
<link rel="apple-touch-icon" sizes="180x180" href="<?= $faviconBase; ?>/assets/images/apple-touch-icon.png">
