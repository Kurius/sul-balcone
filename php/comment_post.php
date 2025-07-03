<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    exit;
}

$user_id = $_SESSION['user_id'];
$post_id = intval($_POST['post_id']);
$content = trim($_POST['content']);

if ($content === '') {
    exit;
}

$conn = new mysqli('localhost', 'root', '', 'sul_balcone');
if ($conn->connect_error) exit;

$stmt = $conn->prepare("INSERT INTO comments (post_id, user_id, content) VALUES (?, ?, ?)");
$stmt->bind_param("iis", $post_id, $user_id, $content);
$stmt->execute();
$conn->close();

header("Location: home.php");
exit;
