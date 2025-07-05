<?php
session_start(); 
include 'navbar.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Query amici
$friends = $conn->query("SELECT id, name, profile_picture, last_active FROM users WHERE id IN (
  SELECT CASE
    WHEN sender_id = $user_id THEN receiver_id
    WHEN receiver_id = $user_id THEN sender_id
  END FROM friend_requests 
  WHERE (sender_id = $user_id OR receiver_id = $user_id) AND status = 'accepted'
)");


// Ricevute
$requests = $conn->query("SELECT friend_requests.id, users.name, users.profile_picture FROM friend_requests 
  JOIN users ON users.id = friend_requests.sender_id 
  WHERE friend_requests.receiver_id = $user_id AND friend_requests.status = 'pending'
");

// Inviate
$sent = $conn->query("SELECT friend_requests.id, users.name, users.profile_picture FROM friend_requests 
  JOIN users ON users.id = friend_requests.receiver_id 
  WHERE friend_requests.sender_id = $user_id AND friend_requests.status = 'pending'
");

// Altri utenti
$others = $conn->query("SELECT id, name, profile_picture FROM users WHERE id != $user_id 
  AND id NOT IN (
    SELECT CASE
      WHEN sender_id = $user_id THEN receiver_id
      WHEN receiver_id = $user_id THEN sender_id
    END FROM friend_requests 
    WHERE (sender_id = $user_id OR receiver_id = $user_id) AND status IN ('accepted', 'pending')
  )
");
$count_friends = $friends->num_rows;
$count_requests = $requests->num_rows;
$count_sent = $sent->num_rows;
$count_others = $others->num_rows;

?>


<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8">
  <title>Amici - Sul Balcone</title>
  <link rel="stylesheet" href="../css/style.css">
  <style>
    .tabs {
      max-width: 900px;
      margin: 40px auto;
    }

    .tab-buttons {
      display: flex;
      border-bottom: 3px solid #fcbf49;
      margin-bottom: 20px;
    }

    .tab-buttons button {
      flex: 1;
      padding: 12px 18px;
      background: #fff;
      border: none;
      border-bottom: 4px solid transparent;
      font-size: 16px;
      font-weight: 600;
      cursor: pointer;
      transition: border-color 0.3s;
    }

    .tab-buttons button.active {
      border-bottom-color: #f77f00;
      background: #fdf0d5;
    }

    .tab-content {
      display: none;
    }

    .tab-content.active {
      display: block;
    }

    .friend-card {
      display: flex;
      align-items: center;
      gap: 15px;
      margin-bottom: 15px;
      background-color: #f9f9f9;
      padding: 12px 18px;
      border-radius: 12px;
      box-shadow: 0 4px 10px rgba(0,0,0,0.05);
    }

    .friend-card .name {
      font-weight: 600;
      flex: 1;
    }

    .friend-card .actions {
      display: flex;
      gap: 10px;
    }

    .friend-card .actions button,
    .friend-card .actions a {
      background-color: #f77f00;
      color: white;
      padding: 6px 10px;
      border: none;
      border-radius: 8px;
      font-size: 14px;
      cursor: pointer;
      text-decoration: none;
    }

    .friend-card .actions button:hover,
    .friend-card .actions a:hover {
      background-color: #d66a00;
    }
  </style>
</head>
<body>

<div class="tabs">
<div class="tab-buttons">
  <button class="active" onclick="openTab('amici')">👥 Amici (<?php echo $count_friends; ?>)</button>
  <button onclick="openTab('ricevute')">📩 Ricevute (<?php echo $count_requests; ?>)</button>
  <button onclick="openTab('inviate')">⏳ Inviate (<?php echo $count_sent; ?>)</button>
  <button onclick="openTab('utenti')">🧑‍🤝‍🧑 Altri utenti (<?php echo $count_others; ?>)</button>
</div>



<div id="amici" class="tab-content active section">
  <h2>I tuoi amici</h2>
  <?php while($f = $friends->fetch_assoc()): ?>
    <?php
      $friend_id = $f['id'];
      $result = $conn->query("SELECT last_active FROM users WHERE id = $friend_id");
      $last_activity = $result->fetch_assoc()['last_active'] ?? null;

      $is_online = false;
      if ($last_activity) {
        $last_time = strtotime($last_activity);
        $is_online = (time() - $last_time <= 60); // 5 minuti
      }
    ?>
    <div class="friend-card">
      <img class="profile-mini" src="../uploads/profile_pictures/<?php echo $f['profile_picture'] ?? 'default.jpg'; ?>">
      <span class="name"><?php echo htmlspecialchars($f['name']); ?></span>
      <span class="status" style="color: <?php echo $is_online ? 'green' : 'gray'; ?>;">
        <?php echo $is_online ? '🟢 Online' : '⚫ Offline'; ?>
      </span>
      <div class="actions">
        <a href="chat.php?user_id=<?php echo $friend_id; ?>">💬 Chat</a>
      </div>
    </div>
  <?php endwhile; ?>
</div>


  <div id="ricevute" class="tab-content section">
    <h2>Richieste ricevute</h2>
    <?php while($r = $requests->fetch_assoc()): ?>
      <div class="friend-card">
        <img class="profile-mini" src="../uploads/profile_pictures/<?php echo $r['profile_picture'] ?? 'default.jpg'; ?>">
        <span class="name"><?php echo htmlspecialchars($r['name']); ?></span>
        <div class="actions">
          <a href="respond_friend.php?id=<?php echo $r['id']; ?>&action=accept">✅</a>
          <a href="respond_friend.php?id=<?php echo $r['id']; ?>&action=decline">❌</a>
        </div>
      </div>
    <?php endwhile; ?>
  </div>

  <div id="inviate" class="tab-content section">
    <h2>Richieste inviate</h2>
    <?php while($s = $sent->fetch_assoc()): ?>
      <div class="friend-card">
        <img class="profile-mini" src="../uploads/profile_pictures/<?php echo $s['profile_picture'] ?? 'default.jpg'; ?>">
        <span class="name"><?php echo htmlspecialchars($s['name']); ?></span>
        <span class="actions">✋ In attesa</span>
      </div>
    <?php endwhile; ?>
  </div>

  <div id="utenti" class="tab-content section">
    <h2>Altri utenti</h2>
    <?php while($u = $others->fetch_assoc()): ?>
      <div class="friend-card">
        <img class="profile-mini" src="../uploads/profile_pictures/<?php echo $u['profile_picture'] ?? 'default.jpg'; ?>">
        <span class="name"><?php echo htmlspecialchars($u['name']); ?></span>
        <form action="send_friend.php" method="POST" class="actions">
          <input type="hidden" name="receiver_id" value="<?php echo $u['id']; ?>">
          <button type="submit">➕ Aggiungi</button>
        </form>
      </div>
    <?php endwhile; ?>
  </div>
</div>

<script>
  function openTab(id) {
    document.querySelectorAll('.tab-content').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.tab-buttons button').forEach(b => b.classList.remove('active'));
    document.getElementById(id).classList.add('active');
    document.querySelector(`[onclick="openTab('${id}')"]`).classList.add('active');
  }
</script>

</body>
</html>
