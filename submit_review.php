<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/BagStore_Ecommerce/includes/db.php'; // Corrected path

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
    $product_id = intval($_POST['product_id'] ?? 0);
    $user_id = intval($_SESSION['user_id']);
    $rating = intval($_POST['rating'] ?? 0);
    $comment = trim($_POST['comment'] ?? '');

    // Basic validation
    if ($product_id <= 0) {
        // Set session error message and redirect
        $_SESSION['review_message'] = ['type' => 'error', 'text' => 'Invalid product specified.'];
        header("Location: " . ($_SERVER['HTTP_REFERER'] ?? "/BagStore_Ecommerce/products.php"));
        exit;
    }
    if ($rating < 1 || $rating > 5) {
        $_SESSION['review_message'] = ['type' => 'error', 'text' => 'Rating must be between 1 and 5.'];
        header("Location: /BagStore_Ecommerce/product_detail.php?id=" . $product_id);
        exit;
    }
    if (empty($comment)) {
        $_SESSION['review_message'] = ['type' => 'error', 'text' => 'Comment cannot be empty.'];
        header("Location: /BagStore_Ecommerce/product_detail.php?id=" . $product_id);
        exit;
    }

    // Prevent duplicate reviews - using prepared statement
    $stmt_check = $conn->prepare("SELECT id FROM reviews WHERE product_id = ? AND user_id = ?");
    if (!$stmt_check) {
        error_log("Prepare failed for review check: " . $conn->error);
        $_SESSION['review_message'] = ['type' => 'error', 'text' => 'An error occurred. Please try again. (Code: R01)'];
        header("Location: /BagStore_Ecommerce/product_detail.php?id=" . $product_id);
        exit;
    }
    $stmt_check->bind_param("ii", $product_id, $user_id);
    $stmt_check->execute();
    $stmt_check->store_result();

    if ($stmt_check->num_rows > 0) {
        $stmt_check->close();
        $_SESSION['review_message'] = ['type' => 'error', 'text' => 'You have already reviewed this product.'];
        header("Location: /BagStore_Ecommerce/product_detail.php?id=" . $product_id);
        exit;
    }
    $stmt_check->close();

    // Insert review - using prepared statement
    // Assuming 'reviews' table has 'created_at' with DEFAULT CURRENT_TIMESTAMP or handled by DB
    $stmt_insert = $conn->prepare("INSERT INTO reviews (product_id, user_id, rating, comment) VALUES (?, ?, ?, ?)");
    if (!$stmt_insert) {
        error_log("Prepare failed for review insert: " . $conn->error);
        $_SESSION['review_message'] = ['type' => 'error', 'text' => 'An error occurred. Please try again. (Code: R02)'];
        header("Location: /BagStore_Ecommerce/product_detail.php?id=" . $product_id);
        exit;
    }
    $stmt_insert->bind_param("iiis", $product_id, $user_id, $rating, $comment);

    if ($stmt_insert->execute()) {
        $stmt_insert->close();
        $_SESSION['review_message'] = ['type' => 'success', 'text' => 'Your review has been submitted successfully!'];
        header("Location: /BagStore_Ecommerce/product_detail.php?id=" . $product_id);
        exit;
    } else {
        error_log("Execute failed for review insert: " . $stmt_insert->error);
        $_SESSION['review_message'] = ['type' => 'error', 'text' => 'Could not submit your review due to a server error. Please try again. (Code: R03)'];
        header("Location: /BagStore_Ecommerce/product_detail.php?id=" . $product_id);
        exit;
    }
} else {
    // Redirect if not POST or user not logged in
    // Check HTTP_REFERER to redirect back, otherwise to index.
    $redirect_url = "/BagStore_Ecommerce/index.php";
    if(isset($_SERVER['HTTP_REFERER'])){
        // Basic check to prevent redirecting to external sites if referer is manipulated
        if(parse_url($_SERVER['HTTP_REFERER'], PHP_URL_HOST) == $_SERVER['SERVER_NAME']){
            $redirect_url = $_SERVER['HTTP_REFERER'];
        }
    }
    header("Location: " . $redirect_url);
    exit;
}
?>
