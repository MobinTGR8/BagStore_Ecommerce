<?php
session_start();
include 'includes/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = md5($_POST['password']);

    $sql = "SELECT * FROM admins WHERE username = '$username' AND password = '$password'";
    $result = $conn->query($sql);

    if ($result->num_rows == 1) {
        $_SESSION['admin'] = $username;
        header("Location: admin_dashboard.php");
        exit();
    } else {
        $error = "Invalid credentials!";
    }
}
?>
<?php include $_SERVER['DOCUMENT_ROOT'] . '/BagStore_Ecommerce/includes/header.php'; ?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Login</title>
    <link rel="stylesheet" href="css/admin.css?v=1.1">

</head>
<body>
    <div style="display: flex; justify-content: center; align-items: center; min-height: 80vh;">
        <div>
            <h2 style="text-align:center;">Admin Login</h2>
            <form method="POST" style="display: flex; flex-direction: column; gap: 10px; min-width: 300px;">
                <input type="text" name="username" placeholder="Admin Username" required>
                <input type="password" name="password" placeholder="Password" required>
                <button type="submit">Login</button>
            </form>
            <?php if (isset($error)) echo "<p class='error' style='text-align:center;'>$error</p>"; ?>
        </div>
    </div>
</body>
</html>
