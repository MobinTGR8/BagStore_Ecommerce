<?php
session_start();

$message_sent = false;
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if ($name === '' || $email === '' || $message === '') {
        $error = 'Please fill in all fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        // For demo: pretend to send message, in real app send email or save in DB
        $message_sent = true;
    }
}
?>

<?php include $_SERVER['DOCUMENT_ROOT'] . '/BagStore_Ecommerce/includes/header.php'; ?>


<div style="max-width:600px; margin:40px auto; padding:20px; border:1px solid #ccc; border-radius:8px;">
    <h2>Contact Us</h2>

    <?php if ($message_sent): ?>
        <p style="color:green;">Thank you for contacting us! We will get back to you shortly.</p>
    <?php else: ?>
        <?php if ($error): ?>
            <p style="color:red;"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>
        
        <form method="post" action="contact.php">
            <label for="name">Name:</label><br>
            <input type="text" id="name" name="name" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required style="width:100%; padding:8px;"><br><br>
            
            <label for="email">Email:</label><br>
            <input type="email" id="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required style="width:100%; padding:8px;"><br><br>
            
            <label for="message">Message:</label><br>
            <textarea id="message" name="message" rows="5" required style="width:100%; padding:8px;"><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea><br><br>
            
            <button type="submit" style="background:#0a6; color:#fff; border:none; padding:12px 20px; border-radius:6px; cursor:pointer;">Send Message</button>
        </form>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
