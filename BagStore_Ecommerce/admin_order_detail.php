<?php
session_start();
include 'includes/db.php';

if (!isset($_SESSION['admin'])) {
    header("Location: admin_login.php");
    exit();
}

$order_id = intval($_GET['id']);

$order = $conn->query("SELECT * FROM orders WHERE id = $order_id")->fetch_assoc();
$items = $conn->query("SELECT order_items.*, products.name FROM order_items JOIN products ON order_items.product_id = products.id WHERE order_items.order_id = $order_id");
?>
<?php include $_SERVER['DOCUMENT_ROOT'] . '/BagStore_Ecommerce/includes/header.php'; ?>

<!DOCTYPE html>
<html>
<head>
    <title>Order Details</title>
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>
    <h1>Order #<?= $order['id'] ?> Details</h1>
    <p><strong>Customer:</strong> <?= $order['customer_name'] ?></p>
    <p><strong>Address:</strong> <?= $order['address'] ?></p>
    <p><strong>Total:</strong> $<?= $order['total'] ?></p>
    <p><strong>Placed On:</strong> <?= $order['created_at'] ?></p>

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
            <td>$<?= number_format($item['price'] * $item['quantity'], 2) ?></td>
        </tr>
        <?php endwhile; ?>
    </table>

    <br><a href="admin_dashboard.php">← Back to Dashboard</a>
</body>
</html>
