<?php
session_start();
include 'navbar.php'; // Assicurati che ci sia la connessione `$conn`

if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$user = $_SESSION['user_id'];
$group_id = intval($_GET['id']);

// Se sei il creatore o admin puoi decidere se bloccare l'uscita oppure no.
// Al momento permettiamo l'uscita a tutti.
$stmt = $conn->prepare("DELETE FROM group_users WHERE group_id = ? AND user_id = ?");
$stmt->bind_param("ii", $group_id, $user);
$stmt->execute();

header("Location: groups.php"); // Torna alla pagina dei gruppi
exit;
?>
