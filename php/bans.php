<?php
session_start();
include 'navbar.php';
$group_id = intval($_GET['group_id']);
$admin = $_SESSION['user_id'];

$check = $conn->prepare("SELECT role FROM group_users WHERE group_id=? AND user_id=?");
$check->bind_param("ii", $group_id, $admin);
$check->execute();
$role = $check->get_result()->fetch_assoc();

if (!$role || $role['role'] !== 'admin') exit;

$bans = $conn->query("
  SELECT gb.user_id, u.name, u.profile_picture, gb.banned_at
  FROM group_bans gb
  JOIN users u ON u.id = gb.user_id
  WHERE gb.group_id = $group_id
");
?>
<link rel="stylesheet" href="../css/style.css">

<h2>Utenti Bannati</h2>
<?php while($ban = $bans->fetch_assoc()): ?>
  <div style="margin-bottom: 15px;">
    <img src="../uploads/profile_pictures/<?php echo $ban['profile_picture'] ?? 'default.jpg'; ?>" width="30" style="border-radius:50%">
    <strong><?php echo htmlspecialchars($ban['name']); ?></strong>
    <small>(<?php echo date("d/m/Y", strtotime($ban['banned_at'])); ?>)</small>
    <a href="unban.php?group_id=<?php echo $group_id ?>&user_id=<?php echo $ban['user_id']; ?>" style="color:red; margin-left:10px;">❌ Rimuovi ban</a>
  </div>
<?php endwhile; ?>
