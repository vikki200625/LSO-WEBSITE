<?php
require_once __DIR__ . '/require_login.php';
require_once __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: blooddonate.php');
    exit;
}

$csrf = $_POST['csrf'] ?? '';
if (empty($_SESSION['csrf']) || !hash_equals($_SESSION['csrf'], $csrf)) {
    header('Location: blooddonate.php?status=error');
    exit;
}

$name   = trim($_POST['name'] ?? '');
$phone  = trim($_POST['phone'] ?? '');
$addr   = trim($_POST['address'] ?? '');
$date   = trim($_POST['appointment_date'] ?? '');
$userId = $_SESSION['user_id'] ?? null;

$errors = [];
if ($name === '' || $phone === '' || $addr === '' || $date === '') {
    $errors[] = 'All fields are required.';
}
if (!preg_match('/^[0-9+\-\s()]{7,20}$/', $phone)) {
    $errors[] = 'Please enter a valid phone number.';
}
$today = new DateTime('today');
try {
    $d = new DateTime($date);
    if ($d < $today) $errors[] = 'Appointment date cannot be in the past.';
} catch (Exception $e) {
    $errors[] = 'Invalid date.';
}

if (!empty($errors)) {
    header('Location: blooddonate.php?status=error');
    exit;
}

$conn = db_connect('donationdb');
$stmt = $conn->prepare("INSERT INTO donation_appointments (user_id, name, phone, address, appointment_date) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param('issss', $userId, $name, $phone, $addr, $date);
$ok = $stmt->execute();
$stmt->close();
$conn->close();

header('Location: blooddonate.php?status=' . ($ok ? 'booked' : 'error'));
exit;