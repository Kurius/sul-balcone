<?php
session_start();
if (isset($_SESSION['user_id'])) {
  header("Location: /php/home.php");
  exit;
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8">
  <title>Sul Balcone</title>
  <link rel="stylesheet" href="../css/style.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
</head>
<body>
  <div class="page">
    <div class="left">
      <h1>Sul Balcone</h1>
      <p>Il social partenopeo dove ci si affaccia per fare due chiacchiere, e per bere un caffè.</p>
    </div>

    <div class="right">
      <form action="php/login_process.php" method="POST">
        <h2>Accedi</h2>
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">Entra</button>
        <p class="register-link">Non hai un account? <a href="php/register.php">Registrati</a></p>
      </form>
    </div>
  </div>
</body>
</html>
