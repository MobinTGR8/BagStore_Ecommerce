<?php
session_start();
include 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customer_name = $conn->real_escape_string($_POST['name']);
    $address = $conn->real_escape_string($_POST['address']);
    $payment_method = $conn->real_escape_string($_POST['payment_method']);
    $user_id = $_SESSION['user_id'] ?? NULL;

    $total = 0;

    foreach ($_SESSION['cart'] as $product_id => $quantity) {
        // Get product price from database
        $pid = (int)$product_id;
        $result = $conn->query("SELECT price FROM products WHERE id = $pid");
        if ($row = $result->fetch_assoc()) {
            $total += $row['price'] * $quantity;
        }
    }

    // Insert into orders table
    $sql = "INSERT INTO orders (user_id, customer_name, address, total, payment_method)
            VALUES ('$user_id', '$customer_name', '$address', '$total', '$payment_method')";

    if ($conn->query($sql)) {
        $order_id = $conn->insert_id;

        // Insert ordered items
        foreach ($_SESSION['cart'] as $product_id => $quantity) {
            $conn->query("INSERT INTO order_items (order_id, product_id, quantity) VALUES ('$order_id', '$product_id', '$quantity')");
        }

        // Clear cart
        unset($_SESSION['cart']);

        echo "<div style='display: flex; justify-content: center; align-items: center; height: 60vh;'>";
        echo "<div>";
        echo "✅ Order placed successfully with payment method: <b>" . htmlspecialchars($payment_method) . "</b><br>";
        echo "<a href='index.php'>Return to Home</a>";
        echo "</div>";
        echo "</div>";
        } else {
        echo "<div style='display: flex; justify-content: center; align-items: center; height: 60vh;'>";
        echo "<div>";
        echo "❌ Error: " . htmlspecialchars($conn->error);
        echo "</div>";
        echo "</div>";
        }
}
?>
