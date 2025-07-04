<?php
session_start();
include 'navbar.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];


// Inserimento post (se inviato)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['content'])) {
    $content = trim($_POST['content']);
    if (!empty($content)) {
        $stmt = $conn->prepare("INSERT INTO posts (user_id, content) VALUES (?, ?)");
        $stmt->bind_param("is", $user_id, $content);
        $stmt->execute();
        $stmt->close();
        header("Location: home.php");
        exit;
    }
}

// Carica i post
$stmt = $conn->prepare("
  SELECT posts.user_id, posts.id, posts.content, posts.created_at, users.name, users.profile_picture,
         (SELECT COUNT(*) FROM likes WHERE likes.post_id = posts.id) as like_count,
         (SELECT COUNT(*) FROM likes WHERE likes.post_id = posts.id AND likes.user_id = ?) as user_liked
  FROM posts
  JOIN users ON posts.user_id = users.id
  ORDER BY posts.created_at DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$posts = $result->fetch_all(MYSQLI_ASSOC);

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8">
  <title>Home - Sul Balcone</title>
  <link rel="stylesheet" href="../css/style.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  
</head>
<body>

  <div class="container">
    <h1>Benvenutə, <?php echo htmlspecialchars($user_name); ?>!</h1>
    <form method="POST" action="home.php">
      <textarea name="content" placeholder="Condividi con il balcone affianco al tuo qualcosa..."></textarea>
      <button type="submit">Pubblica</button>
    </form>

<?php foreach ($posts as $post): ?>
  <div class="post">
    <div class="post-author"><img src="../uploads/profile_pictures/<?php echo $post['profile_picture'] ?? 'default.jpg'; ?>" width="40" height="40" style="border-radius:50%; vertical-align:middle;">
    <?php echo htmlspecialchars($post['name']); ?></div>
    <div class="post-time"><?php echo date("d/m/Y H:i", strtotime($post['created_at'])); ?></div>
    <div class="post-content"><?php echo nl2br(htmlspecialchars($post['content'])); ?></div>
 <div class="post-actions" style="margin-top: 10px;">
    <a href="edit_post.php?id=<?php echo $post['id']; ?>" style="margin-right: 10px;">✏️ Modifica</a>
    <a href="delete_post.php?id=<?php echo $post['id']; ?>" onclick="return confirm('Sei sicuro di voler eliminare questo post?');">🗑️ Elimina</a>
  </div>
    <div class="like-section">
      <button class="like-btn" data-post-id="<?php echo $post['id']; ?>">
        <?php echo $post['user_liked'] ? "❤️" : "🩶"; ?>
      </button>
      <span class="like-count"><?php echo $post['like_count']; ?> like</span>
    </div>

    <!-- Commenti -->
    <div class="comments">
      <?php
      $conn = new mysqli('localhost', 'root', '', 'my_dalbalcone');

      $stmt = $conn->prepare("
        SELECT users.profile_picture, comments.content, comments.created_at, users.name
        FROM comments
        JOIN users ON comments.user_id = users.id
        WHERE comments.post_id = ?
        ORDER BY comments.created_at DESC
      ");
      $stmt->bind_param("i", $post['id']);
      $stmt->execute();
      $comments_result = $stmt->get_result();
      while ($comment = $comments_result->fetch_assoc()):
      ?>
        <div class="comment">
          <strong><img src="../uploads/profile_pictures/<?php echo $comment['profile_picture'] ?? 'default.jpg'; ?>" width="40" height="40" style="border-radius:50%; vertical-align:middle;"><?php echo htmlspecialchars($comment['name']); ?>:</strong>
          <span><?php echo nl2br(htmlspecialchars($comment['content'])); ?></span>
          <div class="comment-time"><?php echo date("d/m/Y H:i", strtotime($comment['created_at'])); ?></div>
        </div>
      <?php endwhile; ?>
      <?php $stmt->close(); $conn->close(); ?>
    </div>

    <!-- Form nuovo commento -->
    <form class="comment-form" action="comment_post.php" method="POST">
      <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
      <input type="text" name="content" placeholder="Reagisci a questa notizia..." required>
      <button type="submit">Invia</button>
    </form>
  </div>
  <?php if ($post['user_id'] == $_SESSION['user_id']): ?>
 
<?php endif; ?>

<?php endforeach; ?>


  </div>
  <script>
  document.querySelectorAll('.like-btn').forEach(button => {
    button.addEventListener('click', function () {
      const postId = this.getAttribute('data-post-id');
      fetch('like_post.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'post_id=' + postId
      }).then(() => location.reload());
    });
  });
</script>

</body>
</html>
