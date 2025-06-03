<?php
session_start();
include $_SERVER['DOCUMENT_ROOT'] . '/BagStore_Ecommerce/includes/db.php';

$message_sent = false;
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $message_content = trim($_POST['message'] ?? ''); // Renamed to avoid conflict with $message variable for status

    if ($name === '' || $email === '' || $message_content === '') {
        $error = 'Please fill in all fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        // Save to messages table
        $stmt = $conn->prepare("INSERT INTO messages (name, email, message) VALUES (?, ?, ?)");
        // Bind the original $message_content (renamed from $message)
        $stmt->bind_param("sss", $name, $email, $message_content);

        if ($stmt->execute()) {
            $message_sent = true;
        } else {
            $error = "Database error: " . $conn->error;
        }
        $stmt->close();
    }
}

$pageTitle = "Contact Us";
$pageSpecificCss = "/BagStore_Ecommerce/css/contact.css?v=" . time();
?>
<?php include $_SERVER['DOCUMENT_ROOT'] . '/BagStore_Ecommerce/includes/header.php'; ?>

<main class="contact-main-container">
    <div class="contact-container">
        <h2>Contact Us</h2>

        <?php if ($message_sent): ?>
            <p class="form-message success-message">✅ Thank you for contacting us! Your message has been received.</p>
        <?php else: ?>
            <?php if ($error): ?>
                <p class="form-message error-message"><?= htmlspecialchars($error) ?></p>
            <?php endif; ?>

            <form method="POST" action="/BagStore_Ecommerce/contact.php">
                <div>
                    <label for="name">Name:</label>
                    <input type="text" id="name" name="name" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required>
                </div>
                <div>
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                </div>
                <div>
                    <label for="message">Message:</label>
                    <textarea id="message" name="message" rows="5" required><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>
                </div>
                <button type="submit">Send Message</button>
            </form>
        <?php endif; ?>
    </div>
</main>

<?php include $_SERVER['DOCUMENT_ROOT'] . '/BagStore_Ecommerce/includes/footer.php'; ?>
