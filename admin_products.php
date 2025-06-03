<?php
session_start();
include $_SERVER['DOCUMENT_ROOT'] . '/BagStore_Ecommerce/includes/db.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: /BagStore_Ecommerce/admin_login.php");
    exit();
}

// Handle potential messages from add/edit/delete operations
$message = $_SESSION['admin_message'] ?? '';
unset($_SESSION['admin_message']); // Clear the message after displaying

// Fetch all products
$products_result = $conn->query("SELECT id, name, price, stock_quantity, image_path FROM products ORDER BY id DESC");

$pageTitle = "Manage Products";
$pageSpecificCss = "/BagStore_Ecommerce/css/admin.css?v=" . time();
$body_class = "admin-page";
?>
<?php include $_SERVER['DOCUMENT_ROOT'] . '/BagStore_Ecommerce/includes/header.php'; ?>

<main class="admin-main-container">
    <div class="container admin-page-container"> <!-- General admin container -->
        <h1>Manage Products</h1>

        <?php if ($message): ?>
            <div class="admin-message <?= strpos(strtolower($message), 'error') !== false || strpos(strtolower($message), 'fail') !== false ? 'error-message' : 'success-message' ?>">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <div class="admin-actions-bar">
            <a href="/BagStore_Ecommerce/admin_add_product.php" class="btn btn-primary">Add New Product</a>
        </div>

        <?php if ($products_result && $products_result->num_rows > 0): ?>
        <div class="table-responsive-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($product = $products_result->fetch_assoc()): ?>
                    <tr>
                        <td data-label="ID"><?= htmlspecialchars($product['id']) ?></td>
                        <td data-label="Image">
                            <?php if (!empty($product['image_path'])): ?>
                                <img src="/BagStore_Ecommerce/images/<?= htmlspecialchars($product['image_path']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" style="width: 50px; height: 50px; object-fit: cover;">
                            <?php else: ?>
                                N/A
                            <?php endif; ?>
                        </td>
                        <td data-label="Name"><?= htmlspecialchars($product['name']) ?></td>
                        <td data-label="Price">$<?= htmlspecialchars(number_format($product['price'], 2)) ?></td>
                        <td data-label="Stock"><?= htmlspecialchars($product['stock_quantity']) ?></td>
                        <td data-label="Actions" class="actions-cell">
                            <a href="/BagStore_Ecommerce/admin_edit_product.php?id=<?= htmlspecialchars($product['id']) ?>" class="btn btn-secondary btn-sm">Edit</a>
                            <a href="/BagStore_Ecommerce/admin_delete_product.php?id=<?= htmlspecialchars($product['id']) ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this product?');">Delete</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
            <p class="empty-msg">No products found. <a href="/BagStore_Ecommerce/admin_add_product.php">Add the first product!</a></p>
        <?php endif; ?>
    </div>
</main>

<?php include $_SERVER['DOCUMENT_ROOT'] . '/BagStore_Ecommerce/includes/footer.php'; ?>
