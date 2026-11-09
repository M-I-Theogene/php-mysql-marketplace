<?php
session_start();

if (!isset($_SESSION['loggedin'])) {
    $_SESSION['loggedin'] = false;
}

include __DIR__ . '/config.php';
// The Database constructor runs the schema migrations once per request.
$query = new Database();
$con = $query->getMysqli();
require_once __DIR__ . '/includes/pawapay.php';
