<?php
session_start();
include 'includes/db.php';

$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password_input = $_POST['password']; // Get raw password for validation

    // Basic server-side validation (can be more extensive)
    if (empty($name) || empty($email) || empty($password_input)) {
        $message = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Invalid email format.";
    } elseif (strlen($password_input) < 6) { // Example: minimum password length
        $message = "Password must be at least 6 characters long.";
    } else {
        $password_hashed = password_hash($password_input, PASSWORD_DEFAULT);

        // Check if email already exists using prepared statement
        $stmt_check = $conn->prepare("SELECT id FROM users WHERE email = ?");
        if (!$stmt_check) {
            $message = "Error preparing email check: " . $conn->error;
        } else {
            $stmt_check->bind_param("s", $email);
            $stmt_check->execute();
            $stmt_check->store_result(); // Needed to check num_rows

            if ($stmt_check->num_rows > 0) {
                $message = "Email already registered!";
            } else {
                // Insert new user using prepared statement
                $stmt_insert = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
                if (!$stmt_insert) {
                    $message = "Error preparing insert statement: " . $conn->error;
                } else {
                    $stmt_insert->bind_param("sss", $name, $email, $password_hashed);
                    if ($stmt_insert->execute()) {
                        // Use htmlspecialchars for the link part if ever needed, but here it's fixed.
                        $message = "Registered successfully! <a href='/BagStore_Ecommerce/login.php'>Login now</a>";
                    } else {
                        $message = "Error registering user: " . $stmt_insert->error;
                    }
                    $stmt_insert->close();
                }
            }
            $stmt_check->close();
        }
    }
}

$pageTitle = "Create Account";
$pageSpecificCss = "/BagStore_Ecommerce/css/register.css?v=" . time();
?>
<?php include $_SERVER['DOCUMENT_ROOT'] . '/BagStore_Ecommerce/includes/header.php'; ?>

<main class="register-main-container">
    <div class="register-container">
        <div class="register-card">
            <h2>Create an Account</h2>
            <form method="POST" action="/BagStore_Ecommerce/register.php">
                <div>
                    <label for="name">Full Name:</label>
                    <input type="text" id="name" name="name" placeholder="Your Full Name" required>
                </div>
                <div>
                    <label for="email">Email Address:</label>
                    <input type="email" id="email" name="email" placeholder="you@example.com" required>
                </div>
                <div>
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" placeholder="Choose a Password" required>
                </div>
                <button type="submit">Register</button>
            </form>
            <?php if ($message): ?>
                <p class="message <?= strpos($message, 'Error') !== false || strpos($message, 'Email already registered') !== false ? 'error-message' : 'success-message' ?>">
                    <?= $message /* This message can contain HTML (the login link), so raw output is intended here, but be cautious. */ ?>
                </p>
            <?php endif; ?>
            <p class="form-switch-link">Already have an account? <a href="/BagStore_Ecommerce/login.php">Login here</a></p>
        </div>
    </div>
</main>

<?php include $_SERVER['DOCUMENT_ROOT'] . '/BagStore_Ecommerce/includes/footer.php'; ?>
