<?php
session_start();
include $_SERVER['DOCUMENT_ROOT'] . '/BagStore_Ecommerce/includes/db.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: /BagStore_Ecommerce/admin_login.php");
    exit();
}

$product_id = intval($_GET['id'] ?? 0);
if ($product_id <= 0) {
    $_SESSION['admin_message'] = "Error: Invalid product ID.";
    header("Location: /BagStore_Ecommerce/admin_products.php");
    exit();
}

// Fetch product data
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();
$stmt->close();

if (!$product) {
    $_SESSION['admin_message'] = "Error: Product not found.";
    header("Location: /BagStore_Ecommerce/admin_products.php");
    exit();
}

// Initialize variables with product data
$name = $product['name'];
$description = $product['description'];
$price = $product['price'];
$stock_quantity = $product['stock_quantity'];
$current_image_path = $product['image_path']; // Keep track of the current image

$errors = [];
// No $success_message here, redirect with session message

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = trim($_POST['price']);
    $stock_quantity = trim($_POST['stock_quantity']);
    // $category_id = trim($_POST['category_id']); // For future use

    // Basic Validation
    if (empty($name)) $errors[] = "Product name is required.";
    if (empty($description)) $errors[] = "Description is required.";
    if (!is_numeric($price) || $price < 0) $errors[] = "Valid price is required.";
    if (!is_numeric($stock_quantity) || $stock_quantity < 0 || floor($stock_quantity) != $stock_quantity) $errors[] = "Valid stock quantity is required.";

    $new_image_path_db = $current_image_path; // By default, keep the current image

    // Image Upload Handling (if a new image is provided)
    if (isset($_FILES['new_image']) && $_FILES['new_image']['error'] == 0 && $_FILES['new_image']['size'] > 0) {
        $target_dir = $_SERVER['DOCUMENT_ROOT'] . "/BagStore_Ecommerce/images/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0755, true);

        $image_name = uniqid() . "-" . basename($_FILES["new_image"]["name"]);
        $target_file = $target_dir . $image_name;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        $check = getimagesize($_FILES["new_image"]["tmp_name"]);
        if($check === false) $errors[] = "New file is not an image.";
        if ($_FILES["new_image"]["size"] > 5000000) $errors[] = "Sorry, your new file is too large (max 5MB).";
        if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif" ) {
            $errors[] = "Sorry, only JPG, JPEG, PNG & GIF files are allowed for new image.";
        }

        if (empty($errors)) {
            if (move_uploaded_file($_FILES["new_image"]["tmp_name"], $target_file)) {
                // New image uploaded successfully, update path for DB
                $new_image_path_db = $image_name;
                // Optionally, delete the old image if it's different and exists
                if ($current_image_path && $current_image_path != $new_image_path_db && file_exists($target_dir . $current_image_path)) {
                    unlink($target_dir . $current_image_path);
                }
            } else {
                $errors[] = "Sorry, there was an error uploading your new file.";
            }
        }
    }

    if (empty($errors)) {
        $stmt = $conn->prepare("UPDATE products SET name = ?, description = ?, price = ?, stock_quantity = ?, image_path = ? WHERE id = ?");
        $stmt->bind_param("ssdisi", $name, $description, $price, $stock_quantity, $new_image_path_db, $product_id);

        if ($stmt->execute()) {
            $_SESSION['admin_message'] = "Product updated successfully!";
            header("Location: /BagStore_Ecommerce/admin_products.php");
            exit();
        } else {
            $errors[] = "Database error: " . $stmt->error;
        }
        $stmt->close();
    }
}

$pageTitle = "Edit Product - " . htmlspecialchars($product['name']);
$pageSpecificCss = "/BagStore_Ecommerce/css/admin.css?v=" . time();
$body_class = "admin-page";
?>
<?php include $_SERVER['DOCUMENT_ROOT'] . '/BagStore_Ecommerce/includes/header.php'; ?>

<main class="admin-main-container">
    <div class="container admin-form-container">
        <h1>Edit Product: <?= htmlspecialchars($product['name']) ?></h1>

        <?php if (!empty($errors)): ?>
            <div class="admin-message error-message">
                <h4>Please fix the following errors:</h4>
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form action="/BagStore_Ecommerce/admin_edit_product.php?id=<?= htmlspecialchars($product_id) ?>" method="POST" enctype="multipart/form-data" class="admin-form">
            <div class="form-group">
                <label for="name">Product Name:</label>
                <input type="text" id="name" name="name" value="<?= htmlspecialchars($name) ?>" required>
            </div>
            <div class="form-group">
                <label for="description">Description:</label>
                <textarea id="description" name="description" rows="5" required><?= htmlspecialchars($description) ?></textarea>
            </div>
            <div class="form-group">
                <label for="price">Price ($):</label>
                <input type="number" id="price" name="price" step="0.01" min="0" value="<?= htmlspecialchars($price) ?>" required>
            </div>
            <div class="form-group">
                <label for="stock_quantity">Stock Quantity:</label>
                <input type="number" id="stock_quantity" name="stock_quantity" min="0" value="<?= htmlspecialchars($stock_quantity) ?>" required>
            </div>

            <div class="form-group">
                <label>Current Image:</label>
                <?php if ($current_image_path): ?>
                    <img src="/BagStore_Ecommerce/images/<?= htmlspecialchars($current_image_path) ?>" alt="Current image for <?= htmlspecialchars($name) ?>" style="max-width: 150px; max-height: 150px; object-fit: cover; border-radius: var(--border-radius-base); margin-bottom: calc(var(--spacing-unit)*0.5);">
                    <p><small>Filename: <?= htmlspecialchars($current_image_path) ?></small></p>
                <?php else: ?>
                    <p><small>No current image.</small></p>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="new_image">Upload New Image (optional):</label>
                <input type="file" id="new_image" name="new_image" accept="image/*">
                <small>Leave blank to keep current image. Max 5MB. JPG, JPEG, PNG, GIF.</small>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Update Product</button>
                <a href="/BagStore_Ecommerce/admin_products.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</main>

<?php include $_SERVER['DOCUMENT_ROOT'] . '/BagStore_Ecommerce/includes/footer.php'; ?>
