<?php
session_start();

// Check if it's an admin logout request
$isAdminLogout = isset($_GET['admin']) && $_GET['admin'] === 'true';

// Unset all session variables
$_SESSION = array(); // Or session_unset();

// Destroy the session
session_destroy();

// Regenerate session ID to prevent session fixation on re-login (good practice)
// session_regenerate_id(true); // Not strictly needed after destroy, but doesn't hurt if new session starts immediately

if ($isAdminLogout) {
    header("Location: /BagStore_Ecommerce/admin_login.php"); // Redirect admin to admin login
} else {
    header("Location: /BagStore_Ecommerce/login.php"); // Redirect regular user to user login
}
exit();
