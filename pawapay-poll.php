<?php
include 'check.php';
require_once __DIR__ . '/includes/pawapay.php';
header('Content-Type: application/json');

$depositId = trim($_GET['deposit'] ?? '');
$orderNumber = trim($_GET['order'] ?? '');
if ($depositId === '' || $orderNumber === '') {
    http_response_code(400);
    echo json_encode(['ok' => false]);
    exit;
}

$userId = (int) $_SESSION['id'];
$depositIdEsc = mysqli_real_escape_string($con, $depositId);
$orderNumberEsc = mysqli_real_escape_string($con, $orderNumber);

$orderQuery = mysqli_query(
    $con,
    "SELECT paymentStatus FROM orders WHERE depositId='$depositIdEsc' AND orderNumber='$orderNumberEsc' AND user_id='$userId' LIMIT 1"
);
if (!$orderQuery || mysqli_num_rows($orderQuery) === 0) {
    http_response_code(404);
    echo json_encode(['ok' => false]);
    exit;
}
$order = mysqli_fetch_assoc($orderQuery);

if ($order['paymentStatus'] === 'paid') {
    echo json_encode(['ok' => true, 'status' => 'COMPLETED', 'redirect' => 'my-orders.php']);
    exit;
}
if ($order['paymentStatus'] === 'failed') {
    echo json_encode(['ok' => true, 'status' => 'FAILED', 'redirect' => 'payment.php']);
    exit;
}

$pawapay = new PawaPayClient();
$response = $pawapay->checkDepositStatus($depositId);
$parsed = pawapayParseDepositSearchResult($response['body']);

if ($parsed['search'] === 'FOUND' && $parsed['depositStatus'] !== '') {
    pawapayApplyDepositStatus($con, $depositId, $parsed['depositStatus'], $parsed['failureMessage']);
    if ($parsed['depositStatus'] === 'COMPLETED') {
        unset($_SESSION['gtotal'], $_SESSION['delivery_address'], $_SESSION['delivery_city'], $_SESSION['delivery_province'], $_SESSION['delivery_country']);
        echo json_encode(['ok' => true, 'status' => 'COMPLETED', 'redirect' => 'my-orders.php']);
        exit;
    }
    if ($parsed['depositStatus'] === 'FAILED') {
        echo json_encode(['ok' => true, 'status' => 'FAILED', 'message' => $parsed['failureMessage'], 'redirect' => 'payment.php']);
        exit;
    }
}

echo json_encode(['ok' => true, 'status' => 'PROCESSING', 'message' => 'Waiting for approval...']);
exit;
