<?php
session_start();
if (!isset($_SESSION['user_id'])) header("Location: index.php");

include 'navbar.php';
$user = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['name'])) {
    $name = $_POST['name'];
    $desc = $_POST['description'] ?? '';
    $is_private = isset($_POST['is_private']) ? 1 : 0;
    $password = $is_private && !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_DEFAULT) : null;

    $stmt = $conn->prepare("INSERT INTO `groups` (name, description, creator_id, is_private, password) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssiis", $name, $desc, $user, $is_private, $password);
    $stmt->execute();

    $gid = $conn->insert_id;
    $conn->query("INSERT INTO group_users (group_id, user_id, role) VALUES ($gid, $user, 'admin')");
}

$all = $conn->query("
  SELECT * FROM `groups` g
  WHERE NOT EXISTS (
    SELECT 1 FROM group_users gu
    WHERE gu.group_id = g.id AND gu.user_id = $user
  )
");

$joined = $conn->query("
  SELECT g.* FROM `groups` g
  JOIN group_users gu ON g.id=gu.group_id
  WHERE gu.user_id = $user
");
$admin_groups = [];
$admin_query = $conn->query("SELECT group_id FROM group_users WHERE user_id = $user AND role = 'admin'");
while ($row = $admin_query->fetch_assoc()) {
  $admin_groups[] = $row['group_id'];
}

?>

<link rel="stylesheet" href="../css/style.css">
<style>
  body {
    background: #fdf0d5;
  }

  .groups-container {
    max-width: 800px;
    margin: 40px auto;
    background: white;
    padding: 30px;
    border-radius: 16px;
    box-shadow: 0 8px 18px rgba(0, 0, 0, 0.08);
  }

  h1, h2 {
    color: #0096c7;
    margin-top: 0;
  }

  form {
    display: flex;
    flex-direction: column;
    gap: 12px;
    margin-bottom: 30px;
  }

  input[type="text"], input[type="password"], textarea {
    padding: 10px;
    border: 2px solid #ccc;
    border-radius: 8px;
    font-size: 16px;
    width: 100%;
  }

  button {
    background-color: #f77f00;
    color: white;
    border: none;
    padding: 10px 18px;
    border-radius: 8px;
    font-size: 16px;
    cursor: pointer;
    width: fit-content;
  }

  button:hover {
    background-color: #d66a00;
  }

  .group-list {
    margin-top: 10px;
    display: flex;
    flex-direction: column;
    gap: 10px;
  }

  .group-card {
    background: #f9f9f9;
    padding: 15px 20px;
    border-radius: 12px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 4px 10px rgba(0,0,0,0.05);
  }

  .group-card a {
    background-color: #f77f00;
    color: white;
    padding: 6px 10px;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 600;
  }

  .group-card a:hover {
    background-color: #d66a00;
  }
.checkbox-group {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 10px;
  font-weight: 500;
}

.checkbox-group input[type="checkbox"] {
  width: 18px;
  height: 18px;
  margin: 0;
}


input[type="checkbox"] {
  margin-top: 2px;
}

</style>
<div class="groups-container">
  <h1>Gruppi</h1>

  <h2>Crea nuovo gruppo:</h2>
  <form method="POST">
    <input name="name" placeholder="Nome gruppo" required>
    <textarea name="description" placeholder="Descrizione del gruppo..."></textarea>

 <div class="checkbox-group">
  <input type="checkbox" name="is_private" id="is_private" onchange="togglePassword()">
  <label for="is_private">Gruppo privato</label>
</div>


    <input type="password" name="password" id="password" placeholder="Password del gruppo" style="display:none">

    <button>Crea</button>
  </form>

  <h2>I tuoi gruppi</h2>
  <div class="group-list">
    <?php while($g = $joined->fetch_assoc()): ?>
<div class="group-card">
  <span>
    <?php echo htmlspecialchars($g['name']); ?>
    <?php if (in_array($g['id'], $admin_groups)): ?>
      <a href="edit_group.php?id=<?php echo $g['id']; ?>" title="Modifica gruppo" style="margin-left:8px;">✏️</a>
    <?php endif; ?>
  </span>
  <a href="group.php?id=<?php echo $g['id']; ?>">Vai</a>
</div>


    <?php endwhile; ?>
  </div>

  <h2>Gruppi disponibili</h2>
  <div class="group-list">
    <?php while($g = $all->fetch_assoc()): ?>
      <div class="group-card">
        <span>
          <?php echo htmlspecialchars($g['name']); ?>
          <?php if ($g['is_private']) echo " 🔒"; ?>
        </span>
        <a href="join_group.php?id=<?php echo $g['id']; ?>">Entra</a>
      </div>
    <?php endwhile; ?>
  </div>
</div>
