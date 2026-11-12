<?php
// Root-relative base so the links below also work when this header is included
// from a subdirectory (for example seller/pending.php includes it as ../includes/header.php).
$base = appBaseUrl() . '/';

$currentPage = basename($_SERVER['PHP_SELF']);
$cartItems = [];
$total_price = 0;
if (!empty($_SESSION['loggedin']) && !empty($_SESSION['id'])) {
    $cartItems = $query->getCartItems((int) $_SESSION['id']);
    $total_price = array_reduce($cartItems, function ($total, $item) {
        return $total + $item['total_price'];
    }, 0);
}

function countTable($table)
{
    global $query;
    if (empty($_SESSION['id'])) {
        return 0;
    }
    $userId = (int) $_SESSION['id'];
    $result = $query->executeQuery("SELECT COUNT(*) AS total_elements FROM $table WHERE user_id = $userId");
    $row = $result->fetch_assoc();
    return $row['total_elements'];
}
?>

<div class="humberger__menu__overlay"></div>
<div class="humberger__menu__wrapper">
    <div class="humberger__menu__logo">
        <a href="<?= $base; ?>"><img src="<?= $base; ?>assets/images/cypadi-shop.png" alt="Cypadi Shop" style="height: 80px; width: auto; max-width: 100%;"></a>
    </div>
    <div class="humberger__menu__cart">
        <ul>
            <li><a href="<?= $base; ?>heart.php"><i class="fa fa-heart"></i> <span><?= countTable('wishes'); ?></span></a>
            </li>
            <li><a href="<?= $base; ?>shoping-cart.php"><i class="fa fa-shopping-bag"></i>
                    <span><?= countTable('cart'); ?></span></a></li>
        </ul>
        <div class="header__cart__price">Total: <span>RWF <?= number_format($total_price, 0); ?></span></div>
    </div>
    <div class="humberger__menu__widget">
        <div class="header__top__right__auth">
            <?php if (!empty($_SESSION['loggedin'])): ?>
                <a href="#" onclick="logout()"><i class="fa fa-user"></i>Logout</a>
            <?php else: ?>
                <a href="<?= $base; ?>login/"><i class="fa fa-user"></i>Login</a>
            <?php endif; ?>
        </div>
    </div>
    <nav class="humberger__menu__nav mobile-menu">
        <ul>
            <li>
                <a href="<?= $base; ?>" class="<?= ($currentPage == 'index.php') ? 'active' : ''; ?>">Home</a>
            </li>

            <li>
                <a href="<?= $base; ?>search.php" class="<?= ($currentPage == 'search.php') ? 'active' : ''; ?>">Shop</a>
            </li>

            <li>
                <a href="<?= $base; ?>heart.php" class="<?= ($currentPage == 'heart.php') ? 'active' : ''; ?>">Wish
                    List</a>
            </li>

            <li>
                <a href="<?= $base; ?>shoping-cart.php"
                    class="<?= ($currentPage == 'shoping-cart.php') ? 'active' : ''; ?>">Cart</a>
            </li>
        </ul>
    </nav>
    <div id="mobile-menu-wrap"></div>
    <div class="humberger__menu__contact">
        <ul>
            <li><i class="fa fa-envelope"></i> <?= COMPANY_EMAIL; ?></li>
            <li><i class="fa fa-phone"></i> <?= COMPANY_PHONE_SUPPORT; ?></li>
            <li><i class="fa fa-map-marker"></i> <?= companyAddress(); ?></li>
        </ul>
    </div>
</div>

<header class="header">
    <div class="header__top">
        <div class="container">
            <div class="row">
                <div class="col-lg-6">
                    <div class="header__top__left">
                        <ul>
                            <li><i class="fa fa-envelope"></i> <?= COMPANY_EMAIL; ?></li>
                            <li><i class="fa fa-phone"></i> <?= COMPANY_PHONE_SUPPORT; ?></li>
                        </ul>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="header__top__right">
                        <div class="header__top__right__auth">
                            <?php if (!empty($_SESSION['loggedin'])): ?>
                                <a href="#" onclick="logout()"><i class="fa fa-user"></i>Logout</a>
                            <?php else: ?>
                                <a href="<?= $base; ?>login/"><i class="fa fa-user"></i>Login</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="container">
        <div class="row">
            <div class="col-lg-3">
                <div class="header__logo">
                    <a href="<?= $base; ?>"><img src="<?= $base; ?>assets/images/cypadi-shop.png" alt="Cypadi Shop" style="height: 90px; width: auto; max-width: 100%;"></a>
                </div>
            </div>
            <div class="col-lg-6">
                <nav class="header__menu">
                    <ul>
                        <li>
                            <a href="<?= $base; ?>" class="<?= ($currentPage == 'index.php') ? 'active' : ''; ?>">Home</a>
                        </li>

                        <li>
                            <a href="<?= $base; ?>search.php" class="<?= ($currentPage == 'search.php') ? 'active' : ''; ?>">Shop</a>
                        </li>

                        <li>
                            <a href="<?= $base; ?>heart.php" class="<?= ($currentPage == 'heart.php') ? 'active' : ''; ?>">Wish
                                List</a>
                        </li>

                        <li>
                            <a href="<?= $base; ?>shoping-cart.php"
                                class="<?= ($currentPage == 'shoping-cart.php') ? 'active' : ''; ?>">Cart</a>
                        </li>

                        <li>
                            <a href="<?= $base; ?>my-orders.php" class="<?= ($currentPage == 'my-orders.php') ? 'active' : ''; ?>">My Orders</a>
                        </li>
                    </ul>
                </nav>
            </div>

            <div class="col-lg-3">
                <div class="header__cart">
                    <ul>
                        <li><a href="<?= $base; ?>heart.php"><i class="fa fa-heart"></i>
                                <span><?= countTable('wishes'); ?></span></a></li>
                        <li><a href="<?= $base; ?>shoping-cart.php"><i class="fa fa-shopping-bag"></i>
                                <span><?= countTable('cart'); ?></span></a></li>
                    </ul>
                    <div class="header__cart__price">Total: <span>RWF <?= number_format($total_price, 0); ?></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="humberger__open">
            <i class="fa fa-bars"></i>
        </div>
    </div>
</header>

<style>
    .hero__categories__all:after {
        content: '';
        display: block;
    }

    ul li a.active {
        color: #f1b301 !important;
        font-weight: bold !important;
    }
</style>

<section class="hero hero-normal" style="margin-bottom: -50px;">
    <div class="container">
        <div class="row">
            <div class="col-lg-3">
                <div class="hero__categories">
                    <div class="hero__categories__all">
                        <i class="fa fa-bars"></i>
                        <span>Category</span>
                    </div>
                    <ul>
                        <?php
                        $categories = $query->select('categories', '*');
                        foreach ($categories as $category): ?>
                            <li>
                                <a
                                    href="<?= $base; ?>category.php?category=<?= $category['id']; ?>"><?= $category['category_name'] ?></a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
            <div class="col-lg-9">
                <div class="hero__search">
                    <div class="hero__search__form">
                        <form action="<?= $base; ?>search.php" method="get" role="search">
                            <select name="category" class="hero__search__categories" aria-label="Category" style="border: none; background: transparent; outline: none;">
                                <option value="0">All Categories</option>
                                <?php foreach ($query->select('categories', 'id, category_name', 'ORDER BY category_name ASC') as $searchCategory): ?>
                                    <option value="<?= (int) $searchCategory['id']; ?>"><?= htmlspecialchars($searchCategory['category_name'], ENT_QUOTES, 'UTF-8'); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <input type="text" name="q" placeholder="What do you need?" aria-label="Search products"
                                value="<?= htmlspecialchars(isset($_GET['q']) ? (string) $_GET['q'] : '', ENT_QUOTES, 'UTF-8'); ?>">
                            <button type="submit" class="site-btn">SEARCH</button>
                        </form>
                    </div>
                    <div class="hero__search__phone">
                        <div class="hero__search__phone__icon">
                            <i class="fa fa-phone"></i>
                        </div>
                        <div class="hero__search__phone__text">
                            <h5><?= COMPANY_PHONE_SUPPORT; ?></h5>
                            <span><?= COMPANY_LEGAL_NAME; ?> — Rwanda</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    function logout() {
        Swal.fire({
            title: 'Are you sure you want to log out?',
            text: "You cannot undo this action!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, log out!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = '<?= $base; ?>logout/';
            }
        });
    }
</script>
