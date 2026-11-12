<?php

function marketplaceRunMigrations($mysqli)
{
    if (!$mysqli) {
        return;
    }

    $queries = [
        "CREATE TABLE IF NOT EXISTS orders (
            id INT AUTO_INCREMENT PRIMARY KEY,
            orderNumber VARCHAR(20) NOT NULL,
            user_id INT NOT NULL,
            delivery_address TEXT,
            delivery_city VARCHAR(100) DEFAULT NULL,
            delivery_province VARCHAR(100) DEFAULT NULL,
            delivery_country VARCHAR(100) DEFAULT 'Rwanda',
            totalAmount DECIMAL(12,2) NOT NULL DEFAULT 0,
            txnType VARCHAR(50) DEFAULT NULL,
            txnNumber VARCHAR(100) DEFAULT NULL,
            depositId VARCHAR(64) DEFAULT NULL,
            paymentStatus VARCHAR(20) DEFAULT 'pending',
            orderStatus VARCHAR(50) DEFAULT 'Pending Payment',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY uniq_order_number (orderNumber),
            KEY idx_orders_user (user_id),
            CONSTRAINT fk_orders_user FOREIGN KEY (user_id) REFERENCES accounts(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        "CREATE TABLE IF NOT EXISTS order_items (
            id INT AUTO_INCREMENT PRIMARY KEY,
            orderNumber VARCHAR(20) NOT NULL,
            user_id INT NOT NULL,
            product_id INT NOT NULL,
            seller_id INT NOT NULL,
            quantity INT NOT NULL,
            unit_price INT NOT NULL,
            line_total INT NOT NULL,
            payout_status VARCHAR(20) DEFAULT 'pending',
            KEY idx_order_items_order (orderNumber),
            CONSTRAINT fk_order_items_user FOREIGN KEY (user_id) REFERENCES accounts(id) ON DELETE CASCADE,
            CONSTRAINT fk_order_items_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
            CONSTRAINT fk_order_items_seller FOREIGN KEY (seller_id) REFERENCES accounts(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
    ];

    foreach ($queries as $sql) {
        $mysqli->query($sql);
    }

    marketplaceAddAccountColumns($mysqli);
    marketplaceEnsureAdminAccount($mysqli);
    marketplacePriceCatalogueInRwf($mysqli);
}

/**
 * Price below which a `products` row cannot be a franc amount.
 *
 * Kigali retail for the cheapest item in this catalogue — a 32-inch television
 * — is about RWF 165,000. Anything under ten thousand has never been converted
 * from the dollar figures the demonstration dump was imported with.
 */
const CATALOGUE_RWF_FLOOR = 10000;

/**
 * Rewrites the seeded catalogue from US dollars into Rwandan francs, once.
 *
 * The dump was imported from a shop that quoted dollars, while every template
 * printed an "RWF" prefix in front of whatever it found: a Tecno Spark 20
 * advertised at "RWF 249", roughly seventeen US cents. Converting in the
 * database rather than at render time means the storefront, the cart totals,
 * the order rows and the amount sent to PawaPay all agree on one figure.
 *
 * Idempotent by construction. The guard row only matches while prices are still
 * dollar-sized, and no converted price can fall back under the floor, so the
 * second and every later request does nothing at all.
 */
function marketplacePriceCatalogueInRwf($mysqli)
{
    $guard = $mysqli->query('SELECT COUNT(*) AS legacy FROM products WHERE price_current < ' . CATALOGUE_RWF_FLOOR);

    if (!$guard) {
        return;
    }

    $row = $guard->fetch_assoc();
    $guard->free();

    if (!$row || (int) $row['legacy'] === 0) {
        return;
    }

    $catalogue = require __DIR__ . '/catalog-prices.php';

    // A row is matched on its imported name. Corrected names are indexed too so
    // a half-applied run — one that renamed rows before it priced them — still
    // resolves instead of converting an already-correct name a second time.
    // Both sides are trimmed: `products.name` is VARCHAR(30) and the import
    // truncated twelve names mid-word, leaving some with a trailing space that
    // an exact match would miss.
    $byName = [];
    foreach ($catalogue as $importedName => $entry) {
        $byName[trim($importedName)] = $entry;
        $byName[trim($entry['name'])] = $entry;
    }

    $products = $mysqli->query('SELECT id, name, price_current, price_old FROM products');

    if (!$products) {
        return;
    }

    $update = $mysqli->prepare('UPDATE products SET name = ?, price_current = ?, price_old = ? WHERE id = ?');

    if (!$update) {
        $products->free();
        return;
    }

    while ($product = $products->fetch_assoc()) {
        $entry = $byName[trim($product['name'])] ?? null;

        if ($entry !== null) {
            $name = $entry['name'];
            $current = rwfRound($entry['price_current']);
            $old = rwfRound($entry['price_old']);
        } else {
            // A seller's own listing, or a product added after this list was
            // written. Convert what is there rather than leave it dollar-sized.
            $name = $product['name'];
            $current = convertUsdToRwf($product['price_current']);
            $old = convertUsdToRwf($product['price_old']);
        }

        if ($old < $current) {
            $old = $current;
        }

        $id = (int) $product['id'];
        $update->bind_param('siii', $name, $current, $old, $id);
        $update->execute();
    }

    $update->close();
    $products->free();
}

/**
 * File that receives a generated administrator password, relative to the
 * project root. It is git-ignored and should be deleted once the password has
 * been changed from the admin area.
 */
const ADMIN_PASSWORD_FILE = 'admin-initial-password.txt';

/**
 * Password given to the generated `admin` login.
 *
 * Taken from ADMIN_INITIAL_PASSWORD in .env when that is set, so a deployment
 * can choose it. Otherwise a random one is generated and written to
 * admin-initial-password.txt, because a hard-coded default meant every install
 * of this application shared the same administrator password.
 *
 * Returns a two-element array: the password and whether it was generated.
 */
function marketplaceGenerateAdminPassword()
{
    $configured = getenv('ADMIN_INITIAL_PASSWORD');

    if (is_string($configured) && trim($configured) !== '') {
        return [trim($configured), false];
    }

    return [bin2hex(random_bytes(9)), true];
}

/**
 * Writes a generated administrator password beside the application so the
 * operator can read it, with owner-only permissions where the platform allows.
 */
function marketplaceStoreAdminPassword($password)
{
    $path = dirname(__DIR__) . DIRECTORY_SEPARATOR . ADMIN_PASSWORD_FILE;
    $body = "Administrator login\n"
        . "username: admin\n"
        . "password: " . $password . "\n\n"
        . "Sign in at /admin, change this password, then delete this file.\n";

    if (@file_put_contents($path, $body) !== false) {
        @chmod($path, 0600);
    }
}

/**
 * Guarantees an administrator can sign in.
 *
 * The seeded admin row used a password hash that no known password matched, so
 * nobody could reach /admin. This creates a dedicated `admin` login once; it is
 * skipped as soon as any administrator account exists.
 */
function marketplaceEnsureAdminAccount($mysqli)
{
    // Keyed on the login itself, not on the role: legacy installs already have
    // an administrator row whose password nobody can reproduce.
    $result = $mysqli->query("SELECT id FROM accounts WHERE username = 'admin' LIMIT 1");

    if ($result) {
        $hasAdminLogin = $result->num_rows > 0;
        $result->free();

        if ($hasAdminLogin) {
            return;
        }
    }

    $name = 'Administrator';
    // COMPANY_PHONE_SUPPORT comes from includes/company.php, which config.php
    // loads before this file. Fall back to an empty value rather than risk a
    // fatal error if this file is ever included on its own.
    $number = defined('COMPANY_PHONE_SUPPORT') ? COMPANY_PHONE_SUPPORT : '';
    $email = 'admin@cypadi.com';
    $username = 'admin';

    [$password, $generated] = marketplaceGenerateAdminPassword();
    $passwordHash = marketplaceHashPassword($password);

    $stmt = $mysqli->prepare(
        "INSERT INTO accounts (name, number, email, username, password, role, status, seller_status)
         VALUES (?, ?, ?, ?, ?, 'admin', 'active', 'approved')"
    );

    if (!$stmt) {
        return;
    }

    $stmt->bind_param('sssss', $name, $number, $email, $username, $passwordHash);
    $stmt->execute();
    $stmt->close();

    if ($generated) {
        marketplaceStoreAdminPassword($password);
    }
}

/**
 * Adds the seller KYC / approval columns to `accounts` once.
 * Existing rows default to 'approved' so live sellers are not locked out.
 */
function marketplaceAddAccountColumns($mysqli)
{
    $columns = [
        "seller_status" => "ENUM('pending','approved','rejected') NOT NULL DEFAULT 'approved'",
        "business_name" => "VARCHAR(150) DEFAULT NULL",
        "country" => "VARCHAR(100) DEFAULT NULL",
        "id_number" => "VARCHAR(100) DEFAULT NULL",
        "kyc_note" => "VARCHAR(255) DEFAULT NULL",
        "id_document" => "VARCHAR(255) DEFAULT NULL",
    ];

    $existing = [];
    $result = $mysqli->query("SHOW COLUMNS FROM accounts");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $existing[$row['Field']] = true;
        }
        $result->free();
    }

    foreach ($columns as $name => $definition) {
        if (isset($existing[$name])) {
            continue;
        }
        $mysqli->query("ALTER TABLE accounts ADD COLUMN `$name` $definition");
    }
}
