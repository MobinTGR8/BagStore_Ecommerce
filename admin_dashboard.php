<?php
session_start();
include 'includes/db.php';

if (!isset($_SESSION['admin'])) {
    header("Location: admin_login.php");
    exit();
}

$orders = $conn->query("SELECT * FROM orders ORDER BY created_at DESC");
?>
<?php include $_SERVER['DOCUMENT_ROOT'] . '/BagStore_Ecommerce/includes/header.php'; ?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="css/admin.css?v=1.1">

</head>
<body>
    <div style="display: flex; justify-content: center; align-items: center; min-height: 80vh; flex-direction: column;">
        <h1>Admin Dashboard</h1>
        <h3>All Orders</h3>
        <table>
            <tr>
                <th>Order ID</th>
                <th>Customer</th>
                <th>Address</th>
                <th>Total</th>
                <th>Date</th>
                <th>Action</th>
            </tr>
            <?php while ($row = $orders->fetch_assoc()): ?>
            <tr>
    <td><?= $row['id'] ?></td>
    <td><?= $row['customer_name'] ?></td>
    <td><?= $row['address'] ?></td>
    <td>$<?= number_format($row['total'], 2) ?></td>
    <td><?= $row['created_at'] ?></td>
    <td><a href="admin_order_detail.php?id=<?= $row['id'] ?>" class="action-link">View Items</a></td>
</tr>

             <?php endwhile; ?>
         </table>
    
</body>

</html>
