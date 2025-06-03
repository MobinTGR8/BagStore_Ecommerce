<?php
session_start();
include 'includes/db.php';

$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // $email = $conn->real_escape_string($_POST['email']); // No longer needed with prepared statements
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    if (!$stmt) {
        // Handle prepare error, e.g., log it or set a generic error message
        $message = "An error occurred. Please try again later.";
        // error_log("Login prepare failed: " . $conn->error);
    } else {
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            // Regenerate session ID upon successful login to prevent session fixation
            session_regenerate_id(true);
            header("Location: /BagStore_Ecommerce/index.php"); // Use absolute path
            exit();
        } else {
            $message = "Invalid password!";
        }
        } else {
            $message = "No account found with that email!";
        }
        $stmt->close();
    }
}

$pageTitle = "User Login";
$pageSpecificCss = "/BagStore_Ecommerce/css/login.css?v=" . time();
?>
<?php include $_SERVER['DOCUMENT_ROOT'] . '/BagStore_Ecommerce/includes/header.php'; ?>

<main class="login-main-container">
    <div class="login-container">
        <div class="login-card">
            <h2>User Login</h2>
            <form method="POST" action="/BagStore_Ecommerce/login.php">
                <div>
                    <label for="email">Email Address:</label>
                    <input type="email" id="email" name="email" placeholder="you@example.com" required>
                </div>
                <div>
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" placeholder="Your Password" required>
                </div>
                <button type="submit">Login</button>
            </form>
            <?php if ($message): ?>
                <p class="error-message"><?= htmlspecialchars($message) ?></p>
            <?php endif; ?>
            <p class="form-switch-link">Don't have an account? <a href="/BagStore_Ecommerce/register.php">Register here</a></p>
        </div>
    </div>
</main>

<?php include $_SERVER['DOCUMENT_ROOT'] . '/BagStore_Ecommerce/includes/footer.php'; ?>
