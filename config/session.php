<?php
// config/session.php

// 1. Set cookie parameters for security
session_set_cookie_params([
    'lifetime' => 86400, // 24 hours
    'path' => '/',
    'domain' => '',
    'secure' => false,   // Set to true if using HTTPS
    'httponly' => true,  // Prevents JS from accessing session cookies
    'samesite' => 'Lax'
]);

// 2. Start the session if it hasn't been started already
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>