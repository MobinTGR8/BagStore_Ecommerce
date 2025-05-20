<?php
session_start();
include 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$order_id = intval($_GET['id']);

// Ensure this order belongs to the logged-in user
$user_id = $_SESSION['user_id'];
$order = $conn->query("SELECT * FROM orders WHERE id = $order_id AND user_id = $user_id")->fetch_assoc();

if (!$order) {
    echo "<p>Order not found or access denied.</p>";
    exit();
}

$items = $conn->query("
    SELECT order_items.*, products.name 
    FROM order_items 
    JOIN products ON order_items.product_id = products.id 
    WHERE order_id = $order_id
");
?>
<?php include $_SERVER['DOCUMENT_ROOT'] . '/BagStore_Ecommerce/includes/header.php'; ?>

<!DOCTYPE html>
<html>
<head>
    <title>Order #<?= $order['id'] ?> Details</title>
    <link rel="stylesheet" href="css/orders.css">
</head>
<body>
    <h1>Order #<?= $order['id'] ?></h1>
    <p><strong>Date:</strong> <?= $order['created_at'] ?></p>
    <p><strong>Total:</strong> $<?= $order['total'] ?></p>
    <h3>Items</h3>
    <table border="1" cellpadding="10">
        <tr>
            <th>Product</th>
            <th>Qty</th>
            <th>Price</th>
            <th>Subtotal</th>
        </tr>
        <?php while ($item = $items->fetch_assoc()): ?>
        <tr>
            <td><?= $item['name'] ?></td>
            <td><?= $item['quantity'] ?></td>
            <td>$<?= $item['price'] ?></td>
            <td>$<?= number_format($item['quantity'] * $item['price'], 2) ?></td>
        </tr>
        <?php endwhile; ?>
    </table>
    <br>
    <a href="my_orders.php">← Back to My Orders</a>
</body>
</html>
