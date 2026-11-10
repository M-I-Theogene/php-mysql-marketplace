<?php
// Temporary verification helper: seeds a session without going through login.
session_start();
$role = $_GET['role'] ?? '';
$id = (int) ($_GET['id'] ?? 0);
if ($role === 'admin' || $role === 'seller' || $role === 'user') {
    $_SESSION['loggedin'] = true;
    $_SESSION['role'] = $role;
    $_SESSION['id'] = $id;
    $_SESSION['username'] = 'probe';
} else {
    $_SESSION = array();
}
echo session_id();
