<?php
session_start();
$pageTitle = "Bag Store - Home";
include $_SERVER['DOCUMENT_ROOT'] . '/BagStore_Ecommerce/includes/header.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/BagStore_Ecommerce/includes/db.php';

// Fetch 4 latest products to showcase
$sql = "SELECT * FROM products ORDER BY id DESC LIMIT 4";
$result = $conn->query($sql);
?>

<!-- Add background image style -->
<style>
    body {
        min-height: 100vh;
        margin: 0;
        background: 
            linear-gradient(120deg, rgba(0,191,255,0.2) 0%, rgba(224,231,255,0.2) 100%),
            url('/BagStore_Ecommerce/images/background.jpg') no-repeat center center fixed;
        background-size: cover;
    }
</style>


<section class="hero" style="text-align:center; padding: 80px 20px; background: rgba(0,191,255,0.0); color: white;">
    <h1>Welcome to BagStore</h1>
    <p>Your one-stop shop for stylish bags</p>
    <a href="products.php" class="btn" style="background:#fff; color:#000; padding:12px 24px; text-decoration:none; border-radius:5px; font-weight:bold;">Shop Now</a>
</section>

<?php include $_SERVER['DOCUMENT_ROOT'] . '/BagStore_Ecommerce/includes/footer.php'; ?>
