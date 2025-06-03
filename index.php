<?php
session_start();
$pageTitle = "Bag Store - Home";
$body_class = "homepage-background"; // Add this line
include $_SERVER['DOCUMENT_ROOT'] . '/BagStore_Ecommerce/includes/header.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/BagStore_Ecommerce/includes/db.php';

// Fetch 4 latest products to showcase
$sql = "SELECT * FROM products ORDER BY id DESC LIMIT 4";
$result = $conn->query($sql);
?>

<section class="hero hero-home">
    <h1>Welcome to BagStore</h1>
    <p>Your one-stop shop for stylish bags</p>
    <a href="products.php" class="btn btn-hero">Shop Now</a>
</section>

<section class="featured-products">
    <div class="container">
        <h2>Latest Products</h2>
        <div class="product-grid">
            <?php if ($result->num_rows > 0): ?>
                <?php while($product = $result->fetch_assoc()): ?>
                    <div class="product-card">
                        <a href="product_detail.php?id=<?php echo $product['id']; ?>">
                            <img src="/BagStore_Ecommerce/images/<?php echo htmlspecialchars($product['image_path']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                        </a>
                        <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                        <p class="price">$<?php echo htmlspecialchars($product['price']); ?></p>
                        <a href="product_detail.php?id=<?php echo $product['id']; ?>" class="btn">View Details</a>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>No products found.</p>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php include $_SERVER['DOCUMENT_ROOT'] . '/BagStore_Ecommerce/includes/footer.php'; ?>
