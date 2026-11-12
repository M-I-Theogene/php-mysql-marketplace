<?php
/**
 * Catalogue search.
 *
 * The search box in the shared header posts here. Before this page existed the
 * box was markup only: the form action was "#", the text input had no name, and
 * pressing Search reloaded the page with nothing filtered, which made the store
 * look broken to anyone who tried it.
 */
include 'init.php';

/** Longest query string accepted; longer input is truncated, not rejected. */
const SEARCH_MAX_TERM_LENGTH = 80;

$searchTerm = isset($_GET['q']) ? trim((string) $_GET['q']) : '';
$searchTerm = mb_substr($searchTerm, 0, SEARCH_MAX_TERM_LENGTH);

$categoryId = isset($_GET['category']) ? (int) $_GET['category'] : 0;
if ($categoryId <= 0) {
    $categoryId = 0;
}

$conditions = array();

if ($searchTerm !== '') {
    // LIKE wildcards typed by the user are literal characters here, so a query
    // of "%" matches items containing a percent sign rather than the whole
    // catalogue.
    $escapedTerm = $query->escape($searchTerm);
    $conditions[] = "(name LIKE '%" . $escapedTerm . "%' OR description LIKE '%" . $escapedTerm . "%')";
}

if ($categoryId > 0) {
    $conditions[] = 'category_id = ' . $categoryId;
}

$where = empty($conditions) ? 'ORDER BY name ASC LIMIT 60' : 'WHERE ' . implode(' AND ', $conditions) . ' ORDER BY name ASC LIMIT 60';
$results = $query->select('products', '*', $where);

$categories = $query->select('categories', 'id, category_name', 'ORDER BY category_name ASC');

$base = appBaseUrl() . '/';
$resultCount = count($results);

if ($searchTerm === '') {
    $heading = $categoryId > 0 ? 'Products in this category' : 'All products';
} else {
    $heading = 'Search results for “' . htmlspecialchars($searchTerm, ENT_QUOTES, 'UTF-8') . '”';
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, follow">
    <title><?= htmlspecialchars(strip_tags($heading), ENT_QUOTES, 'UTF-8'); ?> | Cypadi Shop</title>
    <?php include __DIR__ . '/includes/favicon.php'; ?>
    <link rel="stylesheet" href="<?= $base; ?>src/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?= $base; ?>src/css/font-awesome.min.css">
    <link rel="stylesheet" href="<?= $base; ?>src/css/style.css">
</head>

<body>
    <?php include './includes/header.php'; ?>

    <section class="product spad">
        <div class="container">
            <div class="row mb-4">
                <div class="col-lg-8">
                    <h3><?= $heading; ?></h3>
                    <p class="text-muted">
                        <?= $resultCount; ?> <?= $resultCount === 1 ? 'product' : 'products'; ?> found
                        <?php if ($searchTerm !== ''): ?>
                            for “<?= htmlspecialchars($searchTerm, ENT_QUOTES, 'UTF-8'); ?>”
                        <?php endif; ?>
                    </p>
                </div>
                <div class="col-lg-4">
                    <form method="get" action="<?= $base; ?>search.php" class="form-inline justify-content-end">
                        <select name="category" class="form-control mr-2" onchange="this.form.submit()">
                            <option value="0">All categories</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= (int) $category['id']; ?>" <?= $categoryId === (int) $category['id'] ? 'selected' : ''; ?>>
                                    <?= htmlspecialchars($category['category_name'], ENT_QUOTES, 'UTF-8'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if ($searchTerm !== ''): ?>
                            <input type="hidden" name="q" value="<?= htmlspecialchars($searchTerm, ENT_QUOTES, 'UTF-8'); ?>">
                        <?php endif; ?>
                        <noscript><button type="submit" class="btn btn-dark">Apply</button></noscript>
                    </form>
                </div>
            </div>

            <?php if ($resultCount === 0): ?>
                <div class="row">
                    <div class="col-lg-12">
                        <p>No products matched. Try a shorter search term, or
                            <a href="<?= $base; ?>search.php">browse everything</a>.</p>
                    </div>
                </div>
            <?php else: ?>
                <div class="row">
                    <?php foreach ($results as $product): ?>
                        <?php
                        $productId = (int) $product['id'];
                        $image = productPrimaryImage($query, $productId);
                        ?>
                        <div class="col-lg-4 col-md-6">
                            <div class="product__discount__item">
                                <a href="<?= $base; ?>shop-details.php?product_id=<?= $productId; ?>">
                                    <div class="product__discount__item__pic set-bg" <?= productTileBackground($image) ?>></div>
                                </a>
                                <div class="product__discount__item__text">
                                    <span><?= productDisplayName(productCategoryName($query, $product['category_id'])); ?></span>
                                    <h5>
                                        <a href="<?= $base; ?>shop-details.php?product_id=<?= $productId; ?>">
                                            <?= productDisplayName($product['name']); ?>
                                        </a>
                                    </h5>
                                    <div class="product__item__price">
                                        <?= formatRwf($product['price_current']); ?>
                                        <?php if ((float) $product['price_old'] > 0): ?>
                                            <span><?= formatRwf($product['price_old']); ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <?php include './includes/footer.php'; ?>
    <script src="<?= $base; ?>src/js/jquery-3.3.1.min.js"></script>
    <script src="<?= $base; ?>src/js/main.js"></script>
</body>

</html>
