<?php
session_start(); // Ensure session is started
include $_SERVER['DOCUMENT_ROOT'] . '/BagStore_Ecommerce/includes/db.php'; // Corrected path

$pageTitle = "Our Products";
$pageSpecificCss = "/BagStore_Ecommerce/css/products.css?v=" . time();
?>
<?php include $_SERVER['DOCUMENT_ROOT'] . '/BagStore_Ecommerce/includes/header.php'; ?>

<main class="products-main-container"> <!-- New main container -->
    <div class="products-page-container"> <!-- Specific container -->
        <h1 class="page-title">Available Bags</h1>
        <div class="product-list">
            <?php
            $sql = "SELECT id, name, price, image_path FROM products"; // Assuming image_path is the correct column
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo '<div class="product-card">'; // Changed class to product-card for consistency
                    // Corrected image path and added alt text
                    echo '<a href="/BagStore_Ecommerce/product_detail.php?id=' . htmlspecialchars($row['id']) . '">';
                    echo '<img src="/BagStore_Ecommerce/images/' . htmlspecialchars($row['image_path']) . '" alt="' . htmlspecialchars($row['name']) . '">';
                    echo '</a>';
                    echo '<h3><a href="/BagStore_Ecommerce/product_detail.php?id=' . htmlspecialchars($row['id']) . '">' . htmlspecialchars($row['name']) . '</a></h3>';
                    echo '<p class="price">$' . htmlspecialchars(number_format($row['price'], 2)) . '</p>';
                    // "Add to cart" button can be added here if desired, linking to cart.php?add=ID
                    echo '<a href="/BagStore_Ecommerce/cart.php?add=' . htmlspecialchars($row['id']) . '" class="btn btn-add-to-cart">Add to Cart</a>';
                    echo '</div>';
                }
            } else {
                echo "<p class='empty-msg'>No products found.</p>";
            }
            ?>
        </div>
    </div>
</main>

<?php include $_SERVER['DOCUMENT_ROOT'] . '/BagStore_Ecommerce/includes/footer.php'; ?>
