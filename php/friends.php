<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$conn = new mysqli('localhost', 'root', '', 'sul_balcone');

// Tutti gli utenti tranne sé stesso
$users = $conn->query("SELECT id, name FROM users WHERE id != $user_id");

// Richieste ricevute
$requests = $conn->query("
  SELECT friend_requests.id, users.name, friend_requests.sender_id
  FROM friend_requests
  JOIN users ON users.id = friend_requests.sender_id
  WHERE friend_requests.receiver_id = $user_id AND friend_requests.status = 'pending'
");

// Lista amici
$friends = $conn->query("
  SELECT u.id, u.name FROM users u
  WHERE u.id IN (
    SELECT CASE
      WHEN sender_id = $user_id THEN receiver_id
      WHEN receiver_id = $user_id THEN sender_id
    END
    FROM friend_requests
    WHERE (sender_id = $user_id OR receiver_id = $user_id) AND status = 'accepted'
  )
");
?>

<!DOCTYPE html>
<html>
<head>
  <title>Amici</title>
  <meta charset="UTF-8">
  <style>
    body { font-family: sans-serif; padding: 20px; }
    h2 { margin-top: 30px; }
    .user { margin-bottom: 10px; }
  </style>
</head>
<body>
  <h1>I tuoi amici</h1>
  <ul>
    <?php while($f = $friends->fetch_assoc()): ?>
      <li><?php echo htmlspecialchars($f['name']); ?></li>
    <?php endwhile; ?>
  </ul>

  <h2>Richieste ricevute</h2>
  <?php while($r = $requests->fetch_assoc()): ?>
    <div class="user">
      <?php echo htmlspecialchars($r['name']); ?>
      <a href="respond_friend.php?id=<?php echo $r['id']; ?>&action=accept">✅ Accetta</a>
      <a href="respond_friend.php?id=<?php echo $r['id']; ?>&action=decline">❌ Rifiuta</a>
    </div>
  <?php endwhile; ?>

  <h2>Altri utenti</h2>
  <?php while($u = $users->fetch_assoc()): ?>
    <div class="user">
      <?php echo htmlspecialchars($u['name']); ?>
      <form action="send_friend.php" method="POST" style="display:inline;">
        <input type="hidden" name="receiver_id" value="<?php echo $u['id']; ?>">
        <button type="submit">➕ Aggiungi amico</button>
      </form>
    </div>
  <?php endwhile; ?>
</body>
</html>
