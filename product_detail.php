<?php
include 'includes/db.php';
session_start();

if (!isset($_GET['id'])) {
    echo "Product ID missing!";
    exit();
}

$id = intval($_GET['id']);

// Fetch product using prepared statement
$stmt_product = $conn->prepare("SELECT * FROM products WHERE id = ?");
if (!$stmt_product) {
    // Handle prepare error - critical
    error_log("Prepare failed for product select: " . $conn->error);
    echo "An error occurred loading the product. Please try again later.";
    exit(); // Or redirect to an error page
}
$stmt_product->bind_param("i", $id);
$stmt_product->execute();
$result_product = $stmt_product->get_result();

if ($result_product->num_rows == 0) {
    echo "Product not found."; // Consider a more user-friendly page
    exit();
}
$product = $result_product->fetch_assoc();
$stmt_product->close();

// Set page title for header.php
$pageTitle = htmlspecialchars($product['name']) . " - Details";
// Suggestion: Add a mechanism in header.php to include page-specific CSS files
// For example, by setting a variable like $pageSpecificCss = "css/product_detail.css";
$pageSpecificCss = "/BagStore_Ecommerce/css/product_detail.css";

?>
<?php include $_SERVER['DOCUMENT_ROOT'] . '/BagStore_Ecommerce/includes/header.php'; ?>

<main class="detail-container">
    <div class="product-info">
        <img src="/BagStore_Ecommerce/images/<?= htmlspecialchars($product['image_path']) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
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
        // Fetch reviews using prepared statement
        $stmt_reviews = $conn->prepare("SELECT r.rating, r.comment, r.created_at, u.name as user_name FROM reviews r JOIN users u ON r.user_id = u.id WHERE r.product_id = ? ORDER BY r.created_at DESC");
        if ($stmt_reviews) {
            $stmt_reviews->bind_param("i", $id); // Use $id (product ID) from URL
            $stmt_reviews->execute();
            $review_result = $stmt_reviews->get_result();

            if ($review_result->num_rows > 0):
                while ($review = $review_result->fetch_assoc()):
            ?>
            <div class="review">
                <strong><?= htmlspecialchars($review['user_name']) ?></strong>
                (<?= htmlspecialchars($review['rating']) ?>/5 stars)<br>
                <small><?= htmlspecialchars(date("M d, Y H:i", strtotime($review['created_at']))) ?></small>
                <p><?= nl2br(htmlspecialchars($review['comment'])) ?></p>
            </div>
            <?php
                endwhile;
            else: ?>
                <p class="no-reviews">No reviews yet for this product.</p>
            <?php
            endif;
            $stmt_reviews->close();
        } else {
            error_log("Prepare failed for reviews select: " . $conn->error);
            echo "<p class='error-message'>Could not load reviews at this time.</p>";
        }
        ?>
    </section>

    <section class="review-form">
        <?php
        // Display review submission messages
        if (isset($_SESSION['review_message'])):
            $review_msg_type = $_SESSION['review_message']['type'] ?? 'error';
            $review_msg_text = $_SESSION['review_message']['text'] ?? 'An unknown error occurred.';
        ?>
            <div class="form-message <?= $review_msg_type == 'success' ? 'success-message' : 'error-message' ?>" style="margin-bottom: var(--spacing-unit); background-color: var(--<?= $review_msg_type ?>-bg); color: var(--<?= $review_msg_type ?>-text); border: 1px solid var(--<?= $review_msg_type ?>-border); padding: var(--spacing-unit); border-radius: var(--border-radius-base);">
                <?= htmlspecialchars($review_msg_text) ?>
            </div>
        <?php
            unset($_SESSION['review_message']); // Clear message after displaying
        endif;
        ?>

        <?php if (isset($_SESSION['user_id'])): ?>
        <h2>Leave a Review</h2>
        <form method="POST" action="/BagStore_Ecommerce/submit_review.php">
            <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
            <div>
                <label for="rating">Rating (1-5):</label>
                <input type="number" id="rating" name="rating" min="1" max="5" required>
            </div>
            <div>
                <label for="comment">Comment:</label>
                <textarea id="comment" name="comment" placeholder="Write your comment..." required></textarea>
            </div>
            <button type="submit">Submit Review</button>
        </form>
        <?php else: ?>
        <p><a href="/BagStore_Ecommerce/login.php">Login</a> to leave a review.</p>
        <?php endif; ?>
    </section>
</main>

<?php include $_SERVER['DOCUMENT_ROOT'] . '/BagStore_Ecommerce/includes/footer.php'; ?>
