<?php
session_start();
include $_SERVER['DOCUMENT_ROOT'] . '/BagStore_Ecommerce/includes/db.php';

// 1. Security and Session Checks
if (!isset($_SESSION['user_id'])) {
    // User not logged in, redirect to login.
    $_SESSION['checkout_errors'] = ["Please login to place an order."];
    header("Location: /BagStore_Ecommerce/login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    // Accessed directly or with wrong method, redirect
    header("Location: /BagStore_Ecommerce/checkout.php");
    exit();
}

if (empty($_SESSION['cart'])) {
    // Cart is empty
    $_SESSION['checkout_errors'] = ["Your cart is empty. Cannot process order."];
    header("Location: /BagStore_Ecommerce/checkout.php");
    exit();
}

// 2. Input Collection & Basic Validation
$user_id = $_SESSION['user_id'];
$customer_name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$address_street = trim($_POST['address_street'] ?? '');
$address_city = trim($_POST['address_city'] ?? '');
$address_state = trim($_POST['address_state'] ?? '');
$address_zip = trim($_POST['address_zip'] ?? '');
$address_country = trim($_POST['address_country'] ?? '');
$payment_method = trim($_POST['payment_method'] ?? '');

$errors = [];
$form_data = $_POST; // For repopulating form

if (empty($customer_name)) $errors[] = "Full name is required.";
if (empty($email)) {
    $errors[] = "Email address is required.";
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Invalid email format.";
}
if (empty($phone)) $errors[] = "Phone number is required.";
// Example phone validation (adjust regex as needed for international numbers)
// else if (!preg_match('/^[0-9]{3}-?[0-9]{3}-?[0-9]{4}$/', $phone)) {
//    $errors[] = "Invalid phone number format (e.g., 123-456-7890).";
// }


if (empty($address_street)) $errors[] = "Street address is required.";
if (empty($address_city)) $errors[] = "City is required.";
if (empty($address_state)) $errors[] = "State/Province is required.";
if (empty($address_zip)) $errors[] = "ZIP/Postal code is required.";
if (empty($address_country)) $errors[] = "Country is required.";
if (empty($payment_method)) $errors[] = "Payment method is required.";


if (!empty($errors)) {
    $_SESSION['checkout_errors'] = $errors;
    $_SESSION['checkout_form_data'] = $form_data;
    header("Location: /BagStore_Ecommerce/checkout.php");
    exit();
}

// Combine address fields for storing in 'orders' table 'address' column
// Consider if your 'orders' table should have separate address component columns
$full_address = $address_street . "\n" .
                  $address_city . ", " . $address_state . " " . $address_zip . "\n" .
                  $address_country;

// 3. Database Interaction
$conn->begin_transaction();

try {
    // Calculate Grand Total from DB prices and check stock
    $grand_total = 0;
    $items_to_insert = [];
    $stock_sufficient = true;

    foreach ($_SESSION['cart'] as $product_id => $quantity_ordered) {
        if ($quantity_ordered <= 0) continue; // Skip if quantity is zero or less

        $stmt_product = $conn->prepare("SELECT name, price, stock_quantity FROM products WHERE id = ?");
        $stmt_product->bind_param("i", $product_id);
        $stmt_product->execute();
        $product_result = $stmt_product->get_result();

        if ($product = $product_result->fetch_assoc()) {
            if ($product['stock_quantity'] < $quantity_ordered) {
                $stock_sufficient = false;
                $errors[] = "Not enough stock for " . htmlspecialchars($product['name']) . ". Available: " . $product['stock_quantity'] . ", Ordered: " . $quantity_ordered . ". Please reduce quantity or remove from cart.";
            }
            $items_to_insert[] = [
                'product_id' => $product_id,
                'name' => $product['name'],
                'quantity' => $quantity_ordered,
                'price_per_unit' => $product['price']
            ];
            $grand_total += $product['price'] * $quantity_ordered;
        } else {
            $errors[] = "Product with ID $product_id not found. Please review your cart and remove unavailable items.";
            $stock_sufficient = false;
        }
        $stmt_product->close();
    }

    if (!$stock_sufficient || !empty($errors)) {
        // If stock issue or other product validation error, do not proceed with order
        throw new Exception("Product availability issue. See messages.");
    }
    if (empty($items_to_insert)) { // Double check if cart became empty after validation
         throw new Exception("Cannot process an empty order.");
    }


    // Insert into `orders` table
    $order_status = 'Pending'; // Default status
    // Assumed orders table columns: user_id, customer_name, email, phone, address, total, order_status, payment_method, created_at
    $stmt_order = $conn->prepare("INSERT INTO orders (user_id, customer_name, email, phone, address, total, order_status, payment_method, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
    if (!$stmt_order) throw new Exception("Order statement prepare failed: " . $conn->error);
    $stmt_order->bind_param("isssssds", $user_id, $customer_name, $email, $phone, $full_address, $grand_total, $order_status, $payment_method);

    if (!$stmt_order->execute()) {
        throw new Exception("Failed to create order: " . $stmt_order->error);
    }
    $new_order_id = $conn->insert_id;
    $stmt_order->close();

    // Insert into `order_items` table
    // Assumed order_items table columns: order_id, product_id, quantity, price (price_per_unit at time of order)
    $stmt_items = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
    if (!$stmt_items) throw new Exception("Order items statement prepare failed: " . $conn->error);
    foreach ($items_to_insert as $item) {
        $stmt_items->bind_param("iiid", $new_order_id, $item['product_id'], $item['quantity'], $item['price_per_unit']);
        if (!$stmt_items->execute()) {
            throw new Exception("Failed to save order items for product ID " . $item['product_id'] . ": " . $stmt_items->error);
        }
    }
    $stmt_items->close();

    // Update stock quantities in `products` table
    $stmt_update_stock = $conn->prepare("UPDATE products SET stock_quantity = stock_quantity - ? WHERE id = ? AND stock_quantity >= ?");
    if (!$stmt_update_stock) throw new Exception("Stock update statement prepare failed: " . $conn->error);
    foreach ($items_to_insert as $item) {
        $stmt_update_stock->bind_param("iii", $item['quantity'], $item['product_id'], $item['quantity']);
        if (!$stmt_update_stock->execute() || $stmt_update_stock->affected_rows == 0) {
            // If stock update fails (e.g. race condition where stock became insufficient after initial check)
            throw new Exception("Critical error updating stock for " . htmlspecialchars($item['name']) . ". Order rolled back.");
        }
    }
    $stmt_update_stock->close();

    $conn->commit();

    unset($_SESSION['cart']);
    unset($_SESSION['checkout_form_data']);

    $_SESSION['last_order_id'] = $new_order_id;
    $_SESSION['order_success_message'] = "Thank you, " . htmlspecialchars($customer_name) . "! Your order (#$new_order_id) has been placed successfully.";
    header("Location: /BagStore_Ecommerce/my_orders.php?order_success=true");
    exit();

} catch (Exception $e) {
    $conn->rollback();
    if (empty($errors)) { // Ensure $errors array has the main exception message if it's empty
        $errors[] = "An error occurred: " . $e->getMessage();
    }
    error_log("Order processing failed: " . $e->getMessage() . " for user_id: " . $user_id);

    $_SESSION['checkout_errors'] = $errors;
    $_SESSION['checkout_form_data'] = $form_data;
    header("Location: /BagStore_Ecommerce/checkout.php");
    exit();
}
?>
