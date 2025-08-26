<?php
require_once __DIR__ . '/require_admin.php';
require_once __DIR__ . '/config.php';

$conn = db_connect('userdb');   // table must be in donationdb


if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
$csrf = $_SESSION['csrf'];

$errors = [];
$success_message = '';

/* ---------------- delete ---------------- */
if (isset($_GET['delete']) && hash_equals($csrf, $_GET['token'] ?? '')) {
    $id = (int)$_GET['delete'];

    $p = null;
    $sel = $conn->prepare("SELECT image_path FROM donation_content WHERE id=?");
    $sel->bind_param('i',$id); $sel->execute(); $sel->bind_result($p); $sel->fetch(); $sel->close();

    $del = $conn->prepare("DELETE FROM donation_content WHERE id=?");
    $del->bind_param('i',$id);
    if ($del->execute()) {
        if ($p && file_exists(__DIR__.'/'.$p)) @unlink(__DIR__.'/'.$p);
        $success_message = 'Content deleted.';
    } else $errors[] = 'Delete failed.';
    $del->close();
}

/* ---------------- add ------------------- */
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['add_content'])
    && hash_equals($csrf, $_POST['csrf'] ?? '')
) {
    $title   = trim($_POST['title']??'');
    $content = trim($_POST['content']??'');
    $image_path = null;

    if ($title===''||$content==='') $errors[]='Title and content required';

    if (isset($_FILES['image']) && $_FILES['image']['error']!==UPLOAD_ERR_NO_FILE) {
        $f = $_FILES['image'];
        if ($f['error']!==UPLOAD_ERR_OK) $errors[]='Upload error';
        elseif ($f['size']>2*1024*1024) $errors[]='Max 2 MB';
        else {
            $mime = mime_content_type($f['tmp_name']);
            $ok = ['image/jpeg'=>'jpg','image/png'=>'png','image/gif'=>'gif'];
            if (!isset($ok[$mime])) $errors[]='JPG/PNG/GIF only';
            else {
                $dir=__DIR__.'/uploads/donate/'; if(!is_dir($dir))mkdir($dir,0755,true);
                $file='donation_'.bin2hex(random_bytes(12)).'.'.$ok[$mime];
                if (move_uploaded_file($f['tmp_name'],$dir.$file))
                    $image_path='uploads/donate/'.$file;
                else $errors[]='Move failed';
            }
        }
    }

    if (!$errors) {
        $in=$conn->prepare("INSERT INTO donation_content (title,content,image_path) VALUES (?,?,?)");
        $in->bind_param('sss',$title,$content,$image_path);
        if ($in->execute()) $success_message='Content added!';
        else                $errors[]='Insert failed';
        $in->close();
    }
}

/* ---------------- list ------------------ */
$list=[];
$res=$conn->query("SELECT id,title,image_path,created_at FROM donation_content ORDER BY created_at DESC");
if($res) $list=$res->fetch_all(MYSQLI_ASSOC);
$conn->close();
?>
<!DOCTYPE html><html lang="en"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Admin – Donation Page Content</title></head><body>
<h1>Donation Page Content</h1>

<?php if($success_message) echo '<p style="color:green">'.htmlspecialchars($success_message).'</p>'; ?>
<?php if($errors): ?><ul style="color:red"><?php foreach($errors as $e)echo'<li>'.htmlspecialchars($e).'</li>';?></ul><?php endif;?>

<h2>Add Block</h2>
<form method="post" enctype="multipart/form-data">
<input type="hidden" name="csrf" value="<?=htmlspecialchars($csrf)?>">
<input type="text" name="title"  placeholder="Title" required><br><br>
<textarea name="content" rows="6" cols="50" placeholder="Content" required></textarea><br><br>
<input type="file" name="image" accept="image/*"><br><br>
<button name="add_content">Add</button>
</form>

<h2>Existing Blocks</h2>
<table border="1" cellpadding="6">
<thead><tr><th>ID</th><th>Image</th><th>Title</th><th>Created</th><th>Action</th></tr></thead>
<tbody>
<?php foreach($list as $row):?>
<tr>
<td><?=$row['id']?></td>
<td><?= $row['image_path'] ? '<img src="'.htmlspecialchars($row['image_path']).'" width="80">' : 'No image' ?></td>
<td><?=htmlspecialchars($row['title'])?></td>
<td><?=htmlspecialchars($row['created_at'])?></td>
<td><a href="admin_donation_content.php?delete=<?=$row['id']?>&token=<?=urlencode($csrf)?>" onclick="return confirm('Delete?')">Delete</a></td>
</tr>
<?php endforeach;?>
</tbody></table>

<p><a href="admin_dashboard.php">Dashboard</a> | <a href="admin_logout.php">Logout</a></p>
</body></html>