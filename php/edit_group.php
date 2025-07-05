<?php
session_start();
include 'navbar.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
  header("Location: index.php");
  exit;
}

$user = $_SESSION['user_id'];
$group_id = intval($_GET['id']);

// Controlla se l'utente è admin del gruppo
$admin_check = $conn->prepare("SELECT role FROM group_users WHERE group_id = ? AND user_id = ?");
$admin_check->bind_param("ii", $group_id, $user);
$admin_check->execute();
$role_data = $admin_check->get_result()->fetch_assoc();

if (!$role_data || $role_data['role'] !== 'admin') {
  echo "<p>Non hai i permessi per modificare questo gruppo.</p>";
  exit;
}

// Update info gruppo
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['name'])) {
  $new_name = $_POST['name'];
  $new_desc = $_POST['description'];
  $stmt = $conn->prepare("UPDATE groups SET name = ?, description = ? WHERE id = ?");
  $stmt->bind_param("ssi", $new_name, $new_desc, $group_id);
  $stmt->execute();
}

// Espulsione partecipante
if (isset($_GET['kick'])) {
  $uid = intval($_GET['kick']);
  if ($uid !== $user) {
    $conn->query("DELETE FROM group_users WHERE group_id = $group_id AND user_id = $uid");
  }
  header("Location: edit_group.php?id=$group_id");
  exit;
}

// Imposta come admin
if (isset($_GET['promote'])) {
  $uid = intval($_GET['promote']);
  $conn->query("UPDATE group_users SET role = 'admin' WHERE group_id = $group_id AND user_id = $uid");
  header("Location: edit_group.php?id=$group_id");
  exit;
}

// Dati gruppo
$group = $conn->query("SELECT * FROM groups WHERE id = $group_id")->fetch_assoc();

// Partecipanti
$members = $conn->query("
  SELECT u.id, u.name, u.profile_picture, gu.role
  FROM users u
  JOIN group_users gu ON u.id = gu.user_id
  WHERE gu.group_id = $group_id
");
?>

<link rel="stylesheet" href="../css/style.css">
<style>
.edit-group-container {
  max-width: 800px;
  margin: 40px auto;
  background: #fff;
  padding: 30px;
  border-radius: 16px;
  box-shadow: 0 8px 20px rgba(0,0,0,0.08);
}

.edit-group-container h2 {
  margin-bottom: 20px;
  color: #003049;
}

.edit-group-container input, textarea {
  width: 100%;
  font-size: 16px;
  padding: 10px;
  margin-bottom: 12px;
  border: 1px solid #ccc;
  border-radius: 8px;
}

.edit-group-container button {
  background-color: #f77f00;
  color: white;
  padding: 10px 18px;
  border: none;
  border-radius: 8px;
  font-size: 16px;
  cursor: pointer;
}

.edit-group-container button:hover {
  background-color: #d66a00;
}

.member-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  background: #f9f9f9;
  padding: 12px 16px;
  border-radius: 10px;
  margin-bottom: 10px;
}

.member-info {
  display: flex;
  align-items: center;
  gap: 12px;
}

.member-info img {
  width: 38px;
  height: 38px;
  border-radius: 50%;
  object-fit: cover;
  border: 2px solid #fcbf49;
}

.member-actions a {
  margin-left: 10px;
  color: white;
  background-color: #f77f00;
  padding: 6px 10px;
  border-radius: 6px;
  text-decoration: none;
  font-size: 14px;
}

.member-actions a:hover {
  background-color: #d66a00;
}
</style>

<div class="edit-group-container">
  <h2>Modifica gruppo: "<?php echo htmlspecialchars($group['name']); ?>"</h2>

  <form method="POST">
    <input type="text" name="name" value="<?php echo htmlspecialchars($group['name']); ?>" required>
    <textarea name="description"><?php echo htmlspecialchars($group['description']); ?></textarea>
    <button type="submit">Salva modifiche</button>
  </form>
<a href="bans.php?group_id=<?php echo $group_id; ?>" style="display: inline-block; background: #e63946; color: white; padding: 8px 14px; border-radius: 8px; text-decoration: none; font-weight: bold; margin-bottom: 20px;">
  ⛔ Gestisci utenti bannati
</a>

  <h2>Partecipanti</h2>
  <?php while($m = $members->fetch_assoc()): ?>
    <div class="member-row">
      <div class="member-info">
        <img src="../uploads/profile_pictures/<?php echo $m['profile_picture'] ?? 'default.jpg'; ?>">
        <strong><?php echo htmlspecialchars($m['name']); ?></strong>
        <small style="color:#777;">(<?php echo $m['role']; ?>)</small>
      </div>
      <div class="member-actions">
        <?php if ($m['id'] != $user): ?>
            <a href="kick_ban.php?group_id=<?php echo $group_id ?>&user_id=<?php echo $m['id']; ?>&action=kick" onclick="return confirm('Espellere temporaneamente questo utente?')">🚪 Kick</a>
            <a href="kick_ban.php?group_id=<?php echo $group_id ?>&user_id=<?php echo $m['id']; ?>&action=ban" onclick="return confirm('Bannare permanentemente questo utente dal gruppo?')">⛔ Ban</a>
          <?php if ($m['role'] !== 'admin'): ?>
            <a href="?id=<?php echo $group_id ?>&promote=<?php echo $m['id']; ?>">🛡️ Admin</a>
          <?php endif; ?>
        <?php endif; ?>
      </div>
    </div>
  <?php endwhile; ?>
</div>
