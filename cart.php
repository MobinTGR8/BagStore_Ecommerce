<?php
session_start();
include 'includes/db.php';

// Initialize cart if not already
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Add item to cart
if (isset($_GET['add'])) {
    $id = intval($_GET['add']);
    $_SESSION['cart'][$id] = ($_SESSION['cart'][$id] ?? 0) + 1;
    header("Location: cart.php");
    exit();
}

// Remove item from cart
if (isset($_GET['remove'])) {
    $id = intval($_GET['remove']);
    unset($_SESSION['cart'][$id]);
    header("Location: cart.php");
    exit();
}

// Empty cart
if (isset($_GET['empty'])) {
    $_SESSION['cart'] = [];
    header("Location: cart.php");
    exit();
}
?>
<?php include $_SERVER['DOCUMENT_ROOT'] . '/BagStore_Ecommerce/includes/header.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Your Cart</title>
    <link rel="stylesheet" href="/BagStore_Ecommerce/css/cart.css?v=<?= time() ?>">
</head>
<body>
<main class="cart-container">
    <h1>Your Shopping Cart</h1>

    <?php if (empty($_SESSION['cart'])): ?>
        <p class="empty-msg">Your cart is empty. <a href="products.php">Browse Products</a></p>
    <?php else: ?>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Qty</th>
                        <th>Price</th>
                        <th>Total</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $grand_total = 0;
                foreach ($_SESSION['cart'] as $product_id => $qty):
                    $sql = "SELECT * FROM products WHERE id = $product_id";
                    $result = $conn->query($sql);
                    if ($result->num_rows == 0) continue;

                    $product = $result->fetch_assoc();
                    $total = $product['price'] * $qty;
                    $grand_total += $total;
                ?>
                    <tr>
                        <td><?= htmlspecialchars($product['name']) ?></td>
                        <td><?= $qty ?></td>
                        <td>$<?= number_format($product['price'], 2) ?></td>
                        <td>$<?= number_format($total, 2) ?></td>
                        <td><a href="cart.php?remove=<?= $product['id'] ?>" class="btn-danger">Remove</a></td>
                    </tr>
                <?php endforeach; ?>
                <tr class="grand-total">
                    <td colspan="3" align="right"><strong>Grand Total:</strong></td>
                    <td colspan="2"><strong>$<?= number_format($grand_total, 2) ?></strong></td>
                </tr>
                </tbody>
            </table>
        </div>

        <div class="cart-actions">
            <a class="btn" href="checkout.php">Proceed to Checkout</a>
            <a class="btn-secondary" href="cart.php?empty=1">Empty Cart</a>
        </div>
    <?php endif; ?>
</main>
</body>
</html>
