<?php
session_start();
include $_SERVER['DOCUMENT_ROOT'] . '/BagStore_Ecommerce/includes/db.php'; // Corrected path

if (!isset($_SESSION['user_id'])) {
    header("Location: /BagStore_Ecommerce/login.php"); // Absolute path
    exit();
}

if (!isset($_GET['id'])) {
    // Or redirect to my_orders.php with an error message
    $pageTitle = "Error";
    $pageSpecificCss = "/BagStore_Ecommerce/css/orders.css?v=" . time();
    include $_SERVER['DOCUMENT_ROOT'] . '/BagStore_Ecommerce/includes/header.php';
    echo "<main class='orders-main-container'><div class='container orders-page-container'><p class='empty-msg error-message'>Order ID missing.</p></div></main>";
    include $_SERVER['DOCUMENT_ROOT'] . '/BagStore_Ecommerce/includes/footer.php';
    exit();
}

$order_id = intval($_GET['id']);
$user_id = $_SESSION['user_id'];

$order_stmt = $conn->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
$order_stmt->bind_param("ii", $order_id, $user_id);
$order_stmt->execute();
$order_result = $order_stmt->get_result();
$order = $order_result->fetch_assoc();
$order_stmt->close();

if (!$order) {
    $pageTitle = "Order Not Found";
    $pageSpecificCss = "/BagStore_Ecommerce/css/orders.css?v=" . time();
    include $_SERVER['DOCUMENT_ROOT'] . '/BagStore_Ecommerce/includes/header.php';
    echo "<main class='orders-main-container'><div class='container orders-page-container'><p class='empty-msg error-message'>Order not found or access denied.</p> <a href='/BagStore_Ecommerce/my_orders.php' class='button'>Back to My Orders</a></div></main>";
    include $_SERVER['DOCUMENT_ROOT'] . '/BagStore_Ecommerce/includes/footer.php';
    exit();
}

$items_stmt = $conn->prepare("
    SELECT order_items.*, products.name
    FROM order_items
    JOIN products ON order_items.product_id = products.id
    WHERE order_id = ?
");
$items_stmt->bind_param("i", $order_id);
$items_stmt->execute();
$items_result = $items_stmt->get_result();

$pageTitle = "Order #" . htmlspecialchars($order['id']) . " Details";
$pageSpecificCss = "/BagStore_Ecommerce/css/orders.css?v=" . time();
?>
<?php include $_SERVER['DOCUMENT_ROOT'] . '/BagStore_Ecommerce/includes/header.php'; ?>

<main class="orders-main-container">
    <div class="container orders-page-container"> <!-- Added specific class -->
        <h1>Order #<?= htmlspecialchars($order['id']) ?> Details</h1>
        <div class="order-summary-card">
            <p><strong>Date:</strong> <?= htmlspecialchars(date("M d, Y H:i", strtotime($order['created_at']))) ?></p>
            <p><strong>Customer Name:</strong> <?= htmlspecialchars($order['customer_name']) ?></p>
            <p><strong>Shipping Address:</strong> <?= htmlspecialchars($order['address']) ?></p>
            <p><strong>Total:</strong> $<?= htmlspecialchars(number_format($order['total'], 2)) ?></p>
        </div>

        <h2>Items</h2>
        <?php if ($items_result->num_rows > 0): ?>
        <div class="table-responsive-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Quantity</th>
                        <th>Price per item</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($item = $items_result->fetch_assoc()): ?>
                    <tr>
                        <td data-label="Product"><?= htmlspecialchars($item['name']) ?></td>
                        <td data-label="Quantity"><?= htmlspecialchars($item['quantity']) ?></td>
                        <td data-label="Price per item">$<?= htmlspecialchars(number_format($item['price'], 2)) ?></td>
                        <td data-label="Subtotal">$<?= htmlspecialchars(number_format($item['quantity'] * $item['price'], 2)) ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
            <p class="empty-msg">No items found for this order.</p>
        <?php endif; ?>
        <div class="actions-container">
            <a href="/BagStore_Ecommerce/my_orders.php" class="button">← Back to My Orders</a>
        </div>
    </div>
</main>

<?php
$items_stmt->close();
include $_SERVER['DOCUMENT_ROOT'] . '/BagStore_Ecommerce/includes/footer.php';
?>
