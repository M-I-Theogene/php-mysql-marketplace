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
