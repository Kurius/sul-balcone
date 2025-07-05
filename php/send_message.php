<?php
session_start();
include 'navbar.php';

if (!isset($_SESSION['user_id'])) {
  header("Location: index.php");
  exit;
}

$sender = $_SESSION['user_id'];
$receiver = intval($_POST['receiver_id']);
$message = trim($_POST['message']);

if ($message !== '') {
  $stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, message, sent_at) VALUES (?, ?, ?, NOW())");
  $stmt->bind_param("iis", $sender, $receiver, $message);
  $stmt->execute();
}

header("Location: chat.php?user_id=$receiver");
exit;
