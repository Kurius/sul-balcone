<?php
session_start();
include 'navbar.php';

if (!isset($_SESSION['user_id'], $_GET['group_id'], $_GET['post_id'])) exit;

$uid = $_SESSION['user_id'];
$group_id = intval($_GET['group_id']);
$post_id = intval($_GET['post_id']);

// Verifica se è admin
$check = $conn->prepare("SELECT role FROM group_users WHERE group_id=? AND user_id=?");
$check->bind_param("ii", $group_id, $uid);
$check->execute();
$res = $check->get_result()->fetch_assoc();

if (!$res || $res['role'] !== 'admin') exit;

// Cancella post
$conn->query("DELETE FROM group_posts WHERE id = $post_id AND group_id = $group_id");

header("Location: group.php?id=$group_id");
exit;
?>
