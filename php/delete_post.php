<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$post_id = intval($_GET['id']);
$user_id = $_SESSION['user_id'];

include 'navbar.php';
// Controlla che il post appartenga all'utente
$check = $conn->prepare("SELECT id FROM posts WHERE id = ? AND user_id = ?");
$check->bind_param("ii", $post_id, $user_id);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    $delete = $conn->prepare("DELETE FROM posts WHERE id = ?");
    $delete->bind_param("i", $post_id);
    $delete->execute();
}

$conn->close();
header("Location: home.php");
exit;
