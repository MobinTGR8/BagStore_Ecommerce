<?php
session_start();
include $_SERVER['DOCUMENT_ROOT'] . '/BagStore_Ecommerce/includes/db.php'; // Corrected path

// Initialize cart if not already
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Add item to cart
if (isset($_GET['add'])) {
    $id = intval($_GET['add']);
    $_SESSION['cart'][$id] = ($_SESSION['cart'][$id] ?? 0) + 1;
    header("Location: /BagStore_Ecommerce/cart.php"); // Absolute path
    exit();
}

// Remove item from cart
if (isset($_GET['remove'])) {
    $id = intval($_GET['remove']);
    unset($_SESSION['cart'][$id]);
    header("Location: /BagStore_Ecommerce/cart.php"); // Absolute path
    exit();
}

// Empty cart
if (isset($_GET['empty'])) {
    $_SESSION['cart'] = [];
    header("Location: /BagStore_Ecommerce/cart.php"); // Absolute path
    exit();
}

$pageTitle = "Your Shopping Cart";
$pageSpecificCss = "/BagStore_Ecommerce/css/cart.css?v=" . time();
?>
<?php include $_SERVER['DOCUMENT_ROOT'] . '/BagStore_Ecommerce/includes/header.php'; ?>

<main class="cart-main-container"> <!-- New main container -->
    <div class="cart-page-container"> <!-- Specific container for this page's content -->
        <h1>Your Shopping Cart</h1>

        <?php if (empty($_SESSION['cart'])): ?>
            <p class="empty-msg">Your cart is empty. <a href="/BagStore_Ecommerce/products.php">Browse Products</a></p>
        <?php else: ?>
            <div class="table-wrapper"> <!-- This already exists and is good for responsiveness -->
                <table>
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Quantity</th>
                            <th>Price</th>
                            <th>Total</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    $grand_total = 0;
                    foreach ($_SESSION['cart'] as $product_id => $qty):
                        // Use prepared statements to prevent SQL injection if $product_id could be non-integer
                        $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
                        $stmt->bind_param("i", $product_id);
                        $stmt->execute();
                        $result = $stmt->get_result();

                        if ($result->num_rows == 0) {
                            $stmt->close();
                            continue;
                        }

                        $product = $result->fetch_assoc();
                        $stmt->close();
                        $total = $product['price'] * $qty;
                        $grand_total += $total;
                    ?>
                        <tr>
                            <td data-label="Product"><?= htmlspecialchars($product['name']) ?></td>
                            <td data-label="Quantity"><?= htmlspecialchars($qty) ?></td>
                            <td data-label="Price">$<?= htmlspecialchars(number_format($product['price'], 2)) ?></td>
                            <td data-label="Total">$<?= htmlspecialchars(number_format($total, 2)) ?></td>
                            <td data-label="Action"><a href="/BagStore_Ecommerce/cart.php?remove=<?= htmlspecialchars($product['id']) ?>" class="btn-danger">Remove</a></td>
                        </tr>
                    <?php endforeach; ?>
                    <tr class="grand-total">
                        <td colspan="3" class="grand-total-label"><strong>Grand Total:</strong></td>
                        <td colspan="2" class="grand-total-value"><strong>$<?= htmlspecialchars(number_format($grand_total, 2)) ?></strong></td>
                    </tr>
                    </tbody>
                </table>
            </div>

            <div class="cart-actions">
                <a class="btn btn-checkout" href="/BagStore_Ecommerce/checkout.php">Proceed to Checkout</a>
                <a class="btn-secondary btn-empty-cart" href="/BagStore_Ecommerce/cart.php?empty=1">Empty Cart</a>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php include $_SERVER['DOCUMENT_ROOT'] . '/BagStore_Ecommerce/includes/footer.php'; ?>
