<?php
session_start();
include 'navbar.php';
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    exit;
}

$user_id = $_SESSION['user_id'];
$post_id = intval($_POST['post_id']);

if ($conn->connect_error) exit;

// Controlla se il like esiste
$check = $conn->prepare("SELECT id FROM likes WHERE user_id = ? AND post_id = ?");
$check->bind_param("ii", $user_id, $post_id);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    // Rimuove il like
    $remove = $conn->prepare("DELETE FROM likes WHERE user_id = ? AND post_id = ?");
    $remove->bind_param("ii", $user_id, $post_id);
    $remove->execute();
} else {
    // Aggiunge il like
    $add = $conn->prepare("INSERT INTO likes (user_id, post_id) VALUES (?, ?)");
    $add->bind_param("ii", $user_id, $post_id);
    $add->execute();
}

$conn->close();
echo "ok";
