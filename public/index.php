<?php
require_once __DIR__ . '/../config/init.php';

// Redirect to dashboard if logged in, otherwise to home page
if (isLoggedIn()) {
    header('Location: /dashboard');
} else {
    header('Location: /home');
}
exit;
