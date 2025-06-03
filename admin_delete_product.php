<?php
session_start();
include $_SERVER['DOCUMENT_ROOT'] . '/BagStore_Ecommerce/includes/db.php';

if (!isset($_SESSION['admin_id'])) {
    $_SESSION['admin_message'] = "Error: Unauthorized access."; // Or redirect to login
    header("Location: /BagStore_Ecommerce/admin_login.php");
    exit();
}

$product_id = intval($_GET['id'] ?? 0);

if ($product_id <= 0) {
    $_SESSION['admin_message'] = "Error: Invalid product ID for deletion.";
    header("Location: /BagStore_Ecommerce/admin_products.php");
    exit();
}

// First, retrieve the image path to delete the file later
$stmt_select = $conn->prepare("SELECT image_path FROM products WHERE id = ?");
$stmt_select->bind_param("i", $product_id);
$stmt_select->execute();
$result_select = $stmt_select->get_result();
$product = $result_select->fetch_assoc();
$stmt_select->close();

if ($product) {
    // Attempt to delete the product from the database
    $stmt_delete = $conn->prepare("DELETE FROM products WHERE id = ?");
    $stmt_delete->bind_param("i", $product_id);

    if ($stmt_delete->execute()) {
        // If DB deletion is successful, try to delete the image file
        if (!empty($product['image_path'])) {
            $image_file_path = $_SERVER['DOCUMENT_ROOT'] . "/BagStore_Ecommerce/images/" . $product['image_path'];
            if (file_exists($image_file_path)) {
                if (unlink($image_file_path)) {
                    // Image file deleted successfully
                    $_SESSION['admin_message'] = "Product (ID: $product_id) and its image deleted successfully.";
                } else {
                    // Image file deletion failed
                    $_SESSION['admin_message'] = "Product (ID: $product_id) deleted from database, but failed to delete image file. Please check file permissions.";
                }
            } else {
                // Image file not found, but product deleted
                 $_SESSION['admin_message'] = "Product (ID: $product_id) deleted successfully. Image file not found on server.";
            }
        } else {
            // No image path associated, product deleted
            $_SESSION['admin_message'] = "Product (ID: $product_id) deleted successfully (no image associated).";
        }
    } else {
        $_SESSION['admin_message'] = "Error: Could not delete product (ID: $product_id). " . $stmt_delete->error;
    }
    $stmt_delete->close();
} else {
    $_SESSION['admin_message'] = "Error: Product (ID: $product_id) not found for deletion.";
}

header("Location: /BagStore_Ecommerce/admin_products.php");
exit();
?>
