<?php

/**
 * Lets a seller whose application is still under review replace the identity
 * document on file.
 *
 * The review page is the only entry point, and it is shown to pending and
 * rejected sellers only. A replacement puts the application back in the queue
 * so the administrator looks at the new document rather than the old one.
 */

require_once __DIR__ . '/../init.php';
require_once __DIR__ . '/../includes/kyc-upload.php';

const SELLER_DOCUMENT_FLASH_ERROR = 'kyc_document_error';
const SELLER_DOCUMENT_FLASH_SUCCESS = 'kyc_document_success';

if (empty($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: ../login/');
    exit;
}

if (($_SESSION['role'] ?? '') !== 'seller') {
    header('Location: ../');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ./pending.php');
    exit;
}

$sellerId = (int) $_SESSION['id'];

$account = $query->select('accounts', 'id_document, seller_status', 'WHERE id = ' . $sellerId);
$account = $account[0] ?? array();

if (empty($account)) {
    header('Location: ../logout/');
    exit;
}

// An approved seller never sees the review page, so refuse the upload rather
// than silently reopening a finished application.
if (($account['seller_status'] ?? 'pending') === 'approved') {
    header('Location: ./');
    exit;
}

$document = kycStoreDocument($_FILES['id_document'] ?? null);

if (!$document['ok']) {
    $_SESSION[SELLER_DOCUMENT_FLASH_ERROR] = $document['error'];
    header('Location: ./pending.php');
    exit;
}

$previousDocument = (string) ($account['id_document'] ?? '');

$query->update(
    'accounts',
    array(
        'id_document' => $query->escape($document['file']),
        'seller_status' => 'pending',
        'kyc_note' => '',
    ),
    "where id = '$sellerId'"
);

// Drop the file this one replaces, but only after the row points somewhere new.
if ($previousDocument !== '' && $previousDocument !== $document['file']) {
    kycDeleteDocument($previousDocument);
}

$_SESSION[SELLER_DOCUMENT_FLASH_SUCCESS] =
    'Your identity document has been received. Your application is back in the review queue.';

header('Location: ./pending.php');
exit;
