<?php
/**
 * Deployment doctor for shop.cypadi.com.
 *
 * Diagnoses an HTTP 500 without needing the error log: it checks the PHP
 * runtime, the extensions the application needs, the .env file, the database
 * credentials and the schema, and it prints anything PHP has logged.
 *
 * Usage:  https://shop.cypadi.com/doctor.php?key=cypadi
 * Delete this file from the server once the site is working.
 */

const DOCTOR_KEY = 'cypadi';
const DOCTOR_DB_HOST = 'localhost';
const DOCTOR_DB_USER = 'cjwfktusal_cypadi_shop';
const DOCTOR_DB_PASS = 'cypadi_shop';
const DOCTOR_DB_NAME = 'cjwfktusal_cypadi_shop';

if (!isset($_GET['key']) || !hash_equals(DOCTOR_KEY, (string) $_GET['key'])) {
    http_response_code(404);
    exit('Not found');
}

header('Content-Type: text/plain; charset=utf-8');

$fail = 0;

function dLine($label, $value, $ok = null)
{
    global $fail;
    if ($ok === false) {
        $fail++;
    }
    $mark = $ok === true ? '[ OK ]' : ($ok === false ? '[FAIL]' : '[----]');
    echo $mark . ' ' . str_pad($label, 34) . $value . "\n";
}

echo "CYPADI SHOP - DEPLOYMENT DOCTOR\n";
echo str_repeat('=', 70) . "\n\n";

/* ---------------------------------------------------------------- runtime */

echo "PHP RUNTIME\n";
dLine('PHP version', PHP_VERSION, version_compare(PHP_VERSION, '7.4', '>='));
dLine('Server API', PHP_SAPI);
dLine('Loaded php.ini', php_ini_loaded_file() ?: '(none)');
dLine('display_errors', ini_get('display_errors') ?: 'off');
dLine('error_reporting', (string) ini_get('error_reporting'));
dLine('memory_limit', (string) ini_get('memory_limit'));
dLine('upload_max_filesize', (string) ini_get('upload_max_filesize'));
dLine('post_max_size', (string) ini_get('post_max_size'));
dLine('open_basedir', ini_get('open_basedir') ?: '(not set)');
echo "\n";

echo "REQUIRED EXTENSIONS\n";
foreach (['mysqli', 'curl', 'openssl', 'mbstring', 'json', 'session', 'fileinfo'] as $ext) {
    dLine($ext, extension_loaded($ext) ? 'loaded' : 'MISSING', extension_loaded($ext));
}
echo "\n";

/* ------------------------------------------------------------------- files */

echo "PROJECT FILES\n";
$root = __DIR__;
foreach ([
    'config.php',
    'init.php',
    'index.php',
    'includes/env.php',
    'includes/company.php',
    'includes/currency.php',
    'includes/products.php',
    'includes/db-migrate.php',
    'includes/catalog-prices.php',
    'includes/header.php',
    'includes/footer.php',
    'includes/favicon.php',
    'includes/pawapay.php',
    '.htaccess',
] as $file) {
    $path = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $file);
    dLine($file, file_exists($path) ? 'present' : 'MISSING', file_exists($path));
}

$envPath = $root . DIRECTORY_SEPARATOR . '.env';
dLine('.env', file_exists($envPath) ? 'present (optional)' : 'absent (optional)');

dLine('root writable', is_writable($root) ? 'yes' : 'no');
dLine('src/images/products writable', is_writable($root . '/src/images/products') ? 'yes' : 'no');
dLine('src/images/kyc writable', is_writable($root . '/src/images/kyc') ? 'yes' : 'no');
echo "\n";

/* ---------------------------------------------------------------- database */

echo "DATABASE\n";

// Match the application: a failure must be a value we print, not an exception.
mysqli_report(MYSQLI_REPORT_OFF);

$conn = @new mysqli(DOCTOR_DB_HOST, DOCTOR_DB_USER, DOCTOR_DB_PASS, DOCTOR_DB_NAME);

if ($conn->connect_errno) {
    dLine('connect', 'FAILED - errno ' . $conn->connect_errno . ': ' . $conn->connect_error, false);
    echo "\n  The application cannot reach MySQL with these credentials.\n";
    echo "  Check the user name, password and database name in cPanel > MySQL Databases,\n";
    echo "  and that this user is attached to this database with ALL PRIVILEGES.\n\n";
} else {
    dLine('connect', 'connected to ' . DOCTOR_DB_NAME, true);
    dLine('server version', $conn->server_info);

    $grants = $conn->query('SHOW GRANTS');
    if ($grants) {
        while ($row = $grants->fetch_row()) {
            echo "       grant: " . $row[0] . "\n";
        }
        $grants->free();
    }

    $required = ['accounts', 'categories', 'products', 'product_images', 'cart', 'wishes', 'orders', 'order_items'];
    $tables = [];
    $res = $conn->query('SHOW TABLES');
    if ($res) {
        while ($row = $res->fetch_row()) {
            $tables[strtolower($row[0])] = true;
        }
        $res->free();
    }
    dLine('table count', (string) count($tables), count($tables) > 0);

    foreach ($required as $table) {
        dLine('table ' . $table, isset($tables[$table]) ? 'exists' : 'MISSING', isset($tables[$table]));
    }

    if (isset($tables['products'])) {
        $counts = @$conn->query('SELECT COUNT(*) AS c FROM products');
        if ($counts) {
            dLine('products rows', (string) $counts->fetch_assoc()['c'], true);
            $counts->free();
        }
    }

    if (isset($tables['accounts'])) {
        $cols = [];
        $res = $conn->query('SHOW COLUMNS FROM accounts');
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $cols[$row['Field']] = true;
            }
            $res->free();
        }
        foreach (['seller_status', 'business_name', 'country', 'id_number', 'id_document'] as $col) {
            dLine('accounts.' . $col, isset($cols[$col]) ? 'exists' : 'MISSING', isset($cols[$col]));
        }
    }

    $conn->close();
}
echo "\n";

/* ------------------------------------------------------------------- logs */

echo "PHP ERROR LOG TAIL\n";
$candidates = array_filter([
    ini_get('error_log'),
    $root . '/error_log',
    $root . '/php_errorlog',
    dirname($root) . '/logs/error_log',
    '/home/cjwfktusal/logs/error_log',
]);

$found = false;
foreach ($candidates as $logFile) {
    if (!is_string($logFile) || $logFile === '' || !is_readable($logFile) || !is_file($logFile)) {
        continue;
    }
    $found = true;
    echo "---- " . $logFile . " ----\n";
    $lines = @file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines === false) {
        echo "(unreadable)\n";
        continue;
    }
    foreach (array_slice($lines, -40) as $line) {
        echo $line . "\n";
    }
    echo "\n";
}

if (!$found) {
    echo "(no readable error log found; see cPanel > Errors, or the log file named above)\n\n";
}

/* ---------------------------------------------------------------- verdict */

echo str_repeat('=', 70) . "\n";
echo $fail === 0
    ? "RESULT: no blocking problem found by this script.\n"
    : "RESULT: " . $fail . " problem(s) marked [FAIL] above.\n";
echo "Delete doctor.php from the server once the site works.\n";
