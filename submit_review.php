<?php
session_start();
require_once 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
    $product_id = (int) $_POST['product_id'];
    $user_id = (int) $_SESSION['user_id'];
    $rating = (int) $_POST['rating'];
    $comment = $conn->real_escape_string($_POST['comment']);

    // Prevent duplicate reviews (optional)
    $check = $conn->query("SELECT * FROM reviews WHERE product_id = $product_id AND user_id = $user_id");
    if ($check->num_rows > 0) {
        echo "You have already reviewed this product.";
        exit;
    }

    $sql = "INSERT INTO reviews (product_id, user_id, rating, comment) 
            VALUES ($product_id, $user_id, $rating, '$comment')";
    if ($conn->query($sql)) {
        header("Location: product_detail.php?id=" . $product_id);
        exit;
    } else {
        echo "Error: " . $conn->error;
    }
}
?>
