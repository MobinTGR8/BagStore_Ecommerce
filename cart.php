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
    if (isset($_SESSION['cart'][$id])) {
        $_SESSION['cart'][$id]++;
    } else {
        $_SESSION['cart'][$id] = 1;
    }
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
<html>
<head>
    <title>Your Cart</title>
    <link rel="stylesheet" href="css/cart.css">
</head>
<body>
    <h1>Your Shopping Cart</h1>

    <?php if (empty($_SESSION['cart'])): ?>
        <p>Your cart is empty. <a href="products.php">Browse Products</a></p>
    <?php else: ?>
        <table border="1" cellpadding="10" style="margin: auto;">
            <tr>
                <th>Product</th>
                <th>Qty</th>
                <th>Price</th>
                <th>Total</th>
                <th>Action</th>
            </tr>
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
                <td><?= $product['name'] ?></td>
                <td><?= $qty ?></td>
                <td>$<?= $product['price'] ?></td>
                <td>$<?= number_format($total, 2) ?></td>
                <td><a href="cart.php?remove=<?= $product['id'] ?>">Remove</a></td>
            </tr>
            <?php endforeach; ?>
            <tr>
                <td colspan="3" align="right"><strong>Grand Total:</strong></td>
                <td colspan="2">$<?= number_format($grand_total, 2) ?></td>
            </tr>
        </table>

        <br>
        <a href="checkout.php">Proceed to Checkout</a><br><br>
        <a href="cart.php?empty=1">Empty Cart</a>
    <?php endif; ?>
</body>
</html>
