<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$target_dir = "../uploads/profile_pictures/";

if (!isset($_FILES["profile_picture"])) {
    header("Location: profile.php");
    exit;
}

$file = $_FILES["profile_picture"];
$ext = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));

// Solo immagini
$allowed = ['jpg', 'jpeg', 'png', 'gif'];
if (!in_array($ext, $allowed) || $file['size'] > 2*1024*1024) {
    echo "File non valido.";
    exit;
}

// Rinomina file univocamente
$filename = "user_" . $user_id . "_" . time() . "." . $ext;
$target_file = $target_dir . $filename;

if (move_uploaded_file($file["tmp_name"], $target_file)) {
    $conn = new mysqli('localhost', 'root', '', 'sul_balcone');

    // opzionalmente rimuovi la vecchia immagine se non è la default
    $old = $conn->prepare("SELECT profile_picture FROM users WHERE id = ?");
    $old->bind_param("i", $user_id);
    $old->execute();
    $res = $old->get_result()->fetch_assoc();
    if ($res['profile_picture'] && file_exists($target_dir . $res['profile_picture'])) {
        unlink($target_dir . $res['profile_picture']);
    }

    $stmt = $conn->prepare("UPDATE users SET profile_picture = ? WHERE id = ?");
    $stmt->bind_param("si", $filename, $user_id);
    $stmt->execute();

    header("Location: profile.php");
} else {
    echo "Errore nel caricamento.";
}
