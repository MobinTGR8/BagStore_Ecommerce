<?php
session_start();
include $_SERVER['DOCUMENT_ROOT'] . '/BagStore_Ecommerce/includes/db.php'; // Corrected path

if (!isset($_SESSION['user_id'])) {
    header("Location: /BagStore_Ecommerce/login.php"); // Absolute path
    exit();
}

if (empty($_SESSION['cart'])) {
    $pageTitle = "Checkout - Cart Empty";
    $pageSpecificCss = "/BagStore_Ecommerce/css/checkout.css?v=" . time();
    $body_class = "checkout-page";
    include $_SERVER['DOCUMENT_ROOT'] . '/BagStore_Ecommerce/includes/header.php';
    echo '
        <main class="checkout-container checkout-empty">
            <div class="empty-cart-msg">
                <h2>Your cart is empty.</h2>
                <a href="/BagStore_Ecommerce/products.php" class="btn browse-btn">Shop Now</a>
            </div>
        </main>';
    include $_SERVER['DOCUMENT_ROOT'] . '/BagStore_Ecommerce/includes/footer.php';
    exit();
}

// Retrieve error messages from session if redirected from process_order.php
$errors = $_SESSION['checkout_errors'] ?? [];
$form_data = $_SESSION['checkout_form_data'] ?? [];
unset($_SESSION['checkout_errors'], $_SESSION['checkout_form_data']);

$pageTitle = "Checkout";
$pageSpecificCss = "/BagStore_Ecommerce/css/checkout.css?v=" . time();
$body_class = "checkout-page";
?>
<?php include $_SERVER['DOCUMENT_ROOT'] . '/BagStore_Ecommerce/includes/header.php'; ?>

<main class="checkout-container">
    <h1>Checkout</h1>

    <?php if (!empty($errors)): ?>
        <div class="checkout-message error-message">
            <h4>Please correct the errors below:</h4>
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="checkout-layout">
        <section class="order-summary-section">
            <h2>Order Summary</h2>
            <div class="cart-summary">
                <?php if (!empty($_SESSION['cart'])): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Qty</th>
                                <th>Price</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $grand_total = 0;
                            foreach ($_SESSION['cart'] as $product_id => $qty):
                                $stmt = $conn->prepare("SELECT name, price, image_path FROM products WHERE id = ?");
                                $stmt->bind_param("i", $product_id);
                                $stmt->execute();
                                $result = $stmt->get_result();
                                if ($product = $result->fetch_assoc()):
                                    $subtotal = $product['price'] * $qty;
                                    $grand_total += $subtotal;
                            ?>
                                <tr>
                                    <td>
                                        <img src="/BagStore_Ecommerce/images/<?= htmlspecialchars($product['image_path']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="cart-summary-image">
                                        <?= htmlspecialchars($product['name']) ?>
                                    </td>
                                    <td><?= htmlspecialchars($qty) ?></td>
                                    <td>$<?= htmlspecialchars(number_format($product['price'], 2)) ?></td>
                                    <td>$<?= htmlspecialchars(number_format($subtotal, 2)) ?></td>
                                </tr>
                            <?php
                                endif;
                                $stmt->close();
                            endforeach;
                            ?>
                        </tbody>
                        <tfoot>
                            <tr class="grand-total">
                                <td colspan="3"><strong>Grand Total:</strong></td>
                                <td><strong>$<?= htmlspecialchars(number_format($grand_total, 2)) ?></strong></td>
                            </tr>
                        </tfoot>
                    </table>
                <?php endif; ?>
            </div>
        </section>

        <section class="checkout-form-section">
            <h2>Shipping & Payment</h2>
            <form method="POST" action="/BagStore_Ecommerce/process_order.php" class="checkout-form">
                <div>
                    <label for="name">Full Name:</label>
                    <input type="text" id="name" name="name" placeholder="Enter your full name" value="<?= htmlspecialchars($form_data['name'] ?? '') ?>" required>
                </div>
                <div>
                    <label for="email">Email Address:</label>
                    <input type="email" id="email" name="email" placeholder="you@example.com" value="<?= htmlspecialchars($form_data['email'] ?? '') ?>" required>
                </div>
                <div>
                    <label for="phone">Phone Number:</label>
                    <input type="tel" id="phone" name="phone" placeholder="e.g., 123-456-7890" value="<?= htmlspecialchars($form_data['phone'] ?? '') ?>" required>
                </div>
                <div>
                    <label for="address_street">Street Address:</label>
                    <input type="text" id="address_street" name="address_street" placeholder="123 Main St" value="<?= htmlspecialchars($form_data['address_street'] ?? '') ?>" required>
                </div>
                <div>
                    <label for="address_city">City:</label>
                    <input type="text" id="address_city" name="address_city" placeholder="Anytown" value="<?= htmlspecialchars($form_data['address_city'] ?? '') ?>" required>
                </div>
                <div>
                    <label for="address_state">State/Province:</label>
                    <input type="text" id="address_state" name="address_state" placeholder="CA" value="<?= htmlspecialchars($form_data['address_state'] ?? '') ?>" required>
                </div>
                <div>
                    <label for="address_zip">ZIP/Postal Code:</label>
                    <input type="text" id="address_zip" name="address_zip" placeholder="90210" value="<?= htmlspecialchars($form_data['address_zip'] ?? '') ?>" required>
                </div>
                <div>
                    <label for="address_country">Country:</label>
                    <input type="text" id="address_country" name="address_country" placeholder="USA" value="<?= htmlspecialchars($form_data['address_country'] ?? '') ?>" required>
                </div>

                <div>
                    <label for="payment_method">Payment Method:</label>
                    <select id="payment_method" name="payment_method" required>
                        <option value="">Select Payment Method</option>
                        <option value="cod" <?= (isset($form_data['payment_method']) && $form_data['payment_method'] == 'cod') ? 'selected' : '' ?>>Cash on Delivery</option>
                        <!-- Add other payment methods as needed -->
                        <option value="placeholder_card" <?= (isset($form_data['payment_method']) && $form_data['payment_method'] == 'placeholder_card') ? 'selected' : '' ?>>Credit/Debit Card (Placeholder)</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary">Place Order</button>
            </form>
        </section>
    </div>
</main>

<?php include $_SERVER['DOCUMENT_ROOT'] . '/BagStore_Ecommerce/includes/footer.php'; ?>
