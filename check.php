<?php
require_once __DIR__ . '/init.php';

if (empty($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: ./login/');
    exit;
}

if (empty($_SESSION['id'])) {
    header('Location: ./login/');
    exit;
}

$userStatus = $query->select('accounts', 'status', 'WHERE id = "' . $_SESSION['id'] . '"');
if (!isset($userStatus[0]['status']) || empty($userStatus[0]['status']) || $userStatus[0]['status'] === 'blocked') {
    header('Location: ./blocked_page.php');
    exit;
}

if (empty($_SESSION['role']) || $_SESSION['role'] !== 'user') {
    $roles = [
        'admin' => './admin/',
        'seller' => './seller/',
    ];

    if (isset($roles[$_SESSION['role']])) {
        header('Location: ' . $roles[$_SESSION['role']]);
    } else {
        header('Location: ./login/');
    }
    exit;
}
