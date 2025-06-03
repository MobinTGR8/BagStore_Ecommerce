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
    <?php if (isset($pageSpecificCss)): ?>
        <link rel="stylesheet" href="<?= htmlspecialchars($pageSpecificCss) ?>">
    <?php endif; ?>

</head>
<body class="<?php echo isset($body_class) ? htmlspecialchars($body_class) : ''; ?>">

<nav class="navbar">
    <div class="logo">
        <a href="/BagStore_Ecommerce/index.php">BagStore</a>
    </div>

    <button class="nav-toggle" aria-label="toggle navigation">
        <span class="hamburger"></span>
    </button>

    <div class="nav-menu"> <!-- Wrapper for links and theme toggle -->
        <ul class="nav-links">
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
        <!-- Search Form - Placed inside nav-menu for mobile, CSS will handle desktop -->
        <form action="/BagStore_Ecommerce/search.php" method="GET" class="nav-search-form">
            <input type="search" name="query" placeholder="Search products..." aria-label="Search products" class="nav-search-input">
            <button type="submit" class="nav-search-button">Search</button>
        </form>
        <button id="themeToggle" class="theme-toggle">🌙</button>
    </div>
</nav>

<?php if (isset($_SESSION['admin_id'])): ?>
<nav class="admin-subnav">
    <div class="container">
        <ul class="admin-nav-links">
            <li><a href="/BagStore_Ecommerce/admin_dashboard.php" class="<?= $currentPage === 'admin_dashboard.php' ? 'active' : '' ?>">Dashboard (Orders)</a></li>
            <li><a href="/BagStore_Ecommerce/admin_products.php" class="<?= $currentPage === 'admin_products.php' ? 'active' : '' ?>">Manage Products</a></li>
            <!-- Add more admin links here: e.g., Users, Settings -->
            <li><a href="/BagStore_Ecommerce/logout.php?admin=true">Admin Logout</a></li>
        </ul>
    </div>
</nav>
<?php endif; ?>

<!-- Ensure script.js is loaded, or add inline script for nav toggle -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const navToggle = document.querySelector('.nav-toggle');
    const navMenu = document.querySelector('.nav-menu');

    if (navToggle && navMenu) {
        navToggle.addEventListener('click', function () {
            navMenu.classList.toggle('active');
            navToggle.classList.toggle('active'); // For styling the toggle button itself, e.g., transform to X
        });
    }

    // Theme toggle functionality (assuming it's already in script.js or here)
    const themeToggleButton = document.getElementById('themeToggle');
    if (themeToggleButton) {
        themeToggleButton.addEventListener('click', () => {
            document.body.classList.toggle('dark-theme');
            document.body.classList.toggle('light-theme'); // Assuming light-theme is default or explicitly set
            // Update icon based on theme
            if (document.body.classList.contains('dark-theme')) {
                themeToggleButton.textContent = '☀️'; // Sun icon for dark theme
            } else {
                themeToggleButton.textContent = '🌙'; // Moon icon for light theme
            }
            // Optionally, save theme preference to localStorage
            if (document.body.classList.contains('dark-theme')) {
                localStorage.setItem('theme', 'dark-theme');
            } else {
                localStorage.setItem('theme', 'light-theme');
            }
        });
    }

    // Apply saved theme on load
    const savedTheme = localStorage.getItem('theme');
    if (savedTheme) {
        document.body.classList.remove('light-theme', 'dark-theme'); // Remove default/current
        document.body.classList.add(savedTheme);
        if (themeToggleButton) {
            if (savedTheme === 'dark-theme') {
                themeToggleButton.textContent = '☀️';
            } else {
                themeToggleButton.textContent = '🌙';
            }
        }
    } else {
        // Default to light theme if no preference saved and body doesn't have a theme class yet
        if (!document.body.classList.contains('dark-theme') && !document.body.classList.contains('light-theme')) {
            document.body.classList.add('light-theme');
             if (themeToggleButton) {
                themeToggleButton.textContent = '🌙';
            }
        }
    }
});
</script>
<!-- BODY tag was closed in the original file, it should remain open here as footer closes it -->