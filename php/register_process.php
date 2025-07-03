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

// Recupero dei dati
$name = trim($_POST['name']);
$email = trim($_POST['email']);
$password = $_POST['password'];
$confirm_password = $_POST['confirm_password'];

// Verifica campi obbligatori
if (empty($name) || empty($email) || empty($password) || empty($confirm_password)) {
    die("Tutti i campi sono obbligatori.");
}

// Controllo password
if ($password !== $confirm_password) {
    die("Le password non coincidono.");
}

// Controllo se email già esiste
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    die("Email già registrata.");
}
$stmt->close();

// Cripta password
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Inserisce l'utente
$stmt = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $name, $email, $hashed_password);

if ($stmt->execute()) {
    $_SESSION['user_id'] = $stmt->insert_id;
    $_SESSION['user_name'] = $name;
    header("Location: home.php");
    exit;
} else {
    echo "Errore durante la registrazione.";
}

$stmt->close();
$conn->close();
?>
