<?php include 'check.php';

$sellerStatuses = ['pending', 'approved', 'rejected'];

if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['userId']) && isset($_GET['sellerStatus'])) {
    $userId = (int) $_GET['userId'];
    $sellerStatus = $_GET['sellerStatus'];

    if ($userId > 0 && in_array($sellerStatus, $sellerStatuses, true)) {
        // Stored raw (SQL-escaped only) so the pending page can escape it once.
        $note = $query->escape($_GET['note'] ?? '');
        $query->update(
            'accounts',
            ['seller_status' => $sellerStatus, 'kyc_note' => $note],
            "where id = '$userId'"
        );
    }

    header("Location: ./sellers.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['userId']) && isset($_GET['newStatus']) && isset($_GET['userrole'])) {
    $userId = $_GET['userId'];
    $newStatus = $_GET['newStatus'];
    $query->update('accounts', ['status' => $newStatus], "where id = '$userId'");

    if ($_GET['userrole'] == 'seller') {
        header("Location: ./");
        exit;
    } else {
        header("Location: ./users.php");
    }
}

if ($_GET['userrole'] == 'seller') {
    header("Location: ./");
    exit;
} else {
    header("Location: ./users.php");
}
