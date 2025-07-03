<?php
session_start();
$user=$_SESSION['user_id'];
$id=intval($_GET['id']);
$conn = new mysqli('localhost', 'root', '', 'sul_balcone');

$g=$conn->query("SELECT * FROM groups WHERE id=$id")->fetch_assoc();
$ismember=$conn->query("SELECT 1 FROM group_users WHERE group_id=$id AND user_id=$user")->num_rows;

if ($_SERVER['REQUEST_METHOD']==='POST' && $ismember && !empty($_POST['content'])) {
  $stmt=$conn->prepare("INSERT INTO group_posts (group_id,user_id,content) VALUES (?,?,?)");
  $stmt->bind_param("iis",$id,$user,$_POST['content']); $stmt->execute();
}

$posts=$conn->query("
  SELECT gp.content, gp.created_at, u.name, u.profile_picture
  FROM group_posts gp
  JOIN users u ON gp.user_id=u.id
  WHERE gp.group_id=$id
  ORDER BY gp.created_at DESC
");
?>
<?php include 'navbar.php'; ?>

<link rel="stylesheet" href="../css/groups.css">

<h1><?php echo htmlspecialchars($g['name']); ?></h1>
<p><?php echo htmlspecialchars($g['description']); ?></p>

<?php if ($ismember): ?>
  <form method="POST">
    <textarea name="content" placeholder="Scrivi qualcosa al gruppo..."></textarea>
    <button>Posta</button>
  </form>
<?php else: ?>
  <p><a href="join_group.php?id=<?php echo $id ?>">Entra nel gruppo</a></p>
<?php endif; ?>

<h2>Feed del gruppo</h2>
<?php while($p=$posts->fetch_assoc()): ?>
  <div class="post">
    <img src="../uploads/profile_pictures/<?php echo $p['profile_picture'] ?? 'default.png'; ?>" width="30" style="border-radius:50%">
    <strong><?php echo htmlspecialchars($p['name']); ?></strong>
    <small><?php echo date("d/m H:i",strtotime($p['created_at'])); ?></small>
    <p><?php echo nl2br(htmlspecialchars($p['content'])); ?></p>
  </div>
<?php endwhile; ?>
