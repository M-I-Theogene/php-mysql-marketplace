<?php
/**
 * Product read helpers shared by the storefront pages.
 *
 * The catalogue is driven from several pages (home, category, search), and each
 * of them needs the same three things: a safe way to read a product's first
 * image, a category name, and a total product count. They lived inline in
 * index.php as unguarded array offsets, which raises "Undefined array key 0" on
 * any product that has no image row and prints nothing useful to the customer.
 */

/**
 * Relative file name of a product's first image, or null when it has none.
 *
 * Callers must handle null: a product can exist before its photo is uploaded,
 * and indexing [0] on an empty result set is a fatal error on PHP 8.
 */
function productPrimaryImage(Database $db, int $productId): ?string
{
    $rows = $db->select('product_images', 'image_url', 'WHERE product_id = ' . $productId . ' ORDER BY id ASC LIMIT 1');

    if (empty($rows) || empty($rows[0]['image_url'])) {
        return null;
    }

    return (string) $rows[0]['image_url'];
}

/**
 * The `data-setbg` attribute for a product tile, or an empty string.
 *
 * The theme applies the background through that attribute, so a product with no
 * photograph simply keeps the tile's own background instead of requesting a
 * URL that 404s.
 */
function productTileBackground(?string $image): string
{
    if ($image === null || $image === '') {
        return '';
    }

    return 'data-setbg="' . htmlspecialchars('./src/images/products/' . $image, ENT_QUOTES) . '"';
}

/** Display name of a category, or an empty string when the id is unknown. */
function productCategoryName(Database $db, $categoryId): string
{
    $categoryId = (int) $categoryId;

    if ($categoryId <= 0) {
        return '';
    }

    $rows = $db->select('categories', 'category_name', 'WHERE id = ' . $categoryId . ' LIMIT 1');

    return empty($rows) ? '' : (string) $rows[0]['category_name'];
}

/** Number of products in the catalogue, optionally restricted to one category. */
function productCount(Database $db, ?int $categoryId = null): int
{
    $where = $categoryId === null ? '' : ' WHERE category_id = ' . $categoryId;
    $result = $db->executeQuery('SELECT COUNT(*) AS total FROM products' . $where);
    $row = $result->fetch_assoc();

    return $row ? (int) $row['total'] : 0;
}

/**
 * Escapes a product name for a search-result snippet.
 *
 * Product names are stored as entered by the seller, so they are always escaped
 * on output rather than trusted as markup.
 */
function productDisplayName($name): string
{
    return htmlspecialchars((string) $name, ENT_QUOTES, 'UTF-8');
}
