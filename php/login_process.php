<?php
session_start();

// ⚙️ CONFIGURA I TUOI PARAMETRI QUI
$host = 'localhost';
$db_user = 'root';
$db_password = '';
$db_name = 'sul_balcone';

$conn = new mysqli($host, $db_user, $db_password, $db_name);

// Verifica connessione
if ($conn->connect_error) {
    die("Errore di connessione: " . $conn->connect_error);
}

// Recupero dei dati dal form
$email = trim($_POST['email']);
$password = $_POST['password'];

// Verifica campi obbligatori
if (empty($email) || empty($password)) {
    die("Inserisci email e password.");
}

// Controlla se esiste l'email
$stmt = $conn->prepare("SELECT id, name, password FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 1) {
    $stmt->bind_result($id, $name, $hashed_password);
    $stmt->fetch();

    if (password_verify($password, $hashed_password)) {
        // Login riuscito
        $_SESSION['user_id'] = $id;
        $_SESSION['user_name'] = $name;
        header("Location: home.php");
        exit;
    } else {
        $_SESSION['error'] = 'Credenziali errate';
        header("Location: ../index.php");
        exit;
    }
}
 else {
        $_SESSION['error'] = 'Credenziali errate';
        header("Location: ../index.php");
        exit;
}

$stmt->close();
$conn->close();
?>
