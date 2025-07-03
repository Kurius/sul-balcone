<?php
session_start();

// Se l'utente è già loggato, lo mando alla home
if (isset($_SESSION['user_id'])) {
    header("Location: home.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8">
  <title>Registrati - Sul Balcone</title>
  <link rel="stylesheet" href="../css/style.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
</head>
<body>
  <div class="page">
    <div class="left">
      <h1>Sul Balcone</h1>
      <p>Benvenuto! Qui ci affacciamo per condividere la vita con gli amici.</p>
    </div>

    <div class="right">
      <form action="register_process.php" method="POST">
        <h2>Crea un account</h2>
        <input type="text" name="name" placeholder="Nome completo" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Password" required>
        <input type="password" name="confirm_password" placeholder="Conferma password" required>
        <button type="submit">Registrati</button>
        <p class="register-link">Hai già un account? <a href="../index.php">Accedi</a></p>
      </form>
    </div>
  </div>
</body>
</html>
