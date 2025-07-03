<?php
session_start();
$user = $_SESSION['user_id'];
$gid = intval($_GET['id']);
$conn = new mysqli('localhost', 'root', '', 'sul_balcone');

$group = $conn->query("SELECT is_private, password FROM groups WHERE id = $gid")->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $entered = $_POST['password'] ?? '';
    if ($group['is_private']) {
        if (password_verify($entered, $group['password'])) {
            $conn->query("INSERT INTO group_users (group_id, user_id) VALUES ($gid, $user)");
            header("Location: group.php?id=$gid");
        } else {
            $error = "Password errata.";
        }
    } else {
        $conn->query("INSERT INTO group_users (group_id, user_id) VALUES ($gid, $user)");
        header("Location: group.php?id=$gid");
    }
}
?>

<?php if ($group['is_private']): ?>
<form method="POST">
  <h2>Questo gruppo è privato</h2>
  <p>Inserisci la password per entrare:</p>
  <input type="password" name="password" required>
  <button>Entra</button>
  <?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
</form>
<?php else: ?>
  <?php
    $conn->query("INSERT INTO group_users (group_id, user_id) VALUES ($gid, $user)");
    header("Location: group.php?id=$gid");
  ?>
<?php endif; ?>
