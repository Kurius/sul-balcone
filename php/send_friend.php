<?php
session_start();
if (!isset($_SESSION['user_id'])) exit;

$sender_id = $_SESSION['user_id'];
$receiver_id = intval($_POST['receiver_id']);

if ($sender_id == $receiver_id) exit;

include 'navbar.php';

// Evita richieste duplicate
$stmt = $conn->prepare("
  SELECT id FROM friend_requests
  WHERE (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?)
");
$stmt->bind_param("iiii", $sender_id, $receiver_id, $receiver_id, $sender_id);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows == 0) {
    $insert = $conn->prepare("INSERT INTO friend_requests (sender_id, receiver_id) VALUES (?, ?)");
    $insert->bind_param("ii", $sender_id, $receiver_id);
    $insert->execute();
}

$conn->close();
header("Location: friends.php");
exit;
