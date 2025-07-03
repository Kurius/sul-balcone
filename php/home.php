<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];

// Connessione DB
$host = 'localhost';
$db_user = 'root';
$db_password = '';
$db_name = 'sul_balcone';

$conn = new mysqli($host, $db_user, $db_password, $db_name);
if ($conn->connect_error) {
    die("Errore di connessione: " . $conn->connect_error);
}

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
  SELECT posts.user_id, posts.id, posts.content, posts.created_at, users.name,
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
  <link rel="stylesheet" href="style.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <style>
    .like-section {
  margin-top: 10px;
}

.like-btn {
  border: none;
  background: none;
  font-size: 20px;
  cursor: pointer;
  margin-right: 8px;
}

.like-count {
  font-size: 14px;
  color: #555;
}

    body {
      font-family: 'Poppins', sans-serif;
      background: #f2f2f2;
    }

    .container {
      max-width: 700px;
      margin: 40px auto;
      background: #fff;
      border-radius: 20px;
      padding: 30px;
      box-shadow: 0 8px 24px rgba(0,0,0,0.1);
    }

    h1 {
      color: #0096c7;
      text-align: center;
    }

    form textarea {
      width: 100%;
      height: 100px;
      resize: none;
      padding: 12px;
      font-size: 16px;
      border-radius: 10px;
      border: 1px solid #ccc;
      margin-bottom: 10px;
    }

    form button {
      background-color: #38b000;
      color: white;
      border: none;
      padding: 10px 20px;
      border-radius: 8px;
      font-size: 16px;
      cursor: pointer;
    }

    .post {
      border-top: 1px solid #ddd;
      padding-top: 20px;
      margin-top: 20px;
    }

    .post-author {
      font-weight: bold;
      color: #0077b6;
    }

    .post-time {
      font-size: 13px;
      color: #888;
      margin-bottom: 10px;
    }

    .logout-link {
      text-align: center;
      margin-top: 30px;
    }

    .logout-link a {
      color: #f77f00;
      text-decoration: none;
    }

    .logout-link a:hover {
      text-decoration: underline;
    }
    .comments {
  margin-top: 15px;
  padding-left: 15px;
  border-left: 3px solid #ddd;
}

.comment {
  margin-bottom: 10px;
}

.comment-time {
  font-size: 12px;
  color: #888;
}

.comment-form {
  margin-top: 10px;
  display: flex;
  gap: 10px;
}

.comment-form input[type="text"] {
  flex: 1;
  padding: 8px;
  border-radius: 8px;
  border: 1px solid #ccc;
}

.comment-form button {
  background-color: #0096c7;
  color: white;
  border: none;
  padding: 8px 14px;
  border-radius: 8px;
  cursor: pointer;
}

  </style>
</head>
<body>
  <div class="container">
    <h1>Benvenutə, <?php echo htmlspecialchars($user_name); ?>!</h1>
    <a href="friends.php">👫 Amici</a>
    <form method="POST" action="home.php">
      <textarea name="content" placeholder="Scrivi qualcosa sul balcone..."></textarea>
      <button type="submit">Pubblica</button>
    </form>

<?php foreach ($posts as $post): ?>
  <div class="post">
    <div class="post-author"><?php echo htmlspecialchars($post['name']); ?></div>
    <div class="post-time"><?php echo date("d/m/Y H:i", strtotime($post['created_at'])); ?></div>
    <div class="post-content"><?php echo nl2br(htmlspecialchars($post['content'])); ?></div>

    <div class="like-section">
      <button class="like-btn" data-post-id="<?php echo $post['id']; ?>">
        <?php echo $post['user_liked'] ? "❤️" : "🩶"; ?>
      </button>
      <span class="like-count"><?php echo $post['like_count']; ?> like</span>
    </div>

    <!-- Commenti -->
    <div class="comments">
      <?php
      $conn = new mysqli('localhost', 'root', '', 'sul_balcone');
      $stmt = $conn->prepare("
        SELECT comments.content, comments.created_at, users.name
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
          <strong><?php echo htmlspecialchars($comment['name']); ?>:</strong>
          <span><?php echo nl2br(htmlspecialchars($comment['content'])); ?></span>
          <div class="comment-time"><?php echo date("d/m/Y H:i", strtotime($comment['created_at'])); ?></div>
        </div>
      <?php endwhile; ?>
      <?php $stmt->close(); $conn->close(); ?>
    </div>

    <!-- Form nuovo commento -->
    <form class="comment-form" action="comment_post.php" method="POST">
      <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
      <input type="text" name="content" placeholder="Scrivi un commento..." required>
      <button type="submit">Invia</button>
    </form>
  </div>
  <?php if ($post['user_id'] == $_SESSION['user_id']): ?>
  <div class="post-actions" style="margin-top: 10px;">
    <a href="edit_post.php?id=<?php echo $post['id']; ?>" style="margin-right: 10px;">✏️ Modifica</a>
    <a href="delete_post.php?id=<?php echo $post['id']; ?>" onclick="return confirm('Sei sicuro di voler eliminare questo post?');">🗑️ Elimina</a>
  </div>
<?php endif; ?>

<?php endforeach; ?>


    <div class="logout-link">
      <a href="logout.php">Esci</a>
    </div>
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
