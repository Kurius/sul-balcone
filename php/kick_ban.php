<link rel="stylesheet" href="../css/style.css">
<?php
session_start();
include 'navbar.php';


if (!isset($_SESSION['user_id'], $_GET['group_id'], $_GET['user_id'], $_GET['action'])) exit;

$admin = $_SESSION['user_id'];
$group_id = intval($_GET['group_id']);
$user_id = intval($_GET['user_id']);
$action = $_GET['action'];

// Verifica permessi
$check = $conn->prepare("SELECT role FROM group_users WHERE group_id = ? AND user_id = ?");
$check->bind_param("ii", $group_id, $admin);
$check->execute();
$role = $check->get_result()->fetch_assoc();

if (!$role || $role['role'] !== 'admin' || $user_id == $admin) exit;

// Rimuove l'utente
$conn->query("DELETE FROM group_users WHERE group_id = $group_id AND user_id = $user_id");

// Se è un ban, registra anche nel registro dei ban
if ($action === 'ban') {
  $stmt = $conn->prepare("INSERT INTO group_bans (group_id, user_id) VALUES (?, ?)");
  $stmt->bind_param("ii", $group_id, $user_id);
  $stmt->execute();
}

header("Location: edit_group.php?id=$group_id");
exit;
?>
