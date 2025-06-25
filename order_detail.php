<?php
session_start();
include 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$order_id = intval($_GET['id']);
$user_id = $_SESSION['user_id'];

$order = $conn->query("SELECT * FROM orders WHERE id = $order_id AND user_id = $user_id")->fetch_assoc();

if (!$order) {
    include $_SERVER['DOCUMENT_ROOT'] . '/BagStore_Ecommerce/includes/header.php';
    echo "<div class='empty-msg'><p>Order not found or access denied.</p></div>";
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
    <link rel="stylesheet" href="css/orders.css?v=<?= time() ?>">
</head>
<body>
    <div class="container">
        <h1>Order #<?= $order['id'] ?> Details</h1>
        <p><strong>Date:</strong> <?= $order['created_at'] ?></p>
        <p><strong>Total:</strong> $<?= number_format($order['total'], 2) ?></p>

        <h3>Items</h3>
        <table>
            <tr>
                <th>Product</th>
                <th>Qty</th>
                <th>Price</th>
                <th>Subtotal</th>
            </tr>
            <?php while ($item = $items->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($item['name']) ?></td>
                <td><?= $item['quantity'] ?></td>
                <td>$<?= number_format($item['price'], 2) ?></td>
                <td>$<?= number_format($item['quantity'] * $item['price'], 2) ?></td>
            </tr>
            <?php endwhile; ?>
        </table>

        <br>
        <a href="my_orders.php" class="button">← Back to My Orders</a>
    </div>
</body>
</html>
