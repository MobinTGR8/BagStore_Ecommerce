<?php
session_start();
include $_SERVER['DOCUMENT_ROOT'] . '/BagStore_Ecommerce/includes/db.php'; // Corrected path

if (!isset($_SESSION['user_id'])) {
    header("Location: /BagStore_Ecommerce/login.php"); // Absolute path
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch orders using prepared statement
$stmt = $conn->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
if ($stmt) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $orders_result = $stmt->get_result(); // Renamed to avoid conflict if $orders was used elsewhere by mistake
    $stmt->close();
} else {
    // Handle prepare statement error, e.g., log it or set $orders_result to null/empty array
    error_log("Prepare statement failed in my_orders.php: " . $conn->error);
    $orders_result = null;
}


$pageTitle = "My Orders";
$pageSpecificCss = "/BagStore_Ecommerce/css/orders.css?v=" . time();

// Check for order success message
$order_success_message = $_SESSION['order_success_message'] ?? null;
unset($_SESSION['order_success_message']); // Clear message after retrieving

// Optionally, retrieve last_order_id if needed for highlighting, etc.
// $last_order_id = $_SESSION['last_order_id'] ?? null;
// unset($_SESSION['last_order_id']); // Clear if used
?>
<?php include $_SERVER['DOCUMENT_ROOT'] . '/BagStore_Ecommerce/includes/header.php'; ?>

<main class="orders-main-container">
    <div class="container orders-page-container"> <!-- Added specific class for orders page container -->
        <h1>My Orders</h1>

        <?php if ($order_success_message): ?>
            <div class="checkout-message success-message" style="margin-bottom: var(--spacing-unit); background-color: var(--success-bg); color: var(--success-text); border: 1px solid var(--success-border); padding: var(--spacing-unit); border-radius: var(--border-radius-base);">
                <?= htmlspecialchars($order_success_message) ?>
            </div>
        <?php endif; ?>

        <?php if ($orders_result && $orders_result->num_rows > 0): ?>
        <div class="table-responsive-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Total</th>
                        <th>Order Status</th> <!-- Added Order Status -->
                        <th>Date</th>
                        <th>Details</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($order = $orders_result->fetch_assoc()): ?>
                    <tr>
                        <td data-label="Order ID"><?= htmlspecialchars($order['id']) ?></td>
                        <td data-label="Total">$<?= htmlspecialchars(number_format($order['total'], 2)) ?></td>
                        <td data-label="Order Status"><?= htmlspecialchars($order['order_status'] ?? 'N/A') ?></td> <!-- Display order_status -->
                        <td data-label="Date"><?= htmlspecialchars(date("M d, Y H:i", strtotime($order['created_at']))) ?></td>
                        <td data-label="Details"><a href="/BagStore_Ecommerce/order_detail.php?id=<?= htmlspecialchars($order['id']) ?>" class="btn btn-secondary btn-sm">View</a></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
            <p class="empty-msg">You have no orders yet. <a href="/BagStore_Ecommerce/products.php" class="btn btn-primary">Start shopping</a></p>
        <?php endif; ?>
    </div>
</main>

<?php include $_SERVER['DOCUMENT_ROOT'] . '/BagStore_Ecommerce/includes/footer.php'; ?>
