<link rel="stylesheet" href="../css/style.css">
<?php
session_start();
$user=$_SESSION['user_id'];
$id=intval($_GET['id']);
include 'navbar.php';
// Verifica se l'utente è bannato dal gruppo
$banned_check = $conn->prepare("SELECT 1 FROM group_bans WHERE group_id = ? AND user_id = ?");
$banned_check->bind_param("ii", $id, $user);
$banned_check->execute();
$banned_result = $banned_check->get_result()->num_rows;
// Verifica se l'utente è iscritto al gruppo
$ismember_query = $conn->prepare("SELECT role FROM group_users WHERE group_id = ? AND user_id = ?");
$ismember_query->bind_param("ii", $id, $user);
$ismember_query->execute();
$membership = $ismember_query->get_result()->fetch_assoc();

if (!$membership) {
  echo "<div style='padding:30px; text-align:center; font-size:18px; color:#b00;'>🚫 Non fai parte di questo gruppo. Unisciti per visualizzarne il contenuto.</div>";
  exit;
}

$role_data = $membership;

if ($banned_result) {
  echo "<div style='padding:30px; text-align:center; font-size:18px; color:red;'>⛔ Sei stato bannato da questo gruppo e non puoi più accedervi.</div>";
  exit;
}

$g=$conn->query("SELECT * FROM `groups` WHERE id=$id")->fetch_assoc();
$ismember=$conn->query("SELECT 1 FROM group_users WHERE group_id=$id AND user_id=$user")->num_rows;
$role_stmt = $conn->prepare("SELECT role FROM group_users WHERE group_id = ? AND user_id = ?");
$role_stmt->bind_param("ii", $id, $user);
$role_stmt->execute();
$role_data = $role_stmt->get_result()->fetch_assoc();
$role_stmt->close();

if ($_SERVER['REQUEST_METHOD']==='POST' && $ismember && !empty($_POST['content'])) {
  $stmt=$conn->prepare("INSERT INTO group_posts (group_id,user_id,content) VALUES (?,?,?)");
  $stmt->bind_param("iis",$id,$user,$_POST['content']); $stmt->execute();
}

$posts = $conn->query("
  SELECT gp.id AS post_id, gp.user_id, gp.content, gp.created_at, u.name, u.profile_picture
  FROM group_posts gp
  JOIN users u ON gp.user_id = u.id
  WHERE gp.group_id = $id
  ORDER BY gp.created_at DESC
");

?>



<div class="group-page">
  <h1><?php echo htmlspecialchars($g['name']); ?></h1>
  <p class="group-description"><?php echo htmlspecialchars($g['description']); ?></p>

  <?php if ($ismember): ?>
    <form method="POST">
      <textarea name="content" placeholder="Scrivi qualcosa al gruppo..."></textarea>
      <button>Posta</button>
    </form>
  <?php else: ?>
    <p><a href="join_group.php?id=<?php echo $id ?>">Entra nel gruppo</a></p>
  <?php endif; ?>

  <h2>Feed del gruppo</h2>
  <?php while($p = $posts->fetch_assoc()): ?>
  <div class="post">
    <img src="../uploads/profile_pictures/<?php echo $p['profile_picture'] ?? 'default.png'; ?>" width="30" style="border-radius:50%">
    <strong><?php echo htmlspecialchars($p['name']); ?></strong>
    <small><?php echo date("d/m H:i", strtotime($p['created_at'])); ?></small>
    <p><?php echo nl2br(htmlspecialchars($p['content'])); ?></p>

    <?php if ($ismember && ($p['user_id'] == $user || $role_data['role'] === 'admin')): ?>
      <div>
        <a href="delete_group_post.php?group_id=<?php echo $id ?>&post_id=<?php echo $p['post_id']; ?>" onclick="return confirm('Eliminare il post?')">🗑️ Elimina</a>
      </div>
    <?php endif; ?>
  </div>
<?php endwhile; ?>

</div>

