<?php
http_response_code(200);
header('Content-Type: application/json');
include 'config.php';
$query = new Database();
$con = $query->getMysqli();
require_once __DIR__ . '/includes/pawapay.php';

$payload = file_get_contents('php://input');
$data = json_decode($payload, true);
if (!is_array($data)) {
    echo json_encode(['received' => false]);
    exit;
}

$depositId = trim($data['depositId'] ?? '');
$depositStatus = strtoupper(trim($data['status'] ?? ''));
if ($depositId === '' || $depositStatus === '') {
    echo json_encode(['received' => false]);
    exit;
}

$failureMessage = (string) ($data['failureReason']['failureMessage'] ?? '');
pawapayApplyDepositStatus($con, $depositId, $depositStatus, $failureMessage);
echo json_encode(['received' => true]);
