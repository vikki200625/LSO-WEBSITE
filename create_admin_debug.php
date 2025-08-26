<?php
require_once __DIR__ . '/config.php';

$username = 'lack_of_dopamine';
$email    = 'botb07030@gmail.com';
$password = 'admin123';
$name     = 'Admin';
$role     = 'admin';

try {
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $conn = db_connect('userdb');

    $check = $conn->prepare('SELECT id FROM users WHERE username = ? OR email = ? LIMIT 1');
    $check->bind_param('ss', $username, $email);
    $check->execute();
    $res = $check->get_result();

    if ($res && $res->num_rows > 0) {
        $row = $res->fetch_assoc();
        $uid = (int)$row['id'];
        echo "User already exists (id={$uid}). Updating to admin...<br>";

        $upd = $conn->prepare('UPDATE users SET role = ?, password = ?, name = ? WHERE id = ?');
        $upd->bind_param('sssi', $role, $hash, $name, $uid);
        if ($upd->execute()) {
            echo "Updated existing user to admin (id={$uid}).<br>";
        } else {
            echo "Update error: " . htmlspecialchars($upd->error) . "<br>";
        }
        $upd->close();
    } else {
        $ins = $conn->prepare('INSERT INTO users (name, username, email, password, role) VALUES (?, ?, ?, ?, ?)');
        $ins->bind_param('sssss', $name, $username, $email, $hash, $role);
        if ($ins->execute()) {
            echo "Admin user created. ID: " . $ins->insert_id . "<br>";
        } else {
            echo "Insert error: " . htmlspecialchars($ins->error) . "<br>";
        }
        $ins->close();
    }

    $check->close();
    $conn->close();
} catch (Exception $e) {
    echo "Exception: " . htmlspecialchars($e->getMessage());
}