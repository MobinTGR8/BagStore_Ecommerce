<?php
session_start();
include 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (empty($_SESSION['cart'])) {
    include $_SERVER['DOCUMENT_ROOT'] . '/BagStore_Ecommerce/includes/header.php';
    echo '
    <!DOCTYPE html>
    <html>
    <head>
        <title>Checkout</title>
        <style>
            body { color: #fff; }
        </style>
        <link rel="stylesheet" href="css/checkout.css?v=' . time() . '">
    </head>
    <body>
        <div class="empty-cart-msg">
            <h2>Your cart is empty.</h2>
            <a href="products.php" class="btn browse-btn">Shop Now</a>
        </div>
    </body>
    </html>';
    exit();
}

$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $conn->real_escape_string($_POST['name']);
    $address = $conn->real_escape_string($_POST['address']);

    // Calculate total
    $total = 0;
    foreach ($_SESSION['cart'] as $product_id => $qty) {
        $product = $conn->query("SELECT * FROM products WHERE id = $product_id")->fetch_assoc();
        $total += $product['price'] * $qty;
    }

    // Insert order
    $user_id = $_SESSION['user_id'];
    $conn->query("INSERT INTO orders (user_id, customer_name, address, total) VALUES ('$user_id', '$name', '$address', '$total')");
    $order_id = $conn->insert_id;

    // Insert order items
    foreach ($_SESSION['cart'] as $product_id => $qty) {
        $product = $conn->query("SELECT * FROM products WHERE id = $product_id")->fetch_assoc();
        $price = $product['price'];
        $conn->query("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES ('$order_id', '$product_id', '$qty', '$price')");
    }

    // Clear cart
    $_SESSION['cart'] = [];
    $success = "Order placed successfully! Thank you, $name.";
}

include $_SERVER['DOCUMENT_ROOT'] . '/BagStore_Ecommerce/includes/header.php';
?>

<!DOCTYPE html>
<html>
<head>
    <title>Checkout</title>
    <link rel="stylesheet" href="css/checkout.css?v=<?= time() ?>">
</head>
<body>

<h1>Checkout</h1>

<?php if ($success): ?>
    <div class="success-msg">
        <p><?= $success ?></p>
        <a href="products.php" class="btn">Continue Shopping</a>
    </div>
<?php else: ?>
    <form method="POST" action="checkout.php" class="checkout-form">
        <input type="text" name="name" placeholder="Your Name" required>
        <textarea name="address" placeholder="Shipping Address" required></textarea>

        <select name="payment_method" required>
            <option value="">Select Payment Method</option>
            <option value="cod">Cash on Delivery</option>
            <option value="credit_card">Credit Card</option>
            <option value="bkash">Bkash</option>
            <option value="nagad">Nagad</option>
        </select>

        <button type="submit" class="btn">Place Order</button>
    </form>
<?php endif; ?>

</body>
</html>
