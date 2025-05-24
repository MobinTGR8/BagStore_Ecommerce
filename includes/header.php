<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$currentPage = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) : 'BagStore' ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/BagStore_Ecommerce/css/index.css">
    <link rel="stylesheet" href="/BagStore_Ecommerce/css/auth.css">

</head>
<body>

<nav class="navbar" style="height:50px; min-height:50px; max-height:50px; width:100%; min-width:100%; max-width:100%;">
    <div class="logo" style="height:100%; display:flex; align-items:center;">
    <a href="/BagStore_Ecommerce/index.php" style="text-decoration: none; color: inherit;">BagStore</a>
</div>


    <ul class="nav-links" style="height:100%; display:flex; align-items:center;">
        <li><a href="/BagStore_Ecommerce/index.php" class="<?= $currentPage === 'index.php' ? 'active' : '' ?>">Home</a></li>
        <li><a href="/BagStore_Ecommerce/products.php" class="<?= $currentPage === 'products.php' ? 'active' : '' ?>">Products</a></li>
        <li><a href="/BagStore_Ecommerce/cart.php" class="<?= $currentPage === 'cart.php' ? 'active' : '' ?>">Cart</a></li>
        <li><a href="/BagStore_Ecommerce/checkout.php" class="<?= $currentPage === 'checkout.php' ? 'active' : '' ?>">Checkout</a></li>

        <?php if (isset($_SESSION['user_id'])): ?>
            <li><a href="/BagStore_Ecommerce/my_orders.php" class="<?= $currentPage === 'my_orders.php' ? 'active' : '' ?>">My Orders</a></li>
            <li><a href="/BagStore_Ecommerce/logout.php">Logout</a></li>
        <?php else: ?>
            <li><a href="/BagStore_Ecommerce/login.php" class="<?= $currentPage === 'login.php' ? 'active' : '' ?>">Login</a></li>
            <li><a href="/BagStore_Ecommerce/register.php" class="<?= $currentPage === 'register.php' ? 'active' : '' ?>">Register</a></li>
        <?php endif; ?>

        <li><a href="/BagStore_Ecommerce/contact.php" class="<?= $currentPage === 'contact.php' ? 'active' : '' ?>">Contact</a></li>

        <?php if (isset($_SESSION['admin_id'])): ?>
            <li><a href="/BagStore_Ecommerce/admin_dashboard.php" class="<?= $currentPage === 'admin_dashboard.php' ? 'active' : '' ?>">Admin</a></li>
        <?php else: ?>
            <li><a href="/BagStore_Ecommerce/admin_login.php" class="<?= $currentPage === 'admin_login.php' ? 'active' : '' ?>">Admin</a></li>
        <?php endif; ?>
    </ul>
    <button id="themeToggle" class="theme-toggle" style="width:40px; height:40px; min-width:40px; min-height:40px; max-width:40px; max-height:40px; font-size:20px; display:flex; align-items:center; justify-content:center;">🌙</button>
</nav>

<script src="/BagStore_Ecommerce/js/script.js"></script>

        </body>