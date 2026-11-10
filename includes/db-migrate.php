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
}

/**
 * Password given to the generated `admin` login.
 * Sign in once and change it from the admin area.
 */
function marketplaceDefaultAdminPassword()
{
    return '123';
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
    $number = '0000000000';
    $email = 'admin@cypadi.com';
    $username = 'admin';
    $passwordHash = marketplaceHashPassword(marketplaceDefaultAdminPassword());

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
