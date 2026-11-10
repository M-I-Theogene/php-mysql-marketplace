<?php

/**
 * Seller identity-document upload.
 *
 * Sellers must attach a scan or photo of their national ID / passport during
 * signup so the administrator reviewing the application has something to check
 * against the ID number that was typed in.
 */

/** Directory (relative to the application root) holding uploaded documents. */
const KYC_UPLOAD_DIRECTORY = 'src/images/kyc';

/** Largest document we accept, in bytes. */
const KYC_MAX_UPLOAD_BYTES = 5242880; // 5 MB

/** Extensions accepted for an identity document. */
const KYC_ALLOWED_EXTENSIONS = array('jpg', 'jpeg', 'png', 'webp', 'pdf');

/**
 * Absolute path of the upload directory, creating it when missing.
 */
function kycUploadPath()
{
    $directory = dirname(__DIR__) . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, KYC_UPLOAD_DIRECTORY);

    if (!is_dir($directory)) {
        mkdir($directory, 0755, true);
    }

    return $directory;
}

/**
 * Normalises the value stored in `accounts.id_document` into a plain file name
 * inside the upload directory, or an empty string when it cannot be trusted.
 *
 * Stored values come either from this module or from an older dump, so the
 * only things enforced here are that path traversal is impossible and that the
 * extension is one we are willing to stream back.
 */
function kycDocumentFileName($storedName)
{
    $name = basename(str_replace('\\', '/', trim((string) $storedName)));

    if ($name === '' || $name === '.' || $name === '..') {
        return '';
    }

    $extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));

    if (!in_array($extension, KYC_ALLOWED_EXTENSIONS, true)) {
        return '';
    }

    return $name;
}

/**
 * Absolute path of a stored document, or an empty string when the file is not
 * on disk. Unlike kycUploadPath() this never creates the directory, because it
 * also runs while merely reading a document back.
 */
function kycDocumentPath($storedName)
{
    $fileName = kycDocumentFileName($storedName);

    if ($fileName === '') {
        return '';
    }

    $path = dirname(__DIR__) . DIRECTORY_SEPARATOR
        . str_replace('/', DIRECTORY_SEPARATOR, KYC_UPLOAD_DIRECTORY)
        . DIRECTORY_SEPARATOR . $fileName;

    return is_file($path) ? $path : '';
}

/**
 * Whether the document a row points at can actually be served.
 *
 * A record can outlive its file (restored dump, manual clean-up), and without
 * this check the admin review page renders a link that only produces a server
 * 404, which reads as "the site is broken" instead of "the file is gone".
 */
function kycDocumentAvailable($storedName)
{
    return kycDocumentPath($storedName) !== '';
}

/**
 * Content type used when streaming a stored document back to the browser.
 */
function kycDocumentMimeType($storedName)
{
    $extension = strtolower(pathinfo((string) $storedName, PATHINFO_EXTENSION));

    $types = array(
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'webp' => 'image/webp',
        'pdf' => 'application/pdf',
    );

    return $types[$extension] ?? 'application/octet-stream';
}

/**
 * URL that streams a stored document through kyc-document.php.
 *
 * Documents are national IDs, so they are never linked as a raw static file:
 * the script checks the session (administrator, or the seller who uploaded it)
 * before a byte is sent, and answers with a readable page when the file is
 * missing instead of Apache's "Object not found".
 */
function kycDocumentUrl($storedName)
{
    $fileName = kycDocumentFileName($storedName);

    if ($fileName === '') {
        return '';
    }

    return appBaseUrl() . '/kyc-document.php?file=' . rawurlencode($fileName);
}

/**
 * Removes a stored document, used when the registration it belonged to failed
 * so orphaned files do not pile up in the upload directory.
 */
function kycDeleteDocument($fileName)
{
    if (empty($fileName)) {
        return;
    }

    // Only ever touch a plain file name inside the upload directory.
    $fileName = basename((string) $fileName);
    $path = kycUploadPath() . DIRECTORY_SEPARATOR . $fileName;

    if (is_file($path)) {
        unlink($path);
    }
}

/**
 * Validates and stores the uploaded identity document.
 *
 * @param array|null $file Entry from $_FILES.
 * @return array{ok: bool, file: string, error: string}
 */
function kycStoreDocument($file)
{
    $result = array('ok' => false, 'file' => '', 'error' => '');

    if (!is_array($file) || !isset($file['error'])) {
        $result['error'] = 'Please attach a photo or scan of your identity document.';
        return $result;
    }

    if ($file['error'] === UPLOAD_ERR_NO_FILE) {
        $result['error'] = 'Please attach a photo or scan of your identity document.';
        return $result;
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $result['error'] = 'The identity document could not be uploaded. Please try again.';
        return $result;
    }

    if (!is_uploaded_file($file['tmp_name'])) {
        $result['error'] = 'The identity document could not be uploaded. Please try again.';
        return $result;
    }

    if ($file['size'] <= 0 || $file['size'] > KYC_MAX_UPLOAD_BYTES) {
        $result['error'] = 'The identity document must be smaller than 5 MB.';
        return $result;
    }

    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if (!in_array($extension, KYC_ALLOWED_EXTENSIONS, true)) {
        $result['error'] = 'The identity document must be a JPG, PNG, WEBP image or a PDF.';
        return $result;
    }

    $fileName = 'kyc_' . bin2hex(random_bytes(16)) . '.' . $extension;

    if (!move_uploaded_file($file['tmp_name'], kycUploadPath() . DIRECTORY_SEPARATOR . $fileName)) {
        $result['error'] = 'The identity document could not be saved. Please try again.';
        return $result;
    }

    $result['ok'] = true;
    $result['file'] = $fileName;

    return $result;
}
