<?php
session_start();
include 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$orders = $conn->query("SELECT * FROM orders WHERE user_id = $user_id ORDER BY created_at DESC");
?>
<?php include $_SERVER['DOCUMENT_ROOT'] . '/BagStore_Ecommerce/includes/header.php'; ?>

<!DOCTYPE html>
<html>
<head>
    <title>My Orders</title>
    <link rel="stylesheet" href="css/orders.css">
</head>
<body>
    <h1>My Orders</h1>
    <?php if ($orders->num_rows > 0): ?>
    <table border="1" cellpadding="10">
        <tr>
            <th>Order ID</th>
            <th>Total</th>
            <th>Date</th>
            <th>Details</th>
        </tr>
        <?php while ($order = $orders->fetch_assoc()): ?>
        <tr>
            <td><?= $order['id'] ?></td>
            <td>$<?= $order['total'] ?></td>
            <td><?= $order['created_at'] ?></td>
            <td><a href="order_detail.php?id=<?= $order['id'] ?>">View</a></td>
        </tr>
        <?php endwhile; ?>
    </table>
    <?php else: ?>
        <p>You have no orders yet. <a href="products.php">Start shopping</a></p>
    <?php endif; ?>
</body>
</html>
