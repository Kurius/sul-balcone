<?php
session_start();
$conn = new mysqli('localhost', 'root', '', 'my_dalbalcone');


if (!isset($_SESSION['user_id']) || !isset($_GET['user_id'])) {
  http_response_code(400);
  exit("Parametri mancanti");
}

$my_id = intval($_SESSION['user_id']);
$chat_user_id = intval($_GET['user_id']);

$stmt = $conn->prepare("SELECT * FROM messages WHERE 
  (sender_id = ? AND receiver_id = ?) OR 
  (sender_id = ? AND receiver_id = ?)
  ORDER BY sent_at ASC
");
$stmt->bind_param("iiii", $my_id, $chat_user_id, $chat_user_id, $my_id);
$stmt->execute();
$result = $stmt->get_result();

while ($msg = $result->fetch_assoc()) {
  $class = ($msg['sender_id'] == $my_id) ? 'me' : 'them';
  echo '<div class="msg ' . $class . '">';
  echo nl2br(htmlspecialchars($msg['message']));
  echo '<br><small>' . htmlspecialchars($msg['sent_at']) . '</small>';
  echo '</div>';
}
?>
