<?php
include 'includes/db.php';

session_start();
?>
<?php include $_SERVER['DOCUMENT_ROOT'] . '/BagStore_Ecommerce/includes/header.php'; ?>


<!DOCTYPE html>
<html>
<head>
    <title>Products</title>
    <link rel="stylesheet" href="css/products.css">
</head>
<body>
    <h1 class="page-title">Available Bags</h1>
    <style>
        .page-title {
            font-family: 'Montserrat';
            font-size: 2.5rem;
            color: white;
            text-align: center;
            margin: 40px 0 30px 0;
            letter-spacing: 1px;
            font-weight: 700;
        }
    </style>
    <div class="product-list">
        <?php
        $sql = "SELECT * FROM products";
        $result = $conn->query($sql);

        while ($row = $result->fetch_assoc()) {
            echo '<div class="product">';
            echo '<img src="' . $row['image'] . '" width="200"><br>';
            echo '<h3>' . $row['name'] . '</h3>';
            echo '<p>Price: $' . $row['price'] . '</p>';
            echo '<a href="product_detail.php?id=' . $row['id'] . '">View Details</a>';
            echo '</div>';
        }
        ?>
    </div>
</body>
</html>
