<?php

/**
 * Streams a stored seller identity document to an authorised viewer.
 *
 * Identity documents are not linked as static files: the request has to come
 * from an administrator, or from the seller who uploaded that very document.
 * A record whose file has gone missing answers with a readable page instead of
 * the web server's bare "Object not found".
 */

require_once __DIR__ . '/init.php';
require_once __DIR__ . '/includes/kyc-upload.php';

const KYC_DOCUMENT_HTTP_BAD_REQUEST = 400;
const KYC_DOCUMENT_HTTP_FORBIDDEN = 403;
const KYC_DOCUMENT_HTTP_NOT_FOUND = 404;

/**
 * Ends the request with a small standalone HTML page.
 *
 * The page deliberately avoids the shop templates: an error here can be
 * triggered from any role, and a missing header include would turn a clear
 * message into a PHP warning.
 */
function kycDocumentFail($status, $heading, $message, $detail = '')
{
    http_response_code($status);
    header('Content-Type: text/html; charset=utf-8');
    ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($heading); ?> | Cypadi Shop</title>
    <link rel="icon" href="<?= htmlspecialchars(appBaseUrl()); ?>/favicon.ico">
    <style>
        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f5f6f8;
            color: #1f2430;
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, Arial, sans-serif;
            padding: 24px;
        }

        .card {
            background: #fff;
            border: 1px solid #e3e6ec;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(23, 31, 48, .08);
            max-width: 520px;
            width: 100%;
            padding: 32px;
        }

        h1 {
            font-size: 20px;
            margin: 0 0 12px;
        }

        p {
            font-size: 15px;
            line-height: 1.6;
            margin: 0 0 12px;
            color: #47506a;
        }

        code {
            display: block;
            margin: 16px 0;
            padding: 10px 12px;
            background: #f2f4f8;
            border-radius: 6px;
            font-size: 13px;
            color: #1f2430;
            word-break: break-all;
        }

        .status {
            display: inline-block;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: .06em;
            text-transform: uppercase;
            color: #b3261e;
            margin-bottom: 10px;
        }

        a.button {
            display: inline-block;
            margin-top: 8px;
            padding: 10px 18px;
            border-radius: 6px;
            background: #1f2430;
            color: #fff;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
        }
    </style>
</head>

<body>
    <div class="card">
        <span class="status">Error <?= (int) $status; ?></span>
        <h1><?= htmlspecialchars($heading); ?></h1>
        <p><?= htmlspecialchars($message); ?></p>
        <?php if ($detail !== ''): ?>
            <code><?= htmlspecialchars($detail); ?></code>
        <?php endif; ?>
        <a class="button" href="javascript:history.length > 1 ? history.back() : location.assign('<?= htmlspecialchars(appBaseUrl()); ?>/')">Go back</a>
    </div>
</body>

</html>
    <?php
    exit;
}

$role = isset($_SESSION['role']) ? (string) $_SESSION['role'] : '';

if (empty($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    kycDocumentFail(
        KYC_DOCUMENT_HTTP_FORBIDDEN,
        'Sign in required',
        'Identity documents can only be opened from a signed-in account. Please log in and try again.'
    );
}

$requestedFile = kycDocumentFileName($_GET['file'] ?? '');

if ($requestedFile === '') {
    kycDocumentFail(
        KYC_DOCUMENT_HTTP_BAD_REQUEST,
        'No document requested',
        'The link did not name a document. Go back to the seller page and use the View document button.'
    );
}

// Administrators may open any document; a seller may open only their own.
$isAllowed = $role === 'admin';

if (!$isAllowed && $role === 'seller') {
    $ownAccount = $query->select(
        'accounts',
        'id_document',
        'WHERE id = "' . (int) $_SESSION['id'] . '"'
    );
    $ownFile = kycDocumentFileName($ownAccount[0]['id_document'] ?? '');
    $isAllowed = $ownFile !== '' && $ownFile === $requestedFile;
}

if (!$isAllowed) {
    kycDocumentFail(
        KYC_DOCUMENT_HTTP_FORBIDDEN,
        'Not allowed',
        'This identity document belongs to another account and cannot be opened from here.'
    );
}

$documentPath = kycDocumentPath($requestedFile);

if ($documentPath === '') {
    kycDocumentFail(
        KYC_DOCUMENT_HTTP_NOT_FOUND,
        'The document file is missing',
        'This seller record still points at an identity document, but the file itself is no longer on the server, '
            . 'so there is nothing to display. Ask the seller to upload the document again, then review the '
            . 'application once more.',
        $requestedFile
    );
}

$size = filesize($documentPath);

if ($size === false) {
    kycDocumentFail(
        KYC_DOCUMENT_HTTP_NOT_FOUND,
        'The document file could not be read',
        'The identity document exists on the server but its size could not be determined. Please try again, and '
            . 'contact support if this keeps happening.',
        $requestedFile
    );
}

// Nothing may be flushed before the image itself, or the browser receives a
// corrupt file made of PHP notices followed by JPEG bytes.
while (ob_get_level() > 0) {
    ob_end_clean();
}

header('Content-Type: ' . kycDocumentMimeType($requestedFile));
header('Content-Length: ' . $size);
header('Content-Disposition: inline; filename="' . $requestedFile . '"');
header('X-Content-Type-Options: nosniff');
header('Cache-Control: private, no-store, max-age=0');
header('Pragma: no-cache');

readfile($documentPath);
exit;
