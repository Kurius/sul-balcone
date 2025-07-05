<?php
session_start();
include 'navbar.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$my_id = $_SESSION['user_id'];
$chat_user_id = intval($_GET['user_id']);


// Recupera info utente
$stmt = $conn->prepare("SELECT name, profile_picture FROM users WHERE id = ?");
$stmt->bind_param("i", $chat_user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
  echo "Utente non trovato.";
  exit;
}

// Recupera messaggi
$msgs = $conn->query("SELECT * FROM messages WHERE 
  (sender_id = $my_id AND receiver_id = $chat_user_id)
  OR 
  (sender_id = $chat_user_id AND receiver_id = $my_id)
  ORDER BY sent_at ASC
");
?>

<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8">
  <title>Chat con <?php echo htmlspecialchars($user['name']); ?></title>
  <link rel="stylesheet" href="../css/style.css">
  <style>
    .chat-container {
      max-width: 700px;
      margin: 40px auto;
      background: #fff;
      padding: 20px;
      border-radius: 12px;
      box-shadow: 0 0 15px rgba(0,0,0,0.1);
    }

    .chat-messages {
      max-height: 400px;
      overflow-y: auto;
      margin-bottom: 20px;
    }

    .msg {
      padding: 10px;
      margin-bottom: 10px;
      border-radius: 8px;
      max-width: 75%;
    }

    .msg.me {
      background: #fcbf49;
      margin-left: auto;
      text-align: right;
    }

    .msg.them {
      background: #e0e0e0;
    }

    .chat-form textarea {
      width: 100%;
      height: 60px;
      resize: none;
      padding: 10px;
    }

    .chat-form button {
      margin-top: 8px;
      padding: 8px 16px;
      background: #f77f00;
      color: white;
      border: none;
      border-radius: 6px;
      cursor: pointer;
    }
  </style>
</head>
<body>

<div class="chat-container">
  <h2>Chat con <?php echo htmlspecialchars($user['name']); ?></h2>
  <div class="chat-messages" id="chat-box">
  </div>

  <form action="send_message.php" method="POST" class="chat-form">
    <input type="hidden" name="receiver_id" value="<?php echo $chat_user_id; ?>">
    <textarea name="message" placeholder="Scrivi un messaggio..." required></textarea>
    <button type="submit">Invia</button>
  </form>
  <script>
const chatBox = document.getElementById("chat-box");
const receiverId = <?php echo $chat_user_id; ?>;

// Funzione per caricare i messaggi ogni 2 secondi
function loadMessages() {
  const xhr = new XMLHttpRequest();
  xhr.open("GET", "load_messages.php?user_id=" + receiverId, true);
  xhr.onload = function () {
    if (this.status === 200) {
      const wasScrolledToBottom = chatBox.scrollTop + chatBox.clientHeight === chatBox.scrollHeight;
      chatBox.innerHTML = this.responseText;
      if (!wasScrolledToBottom) return;
      chatBox.scrollTop = chatBox.scrollHeight;
    }
  };
  xhr.send();
}

// Primo caricamento e poi ogni 2s
loadMessages();
setInterval(loadMessages, 2000);

// Scroll automatico alla fine
setTimeout(() => {
  chatBox.scrollTop = chatBox.scrollHeight;
}, 100);
</script>

</div>

</body>
</html>
