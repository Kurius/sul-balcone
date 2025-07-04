<?php 
include 'navbar.php';
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Prendi dati utente
$stmt = $conn->prepare("SELECT name, profile_picture FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
?>


<!DOCTYPE html>
<html>
    <link rel="stylesheet" href="../css/style.css">

<head>
  <meta charset="UTF-8">
  <title>Modifica profilo</title>
  
  <style>
/* styles.css */
body{
  background-color:#f4f4f4;
}
.profile-page {
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  color: #333;
  padding: 20px;
  text-align: center;
}




.profile-page img {
  width: 150px;
  height: 150px;
  object-fit: cover;
  border-radius: 50%;
  border: 3px solid #3498db;
  margin-top: 10px;
}




.profile-page button {
  background-color: #3498db;
  color: white;
  border: none;
  padding: 10px 20px;
  font-size: 16px;
  border-radius: 5px;
  cursor: pointer;
}

.profile-page button:hover {
  background-color: #2980b9;
}

.profile-page a {
  display: inline-block;
  margin-top: 30px;
  text-decoration: none;
  color: #2980b9;
  font-weight: bold;
}




  </style>
</head>

<body>
  <div class="profile-page">
    <h1>Ciao, <?php echo htmlspecialchars($user['name']); ?></h1>

    <h3>La tua immagine di profilo</h3>
    <img src="<?php echo $user['profile_picture'] ? '../uploads/profile_pictures/' . $user['profile_picture'] : '../uploads/profile_pictures/default.jpg'; ?>" alt="Profilo">

    <form action="upload_profile_picture.php" method="POST" enctype="multipart/form-data">
      <input type="file" name="profile_picture" accept="image/*" required><br>
      <button type="submit">Carica</button>
    </form>

  </div>
</body>

</html>
