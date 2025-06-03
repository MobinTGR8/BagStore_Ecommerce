<?php
session_start();
include $_SERVER['DOCUMENT_ROOT'] . '/BagStore_Ecommerce/includes/db.php'; // Corrected path

if (!isset($_SESSION['admin_id'])) { // Changed to check for admin_id for consistency if login sets that
    header("Location: /BagStore_Ecommerce/admin_login.php"); // Absolute path
    exit();
}

// Fetch orders - consider pagination for many orders
$orders_result = $conn->query("SELECT * FROM orders ORDER BY created_at DESC");

$pageTitle = "Admin Dashboard - Orders";
$pageSpecificCss = "/BagStore_Ecommerce/css/admin.css?v=" . time(); // Added cache buster
$body_class = "admin-page"; // Add a general class for admin pages
?>
<?php include $_SERVER['DOCUMENT_ROOT'] . '/BagStore_Ecommerce/includes/header.php'; ?>

<main class="admin-main-container">
    <div class="container orders-page-container"> <!-- Reusing .container from global, and specific class -->
        <h1>Admin Dashboard</h1>
        <h2>All Orders</h2>
        <?php if ($orders_result && $orders_result->num_rows > 0): ?>
        <div class="table-responsive-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer Name</th>
                        <th>Address</th>
                        <th>Total</th>
                        <th>Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($order = $orders_result->fetch_assoc()): ?>
                    <tr>
                        <td data-label="Order ID"><?= htmlspecialchars($order['id']) ?></td>
                        <td data-label="Customer Name"><?= htmlspecialchars($order['customer_name']) ?></td>
                        <td data-label="Address"><?= htmlspecialchars($order['address']) ?></td>
                        <td data-label="Total">$<?= htmlspecialchars(number_format($order['total'], 2)) ?></td>
                        <td data-label="Date"><?= htmlspecialchars(date("M d, Y H:i", strtotime($order['created_at']))) ?></td>
                        <td data-label="Action"><a href="/BagStore_Ecommerce/admin_order_detail.php?id=<?= htmlspecialchars($order['id']) ?>" class="btn btn-secondary btn-sm">View</a></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
            <p class="empty-msg">No orders found.</p>
        <?php endif; ?>
    </div>
</main>

<?php include $_SERVER['DOCUMENT_ROOT'] . '/BagStore_Ecommerce/includes/footer.php'; ?>
