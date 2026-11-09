<?php
include 'init.php';
header('Content-Type: application/json');

if (empty($_SESSION['loggedin']) || empty($_SESSION['id'])) {
    echo json_encode(['ok' => false, 'login' => true]);
    exit;
}

if (isset($_GET['product_id'])) {
    $productId = (int) $_GET['product_id'];
    $quantity = isset($_GET['quantity']) ? (int) $_GET['quantity'] : 1;
    $userId = (int) $_SESSION['id'];

    $cartData = [
        'user_id' => $userId,
        'product_id' => $productId,
        'number_of_products' => max(1, $quantity),
    ];

    if (!isset($query->select('cart', 'id', "WHERE product_id = $productId AND user_id = $userId")[0]['id'])) {
        $query->insert('cart', $cartData);
    }

    echo json_encode(['ok' => true]);
    exit;
}

echo json_encode(['ok' => false]);
