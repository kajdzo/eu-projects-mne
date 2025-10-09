<?php
require_once __DIR__ . '/../config/init.php';
requireLogin();

// Redirect to edit own profile
header('Location: /user-edit.php?id=' . $_SESSION['user_id']);
exit;
