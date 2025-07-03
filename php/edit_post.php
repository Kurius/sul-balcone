<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$post_id = intval($_GET['id']);
$user_id = $_SESSION['user_id'];

$conn = new mysqli('localhost', 'root', '', 'sul_balcone');
$stmt = $conn->prepare("SELECT content FROM posts WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $post_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $content = $row['content'];
} else {
    header("Location: home.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Modifica Post</title>
  <meta charset="UTF-8">
  <style>
    body { font-family: sans-serif; padding: 30px; }
    textarea { width: 100%; height: 150px; padding: 10px; font-size: 16px; }
    button { margin-top: 10px; padding: 10px 20px; }
  </style>
</head>
<body>
  <h2>Modifica il tuo post</h2>
  <form action="edit_post_process.php" method="POST">
    <input type="hidden" name="post_id" value="<?php echo $post_id; ?>">
    <textarea name="content" required><?php echo htmlspecialchars($content); ?></textarea><br>
    <button type="submit">Salva modifiche</button>
  </form>
</body>
</html>
