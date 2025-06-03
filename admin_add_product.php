<?php
session_start();
include $_SERVER['DOCUMENT_ROOT'] . '/BagStore_Ecommerce/includes/db.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: /BagStore_Ecommerce/admin_login.php");
    exit();
}

$name = '';
$description = '';
$price = '';
$stock_quantity = '';
$category_id = ''; // Assuming you might have categories later
$errors = [];
$success_message = '';

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

    // Image Upload Handling
    $image_path = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $target_dir = $_SERVER['DOCUMENT_ROOT'] . "/BagStore_Ecommerce/images/";
        // Ensure target directory exists and is writable
        if (!is_dir($target_dir)) mkdir($target_dir, 0755, true);

        $image_name = uniqid() . "-" . basename($_FILES["image"]["name"]);
        $target_file = $target_dir . $image_name;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Check if image file is an actual image or fake image
        $check = getimagesize($_FILES["image"]["tmp_name"]);
        if($check === false) {
            $errors[] = "File is not an image.";
        }
        // Check file size (e.g., 5MB limit)
        if ($_FILES["image"]["size"] > 5000000) {
            $errors[] = "Sorry, your file is too large (max 5MB).";
        }
        // Allow certain file formats
        if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif" ) {
            $errors[] = "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
        }

        if (empty($errors) && move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            $image_path = $image_name; // Save only the filename/relative path
        } else {
            if(empty($errors)) $errors[] = "Sorry, there was an error uploading your file.";
        }
    } else {
        // $errors[] = "Product image is required."; // Make image optional or required based on needs
    }

    if (empty($errors)) {
        $stmt = $conn->prepare("INSERT INTO products (name, description, price, stock_quantity, image_path) VALUES (?, ?, ?, ?, ?)");
        // Assuming category_id will be added later: $stmt->bind_param("ssdisi", $name, $description, $price, $stock_quantity, $image_path, $category_id);
        $stmt->bind_param("ssdis", $name, $description, $price, $stock_quantity, $image_path);

        if ($stmt->execute()) {
            $_SESSION['admin_message'] = "Product added successfully!";
            header("Location: /BagStore_Ecommerce/admin_products.php");
            exit();
        } else {
            $errors[] = "Database error: " . $stmt->error;
        }
        $stmt->close();
    }
}

$pageTitle = "Add New Product";
$pageSpecificCss = "/BagStore_Ecommerce/css/admin.css?v=" . time();
$body_class = "admin-page";
?>
<?php include $_SERVER['DOCUMENT_ROOT'] . '/BagStore_Ecommerce/includes/header.php'; ?>

<main class="admin-main-container">
    <div class="container admin-form-container"> <!-- Specific container for forms -->
        <h1>Add New Product</h1>

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

        <form action="/BagStore_Ecommerce/admin_add_product.php" method="POST" enctype="multipart/form-data" class="admin-form">
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
                <label for="image">Product Image:</label>
                <input type="file" id="image" name="image" accept="image/*">
                <small>Max 5MB. Allowed types: JPG, JPEG, PNG, GIF.</small>
            </div>

            <!-- Example for Category (Future Use)
            <div class="form-group">
                <label for="category_id">Category:</label>
                <select id="category_id" name="category_id">
                    <option value="">Select Category</option>
                    <?php
                    // $categories_result = $conn->query("SELECT * FROM categories ORDER BY name");
                    // while ($category = $categories_result->fetch_assoc()) {
                    //    echo "<option value=\"".htmlspecialchars($category['id'])."\">".htmlspecialchars($category['name'])."</option>";
                    // }
                    ?>
                </select>
            </div>
            -->

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Add Product</button>
                <a href="/BagStore_Ecommerce/admin_products.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</main>

<?php include $_SERVER['DOCUMENT_ROOT'] . '/BagStore_Ecommerce/includes/footer.php'; ?>
