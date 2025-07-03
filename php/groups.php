<?php
session_start();
if (!isset($_SESSION['user_id'])) header("Location: index.php");

$conn = new mysqli('localhost', 'root', '', 'sul_balcone');
$user = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['name'])) {
    $name = $_POST['name'];
    $desc = $_POST['description'] ?? '';
    $is_private = isset($_POST['is_private']) ? 1 : 0;
    $password = $is_private && !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_DEFAULT) : null;

    $stmt = $conn->prepare("INSERT INTO groups (name, description, creator_id, is_private, password) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssiis", $name, $desc, $user, $is_private, $password);
    $stmt->execute();

    $gid = $conn->insert_id;
    $conn->query("INSERT INTO group_users (group_id, user_id, role) VALUES ($gid, $user, 'admin')");
}

$all = $conn->query("SELECT * FROM groups");
$joined = $conn->query("
  SELECT g.* FROM groups g
  JOIN group_users gu ON g.id=gu.group_id
  WHERE gu.user_id = $user
");
?>
<?php include 'navbar.php'; ?>
<link rel="stylesheet" href="../css/groups.css">

<h1>Gruppi</h1>
<h2>Crea nuovo gruppo:</h2>
<form method="POST">
  <input name="name" placeholder="Nome gruppo" required><br>
  <textarea name="description" placeholder="Descrizione"></textarea><br>

  <label>
    <input type="checkbox" name="is_private" id="is_private" onchange="togglePassword()"> Gruppo privato
  </label><br>

  <input type="password" name="password" id="password" placeholder="Password del gruppo" style="display:none"><br>

  <button>Crea</button>
</form>

<script>
function togglePassword() {
  const pwd = document.getElementById('password');
  pwd.style.display = document.getElementById('is_private').checked ? 'block' : 'none';
}
</script>


<h2>I tuoi gruppi</h2>
<?php while($g=$joined->fetch_assoc()):
  echo "<p><a href='group.php?id={$g['id']}'>".htmlspecialchars($g['name'])."</a></p>";
endwhile; ?>

<h2>Gruppi disponibili</h2>
<?php while($g=$all->fetch_assoc()):
echo "<p>" . htmlspecialchars($g['name']);
if ($g['is_private']) echo " 🔒";
echo " <a href='join_group.php?id={$g['id']}'>Entra</a></p>";

endwhile; ?>
