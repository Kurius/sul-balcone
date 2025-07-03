<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$post_id = intval($_POST['post_id']);
$user_id = $_SESSION['user_id'];
$content = trim($_POST['content']);

if ($content === '') {
    header("Location: home.php");
    exit;
}

$conn = new mysqli('localhost', 'root', '', 'sul_balcone');
$stmt = $conn->prepare("UPDATE posts SET content = ? WHERE id = ? AND user_id = ?");
$stmt->bind_param("sii", $content, $post_id, $user_id);
$stmt->execute();
$conn->close();

header("Location: home.php");
exit;
