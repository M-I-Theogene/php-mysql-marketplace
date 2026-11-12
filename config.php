<?php

/** Pepper used by the HMAC-SHA256 password scheme. */
const PASSWORD_PEPPER = 'iqbolshoh';

/**
 * Hashes a plain password for storage in `accounts.password`.
 *
 * Kept as a global function so the schema migrations, which only receive a
 * mysqli handle, can seed accounts with a hash the login can verify.
 */
function marketplaceHashPassword($password)
{
    return hash_hmac('sha256', (string) $password, PASSWORD_PEPPER);
}

class Database
{
    private $conn;

    /** Guards the schema check so it runs once per request, not once per object. */
    private static $migrated = false;

    public function __construct()
    {
        $servername = "localhost";
        $username = "root";
        $password = "";
        $dbname = "marketplace";

        $this->conn = new mysqli($servername, $username, $password, $dbname);

        if ($this->conn->connect_error) {
            die("Connection failed: " . $this->conn->connect_error);
        }

        // Every page reaches the database through this constructor, including
        // /signup, /seller and /admin, which include config.php directly rather
        // than init.php. Running the schema check here guarantees the seller KYC
        // columns exist before the first seller registers on a fresh install.
        if (!self::$migrated) {
            marketplaceRunMigrations($this->conn);
            self::$migrated = true;
        }
    }

    public function __destruct()
    {
        if ($this->conn) {
            $this->conn->close();
        }
    }

    function validate($value)
    {
        $value = trim($value);
        $value = stripslashes($value);
        $value = htmlspecialchars($value);
        $value = mysqli_real_escape_string($this->conn, $value);
        return $value;
    }

    /**
     * Escapes a value for SQL without HTML-encoding it.
     * Use this for stored data that is escaped again with htmlspecialchars()
     * when it is displayed, so an ampersand is not stored in encoded form.
     */
    public function escape($value)
    {
        return mysqli_real_escape_string($this->conn, trim((string) $value));
    }

    public function executeQuery($sql)
    {
        $result = $this->conn->query($sql);
        if ($result === false) {
            die("ERROR: " . $this->conn->error);
        }
        return $result;
    }

    public function select($table, $columns = "*", $condition = "")
    {
        $sql = "SELECT $columns FROM $table $condition";
        return $this->executeQuery($sql)->fetch_all(MYSQLI_ASSOC);
    }

    public function insert($table, $data)
    {
        $keys = implode(', ', array_keys($data));
        $values = "'" . implode("', '", array_values($data)) . "'";
        $sql = "INSERT INTO $table ($keys) VALUES ($values)";
        return $this->executeQuery($sql);
    }

    public function update($table, $data, $condition = "")
    {
        $set = '';
        foreach ($data as $key => $value) {
            $set .= "$key = '$value', ";
        }
        $set = rtrim($set, ', ');
        $sql = "UPDATE $table SET $set $condition";
        return $this->executeQuery($sql);
    }

    public function delete($table, $condition = "")
    {
        $sql = "DELETE FROM $table $condition";
        return $this->executeQuery($sql);
    }

    public function hashPassword($password)
    {
        return marketplaceHashPassword($password);
    }

    /**
     * Checks a plain password against a stored hash.
     *
     * The application stores HMAC-SHA256 hashes, but rows edited by hand
     * (or seeded scripts) sometimes hold a plain MD5 or a password_hash()
     * value. All three are accepted so a manually reset password still works.
     */
    public function verifyPassword($password, $storedHash)
    {
        $storedHash = trim((string) $storedHash);

        if ($storedHash === '') {
            return false;
        }

        if (hash_equals($this->hashPassword($password), $storedHash)) {
            return true;
        }

        if (preg_match('/^[a-f0-9]{32}$/i', $storedHash) && hash_equals(strtolower($storedHash), md5($password))) {
            return true;
        }

        if (strlen($storedHash) > 50 && password_verify($password, $storedHash)) {
            return true;
        }

        return false;
    }

    /**
     * Logs a user in by username OR email address.
     *
     * The row is fetched by identifier first and the password is then checked
     * in PHP, because the stored hash may not follow the current scheme.
     */
    public function authenticate($username, $password, $table)
    {
        $login = $this->escape((string) $username);
        $condition = "WHERE username = '" . $login . "' OR email = '" . $login . "'";
        $rows = $this->select($table, "*", $condition);

        if (empty($rows)) {
            return array();
        }

        foreach ($rows as $row) {
            if (!$this->verifyPassword($password, isset($row['password']) ? $row['password'] : '')) {
                continue;
            }

            $this->upgradePasswordHash($table, $row, $password);

            return array($row);
        }

        return array();
    }

    /**
     * Replaces a legacy MD5 / password_hash() value with the current scheme
     * after a successful login, so old rows migrate themselves.
     */
    private function upgradePasswordHash($table, array $row, $password)
    {
        $expectedHash = $this->hashPassword($password);

        if (hash_equals($expectedHash, (string) $row['password'])) {
            return;
        }

        $this->executeQuery(
            "UPDATE $table SET password = '" . $expectedHash . "' WHERE id = " . (int) $row['id']
        );
    }

    public function registerUser($name, $number, $email, $username, $password, $role, $profile = array())
    {
        $name = $this->validate($name);
        $number = $this->validate($number);
        $email = $this->validate($email);
        $username = $this->validate($username);

        $password_hash = $this->hashPassword($password);

        $data = array(
            'name' => $name,
            'number' => $number,
            'email' => $email,
            'username' => $username,
            'password' => $password_hash,
            'role' => $role
        );

        // Seller onboarding profile: business details, the uploaded identity
        // document, plus the review state.
        $allowedProfileKeys = array('business_name', 'country', 'id_number', 'id_document');
        foreach ($allowedProfileKeys as $key) {
            if (!isset($profile[$key])) {
                continue;
            }
            $value = $this->escape($profile[$key]);
            if ($value !== '') {
                $data[$key] = $value;
            }
        }

        // A new seller is not allowed to list products until an admin approves.
        $data['seller_status'] = ($role === 'seller') ? 'pending' : 'approved';

        $user_id = $this->insert('accounts', $data);

        if ($user_id) {
            return $user_id;
        }
        return false;
    }

    function saveImagesToDatabase($files, $path, $productId)
    {
        if (is_array($files['tmp_name'])) {
            $uploaded_files = array();
            foreach ($files['tmp_name'] as $index => $tmp_name) {
                $file_name = $files['name'][$index];
                $file_info = pathinfo($file_name);
                $file_extension = $file_info['extension'];
                $new_file_name = md5($tmp_name . date("Y-m-d_H-i-s") . rand(1, 9999999) . $productId) . "." . $file_extension;
                if (move_uploaded_file($tmp_name, $path . $new_file_name)) {
                    $uploaded_files[] = $new_file_name;
                    $this->insert('product_images', array('product_id' => $productId, 'image_url' => $new_file_name));
                }
            }
            return $uploaded_files;
        } else {
            $file_name = $files['name'];
            $file_tmp = $files['tmp_name'];

            $file_info = pathinfo($file_name);
            $file_format = $file_info['extension'];

            $new_file_name = md5($file_tmp . date("Y-m-d_H-i-s") . rand(1, 9999999) . $productId) . "." . $file_format;

            if (move_uploaded_file($file_tmp, $path . $new_file_name)) {
                $this->insert('product_images', array('product_id' => $productId, 'image_url' => $new_file_name));
                return $new_file_name;
            }
            return false;
        }
    }

    function saveImage($file, $path)
    {
        $file_name = $file['name'];
        $file_tmp = $file['tmp_name'];

        $file_info = pathinfo($file_name);
        $file_format = $file_info['extension'];

        $new_file_name = md5($file_tmp . date("Y-m-d_H-i-s")) . rand(1, 9999999) . "." . $file_format;

        if (move_uploaded_file($file_tmp, $path . $new_file_name)) {
            return $new_file_name;
        }
        return false;
    }

    public function getCategories()
    {
        $categories = array();
        $result = $this->select('categories', 'id, category_name');
        foreach ($result as $row) {
            $categories[$row['id']] = $row['category_name'];
        }
        return $categories;
    }

    public function getProduct($product_id)
    {
        $result = $this->select('products', '*', 'WHERE id = ' . $product_id);
        return $result[0];
    }

    public function getProductImageID($product_id)
    {
        $images = $this->select('product_images', 'id', 'WHERE product_id = ' . $product_id);
        $id = array();
        foreach ($images as $image) {
            $id[] = $image['id'];
        }
        return $id;
    }


    function getProductImage($id)
    {
        global $query;
        $result = $this->select('product_images', 'image_url', 'WHERE id = ' . $id);
        return $result[0]['image_url'];
    }

    public function getCartItems($user_id)
    {
        $sql = "SELECT 
        p.id,
        p.name,
        p.price_current,
        p.price_old,
        c.number_of_products,
        (p.price_current * c.number_of_products) AS total_price
    FROM 
        cart c
    JOIN
        products p ON c.product_id = p.id
    WHERE 
        c.user_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $cartItems = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $cartItems;
    }

    public function getProductImages($product_id)
    {
        $sql = "SELECT image_url FROM product_images WHERE product_id = $product_id";
        $result = $this->executeQuery($sql)->fetch_all(MYSQLI_ASSOC);
        $imageUrls = array();
        foreach ($result as $row) {
            $imageUrls[] = $row['image_url'];
        }
        return $imageUrls;
    }

    public function getWishes($user_id)
    {
        $sql = "SELECT 
        p.name,
        p.price_current,
        p.price_old,
        w.product_id
    FROM 
        wishes w
    JOIN
        products p ON w.product_id = p.id
    WHERE 
        w.user_id = ?";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();

        $result = $stmt->get_result();

        $wishes = $result->fetch_all(MYSQLI_ASSOC);

        $stmt->close();

        return $wishes;
    }

    public function lastInsertId($table, $data)
    {
        $keys = implode(', ', array_keys($data));
        $values = "'" . implode("', '", array_values($data)) . "'";
        $sql = "INSERT INTO $table ($keys) VALUES ($values)";
        $insert_result = $this->executeQuery($sql);

        if ($insert_result) {
            return $this->conn->insert_id;
        } else {
            return false;
        }
    }

    public function getMysqli()
    {
        return $this->conn;
    }
}

/**
 * Absolute URL path of the application root, e.g. "/shopping" or "".
 *
 * The shared header and footer are included from subdirectories (for example
 * seller/pending.php includes ../includes/header.php), where a page-relative
 * link such as "./logout/" would resolve to /seller/logout/ and 404.
 */
function appBaseUrl()
{
    static $base = null;

    if ($base !== null) {
        return $base;
    }

    $documentRoot = isset($_SERVER['DOCUMENT_ROOT']) ? realpath($_SERVER['DOCUMENT_ROOT']) : false;
    $appRoot = realpath(dirname(__FILE__));

    if ($documentRoot && $appRoot) {
        $documentRoot = str_replace('\\', '/', $documentRoot);
        $appRoot = str_replace('\\', '/', $appRoot);

        if (strpos($appRoot, $documentRoot) === 0) {
            $base = rtrim(substr($appRoot, strlen($documentRoot)), '/');
            return $base;
        }
    }

    $base = '';
    return $base;
}

require_once __DIR__ . '/includes/env.php';
require_once __DIR__ . '/includes/company.php';
require_once __DIR__ . '/includes/currency.php';
require_once __DIR__ . '/includes/products.php';
require_once __DIR__ . '/includes/db-migrate.php';
