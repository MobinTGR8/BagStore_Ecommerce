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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($product['name']) ?> - Details</title>
    <link rel="stylesheet" href="css/product_detail.css">
</head>
<body>
<main class="detail-container">
    <div class="product-info">
        <img src="<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
        <div class="details">
            <h1><?= htmlspecialchars($product['name']) ?></h1>
            <p class="desc"><?= nl2br(htmlspecialchars($product['description'])) ?></p>
            <p class="price">$<?= number_format($product['price'], 2) ?></p>
            <a class="btn" href="cart.php?add=<?= $product['id'] ?>">Add to Cart</a>
        </div>
    </div>

    <section class="reviews">
        <h2>Customer Reviews</h2>
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
        <?php endwhile; else: ?>
            <p class="no-reviews">No reviews yet.</p>
        <?php endif; ?>
    </section>

    <section class="review-form">
        <?php if (isset($_SESSION['user_id'])): ?>
        <h2>Leave a Review</h2>
        <form method="POST" action="/BagStore_Ecommerce/submit_review.php">
            <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
            <label>Rating (1-5):</label>
            <input type="number" name="rating" min="1" max="5" required>
            <textarea name="comment" placeholder="Write your comment..." required></textarea>
            <button type="submit">Submit Review</button>
        </form>
        <?php else: ?>
        <p><a href="login.php">Login</a> to leave a review.</p>
        <?php endif; ?>
    </section>
</main>
</body>
</html>
