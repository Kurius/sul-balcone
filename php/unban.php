<?php
session_start();
include 'navbar.php'; // O il file dove crei $conn

if (!isset($_SESSION['user_id'], $_GET['group_id'], $_GET['user_id'])) {
  header("Location: index.php");
  exit;
}

$admin = $_SESSION['user_id'];
$group_id = intval($_GET['group_id']);
$user_id = intval($_GET['user_id']);

// Verifica se l'admin ha i permessi sul gruppo
$stmt = $conn->prepare("SELECT role FROM group_users WHERE group_id = ? AND user_id = ?");
$stmt->bind_param("ii", $group_id, $admin);
$stmt->execute();
$role_data = $stmt->get_result()->fetch_assoc();

if (!$role_data || $role_data['role'] !== 'admin') {
  echo "⛔ Non hai il permesso di eseguire questa azione.";
  exit;
}

// Rimuove il ban
$del = $conn->prepare("DELETE FROM group_bans WHERE group_id = ? AND user_id = ?");
$del->bind_param("ii", $group_id, $user_id);
$del->execute();

header("Location: bans.php?group_id=$group_id");
exit;
?>
