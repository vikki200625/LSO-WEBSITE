<?php
require_once __DIR__ . '/config.php';
$hasUser  = !empty($_SESSION['user_id']);
$hasRole  = (($_SESSION['role'] ?? '') === 'admin');
$legacy   = !empty($_SESSION['admin_logged_in']);

if (!$hasUser || (!$hasRole && !$legacy)) {
    header('Location: login.php');
    exit;
}