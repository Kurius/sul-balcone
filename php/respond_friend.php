<?php
session_start();
if (!isset($_SESSION['user_id'])) exit;

$user_id = $_SESSION['user_id'];
$request_id = intval($_GET['id']);
$action = $_GET['action'];

if (!in_array($action, ['accept', 'decline'])) exit;

$status = $action === 'accept' ? 'accepted' : 'declined';

$conn = new mysqli('localhost', 'root', '', 'sul_balcone');

$stmt = $conn->prepare("UPDATE friend_requests SET status = ? WHERE id = ? AND receiver_id = ?");
$stmt->bind_param("sii", $status, $request_id, $user_id);
$stmt->execute();

$conn->close();
header("Location: friends.php");
exit;
