<?php
include 'init.php';
header('Content-Type: application/json');

if (empty($_SESSION['loggedin']) || empty($_SESSION['id'])) {
    echo json_encode(['ok' => false, 'login' => true]);
    exit;
}

if (isset($_GET['product_id'])) {
    $productId = (int) $_GET['product_id'];
    $userId = (int) $_SESSION['id'];

    $wishData = [
        'user_id' => $userId,
        'product_id' => $productId,
    ];

    if (!isset($query->select('wishes', 'id', "WHERE product_id = $productId AND user_id = $userId")[0]['id'])) {
        $query->insert('wishes', $wishData);
    }

    echo json_encode(['ok' => true]);
    exit;
}

echo json_encode(['ok' => false]);
