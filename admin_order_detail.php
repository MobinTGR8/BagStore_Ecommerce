<?php
session_start();
include $_SERVER['DOCUMENT_ROOT'] . '/BagStore_Ecommerce/includes/db.php'; // Corrected path

if (!isset($_SESSION['admin_id'])) { // Changed to check for admin_id
    header("Location: /BagStore_Ecommerce/admin_login.php"); // Absolute path
    exit();
}

$order_id = intval($_GET['id'] ?? 0);

if ($order_id <= 0) {
    $pageTitle = "Error - Invalid Order ID";
    $pageSpecificCss = "/BagStore_Ecommerce/css/admin.css?v=" . time();
    $body_class = "admin-page error-page";
    include $_SERVER['DOCUMENT_ROOT'] . '/BagStore_Ecommerce/includes/header.php';
    echo "<main class='admin-main-container'><div class='container'><p class='empty-msg error-message'>Invalid Order ID provided.</p><a href='/BagStore_Ecommerce/admin_dashboard.php' class='btn btn-secondary'>Back to Dashboard</a></div></main>";
    include $_SERVER['DOCUMENT_ROOT'] . '/BagStore_Ecommerce/includes/footer.php';
    exit();
}

// Fetch order details using prepared statement
$stmt = $conn->prepare("SELECT * FROM orders WHERE id = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$result = $stmt->get_result();
$order = $result->fetch_assoc();
$stmt->close();

if (!$order) {
    $pageTitle = "Error - Order Not Found";
    $pageSpecificCss = "/BagStore_Ecommerce/css/admin.css?v=" . time();
    $body_class = "admin-page error-page";
    include $_SERVER['DOCUMENT_ROOT'] . '/BagStore_Ecommerce/includes/header.php';
    echo "<main class='admin-main-container'><div class='container'><p class='empty-msg error-message'>Order not found.</p><a href='/BagStore_Ecommerce/admin_dashboard.php' class='btn btn-secondary'>Back to Dashboard</a></div></main>";
    include $_SERVER['DOCUMENT_ROOT'] . '/BagStore_Ecommerce/includes/footer.php';
    exit();
}

// Fetch order items using prepared statement
$items_stmt = $conn->prepare("
    SELECT oi.quantity, oi.price, p.name
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    WHERE oi.order_id = ?
");
$items_stmt->bind_param("i", $order_id);
$items_stmt->execute();
$items_result = $items_stmt->get_result();

$pageTitle = "Order #" . htmlspecialchars($order['id']) . " Details";
$pageSpecificCss = "/BagStore_Ecommerce/css/admin.css?v=" . time();
$body_class = "admin-page";
?>
<?php include $_SERVER['DOCUMENT_ROOT'] . '/BagStore_Ecommerce/includes/header.php'; ?>

<main class="admin-main-container">
    <div class="container orders-page-container"> <!-- Reusing classes -->
        <h1>Order #<?= htmlspecialchars($order['id']) ?> Details</h1>

        <div class="order-summary-card">
            <p><strong>Customer Name:</strong> <?= htmlspecialchars($order['customer_name']) ?></p>
            <p><strong>Shipping Address:</strong> <?= htmlspecialchars($order['address']) ?></p>
            <p><strong>Order Total:</strong> $<?= htmlspecialchars(number_format($order['total'], 2)) ?></p>
            <p><strong>Order Date:</strong> <?= htmlspecialchars(date("M d, Y H:i", strtotime($order['created_at']))) ?></p>
            <!-- Placeholder for Order Status - To be implemented later -->
            <!--
            <p><strong>Order Status:</strong> <?= htmlspecialchars($order['status'] ?? 'N/A') ?></p>
            <form action="admin_update_order_status.php" method="POST" style="margin-top: 10px;">
                <input type="hidden" name="order_id" value="<?= htmlspecialchars($order['id']) ?>">
                <select name="status">
                    <option value="pending" <?= ($order['status'] ?? '') == 'pending' ? 'selected' : '' ?>>Pending</option>
                    <option value="shipped" <?= ($order['status'] ?? '') == 'shipped' ? 'selected' : '' ?>>Shipped</option>
                    <option value="delivered" <?= ($order['status'] ?? '') == 'delivered' ? 'selected' : '' ?>>Delivered</option>
                    <option value="canceled" <?= ($order['status'] ?? '') == 'canceled' ? 'selected' : '' ?>>Canceled</option>
                </select>
                <button type="submit" class="btn btn-primary btn-sm" style="margin-left: 10px;">Update Status</button>
            </form>
            -->
        </div>

        <h2>Items Ordered</h2>
        <?php if ($items_result && $items_result->num_rows > 0): ?>
        <div class="table-responsive-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Product Name</th>
                        <th>Quantity</th>
                        <th>Price per Item</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($item = $items_result->fetch_assoc()): ?>
                    <tr>
                        <td data-label="Product Name"><?= htmlspecialchars($item['name']) ?></td>
                        <td data-label="Quantity"><?= htmlspecialchars($item['quantity']) ?></td>
                        <td data-label="Price per Item">$<?= htmlspecialchars(number_format($item['price'], 2)) ?></td>
                        <td data-label="Subtotal">$<?= htmlspecialchars(number_format($item['quantity'] * $item['price'], 2)) ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
            <p class="empty-msg">No items found for this order.</p>
        <?php endif; ?>

        <div class="actions-container" style="margin-top: 20px;">
            <a href="/BagStore_Ecommerce/admin_dashboard.php" class="btn btn-secondary">← Back to Dashboard</a>
        </div>
    </div>
</main>

<?php
if(isset($items_stmt)) $items_stmt->close();
include $_SERVER['DOCUMENT_ROOT'] . '/BagStore_Ecommerce/includes/footer.php';
?>
