<?php
require_once __DIR__ . '/require_admin.php';  // makes sure only admins reach this file

$db  = db_connect('donationdb');
$errors = [];

/* ─────────────────────────  HANDLE ADD  ───────────────────────── */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_article'])) {

    $title   = trim($_POST['title']   ?? '');
    $content = trim($_POST['content'] ?? '');
    $author  = trim($_POST['author']  ?? 'Admin');
    $image_path = null;

    if ($title === '' || $content === '') {
        $errors[] = 'Title and content are required.';
    }

    /* image upload */
    if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {

        if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'Upload error.';
        } else {

            $max = 2 * 1024 * 1024;                          // 2 MB
            if ($_FILES['image']['size'] > $max) {
                $errors[] = 'Image must be smaller than 2 MB.';
            }

            $mime = mime_content_type($_FILES['image']['tmp_name']);
            $allowed = ['image/jpeg'=>'jpg','image/png'=>'png','image/gif'=>'gif'];
            if (!isset($allowed[$mime])) {
                $errors[] = 'Only JPG, PNG, GIF files allowed.';
            }

            if (!$errors) {
                $ext   = $allowed[$mime];
                $name  = bin2hex(random_bytes(16)) . ".$ext";
                $dir   = __DIR__ . '/uploads/';
                if (!is_dir($dir)) mkdir($dir, 0755, true);
                $dest  = $dir . $name;

                if (move_uploaded_file($_FILES['image']['tmp_name'], $dest)) {
                    $image_path = 'uploads/' . $name;        // store relative path
                } else {
                    $errors[] = 'Failed to move uploaded file.';
                }
            }
        }
    }

    if (!$errors) {
        $stmt = $db->prepare(
            'INSERT INTO articles (title, content, image_url, author) VALUES (?,?,?,?)'
        );
        $stmt->bind_param('ssss', $title, $content, $image_path, $author);
        if (!$stmt->execute()) $errors[] = 'DB insert failed: '.$stmt->error;
        $stmt->close();
        if (!$errors) {
            header('Location: admin_articles.php?added=1'); exit;
        }
    }
}

/* ─────────────────────────  HANDLE DELETE  ───────────────────── */
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    if ($id > 0) {
        /* fetch image path first to delete file */
        $img = null;
        $q   = $db->prepare('SELECT image_url FROM articles WHERE id=? LIMIT 1');
        $q->bind_param('i',$id); $q->execute(); $q->bind_result($img); $q->fetch(); $q->close();

        $stmt = $db->prepare('DELETE FROM articles WHERE id=?');
        $stmt->bind_param('i',$id);
        if (!$stmt->execute()) $errors[]='Delete failed: '.$stmt->error;
        $stmt->close();

        if (!$errors && $img) {
            $file = __DIR__ . '/' . $img;
            if (is_file($file)) @unlink($file);
        }
        if (!$errors) { header('Location: admin_articles.php?deleted=1'); exit; }
    } else {
        $errors[]='Invalid ID.';
    }
}

/* ─────────────────────────  FETCH LIST  ───────────────────────── */
$articles = [];
$res = $db->query('SELECT id,title,author,created_at FROM articles ORDER BY created_at DESC');
if ($res) { $articles = $res->fetch_all(MYSQLI_ASSOC); $res->free(); }
$db->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Admin – Articles</title>
</head>
<body>
<h1>Manage Articles</h1>

<?php if (isset($_GET['added']))   echo '<p style="color:green">Article added.</p>'; ?>
<?php if (isset($_GET['deleted'])) echo '<p style="color:green">Article deleted.</p>'; ?>

<?php if ($errors): ?>
  <ul style="color:red">
    <?php foreach ($errors as $e) echo '<li>'.htmlspecialchars($e).'</li>'; ?>
  </ul>
<?php endif; ?>

<h2>Add New Article</h2>
<form action="admin_articles.php" method="post" enctype="multipart/form-data">
  <input type="text"  name="title"   placeholder="Title" required><br><br>
  <textarea name="content" rows="6" cols="60" placeholder="Content" required></textarea><br><br>
  <input type="file" name="image" accept="image/*"><br><br>
  <input type="text"  name="author"  placeholder="Author (optional)"><br><br>
  <button type="submit" name="add_article">Add Article</button>
</form>

<h2>Existing Articles</h2>
<?php if (!$articles): ?>
  <p>No articles yet.</p>
<?php else: ?>
<table border="1" cellpadding="6">
  <thead>
    <tr><th>ID</th><th>Title</th><th>Author</th><th>Created</th><th>Action</th></tr>
  </thead>
  <tbody>
    <?php foreach ($articles as $a): ?>
      <tr>
        <td><?= $a['id'] ?></td>
        <td><?= htmlspecialchars($a['title']) ?></td>
        <td><?= htmlspecialchars($a['author']) ?></td>
        <td><?= htmlspecialchars($a['created_at']) ?></td>
        <td>
          <a href="admin_articles.php?delete=<?= $a['id'] ?>" 
             onclick="return confirm('Delete this article?')">Delete</a>
        </td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>
<?php endif; ?>

<p><a href="admin_dashboard.php">Back to Dashboard</a> | 
   <a href="admin_logout.php">Logout</a></p>
</body>
</html>