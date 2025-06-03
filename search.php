<?php
session_start();
include $_SERVER['DOCUMENT_ROOT'] . '/BagStore_Ecommerce/includes/db.php';

$query = $_GET['query'] ?? '';
$search_performed = !empty($query);
$products = [];

if ($search_performed) {
    $like_query = "%" . $query . "%";
    $stmt = $conn->prepare("SELECT id, name, price, image_path, description FROM products WHERE name LIKE ? OR description LIKE ?");
    $stmt->bind_param("ss", $like_query, $like_query);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
    $stmt->close();
}

$pageTitle = $search_performed ? "Search Results for \"" . htmlspecialchars($query) . "\"" : "Search Products";
$pageSpecificCss = "/BagStore_Ecommerce/css/search.css?v=" . time();
?>
<?php include $_SERVER['DOCUMENT_ROOT'] . '/BagStore_Ecommerce/includes/header.php'; ?>

<main class="search-main-container">
    <div class="search-page-container">
        <h1><?= htmlspecialchars($pageTitle) ?></h1>

        <?php if (!$search_performed && empty($query)): // Page loaded without a query submission ?>
            <div class="search-prompt">
                <p>Please enter a term in the search bar to find products.</p>
            </div>
        <?php elseif ($search_performed && empty($products)): // Search was done, but no products found ?>
            <div class="search-no-results">
                <p>No products found matching "<?= htmlspecialchars($query) ?>".</p>
                <p>Try a different search term or <a href="/BagStore_Ecommerce/products.php">browse all products</a>.</p>
            </div>
        <?php elseif (!empty($products)): // Products found ?>
            <div class="product-list search-results-grid">
                <?php foreach ($products as $product): ?>
                    <div class="product-card">
                        <a href="/BagStore_Ecommerce/product_detail.php?id=<?= htmlspecialchars($product['id']) ?>">
                            <img src="/BagStore_Ecommerce/images/<?= htmlspecialchars($product['image_path']) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
                        </a>
                        <h3><a href="/BagStore_Ecommerce/product_detail.php?id=<?= htmlspecialchars($product['id']) ?>"><?= htmlspecialchars($product['name']) ?></a></h3>
                        <p class="price">$<?= htmlspecialchars(number_format($product['price'], 2)) ?></p>
                        <a href="/BagStore_Ecommerce/cart.php?add=<?= htmlspecialchars($product['id']) ?>" class="btn btn-add-to-cart">Add to Cart</a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php include $_SERVER['DOCUMENT_ROOT'] . '/BagStore_Ecommerce/includes/footer.php'; ?>
