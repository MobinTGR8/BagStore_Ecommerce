<?php
include 'includes/db.php';
session_start();

if (!isset($_GET['id'])) {
    echo "Product ID missing!";
    exit();
}

$id = intval($_GET['id']);
$sql = "SELECT * FROM products WHERE id = $id";
$result = $conn->query($sql);

if ($result->num_rows == 0) {
    echo "Product not found.";
    exit();
}

$product = $result->fetch_assoc();
?>
<?php include $_SERVER['DOCUMENT_ROOT'] . '/BagStore_Ecommerce/includes/header.php'; ?>

<!DOCTYPE html>
<html>
<head>
    <title><?= $product['name'] ?> - Details</title>
    <link rel="stylesheet" href="css/product_detail.css">
</head>
<body>
    <h1><?= $product['name'] ?></h1>
    <img src="<?= $product['image'] ?>" width="300"><br><br>
    <p><strong>Description:</strong> <?= $product['description'] ?></p>
    <p><strong>Price:</strong> $<?= $product['price'] ?></p>
    <a href="cart.php?add=<?= $product['id'] ?>">Add to Cart</a>

    <?php if (isset($_SESSION['user_id'])): ?>
    <form method="POST" action="/BagStore_Ecommerce/submit_review.php">

        <h3>Leave a Review</h3>
        <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
        <label>Rating (1-5):</label>
        <input type="number" name="rating" min="1" max="5" required><br>
        <textarea name="comment" placeholder="Write your comment..." required></textarea><br>
        <button type="submit">Submit Review</button>
    </form>
<?php else: ?>
    <p><a href="login.php">Login</a> to leave a review.</p>
<?php endif; ?>
<h3>Customer Reviews</h3>

<?php
$review_result = $conn->query("SELECT r.*, u.name FROM reviews r 
    JOIN users u ON r.user_id = u.id 
    WHERE r.product_id = " . $product['id'] . " ORDER BY r.created_at DESC");

if ($review_result->num_rows > 0):
    while ($review = $review_result->fetch_assoc()):
?>
    <div class="review">
        <strong><?= htmlspecialchars($review['name']) ?></strong> 
        (<?= $review['rating'] ?>/5 stars)<br>
        <small><?= $review['created_at'] ?></small>
        <p><?= nl2br(htmlspecialchars($review['comment'])) ?></p>
    </div>
    <hr>
<?php endwhile; else: ?>
    <p>No reviews yet.</p>
<?php endif; ?>

</body>
</html>
