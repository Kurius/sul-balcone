<?php
if (!isset($_SESSION)) {
    session_start();
}

$conn = new mysqli('localhost', 'root', '', 'sul_balcone');

$user_id = $_SESSION['user_id'] ?? null;
$profile_img = 'default.png';

if ($user_id) {
    $stmt = $conn->prepare("SELECT profile_picture FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    if ($res && $res['profile_picture']) {
        $profile_img = $res['profile_picture'];
    }
}
?>

<style>
.navbar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: linear-gradient(to right, #0077c2, #00c8e5);
  padding: 16px 32px;
  color: white;
  font-family: 'Poppins', sans-serif;
  box-shadow: 0 4px 6px rgba(0,0,0,0.1);
  position: sticky;
  top: 0;
  z-index: 1000;
}

.navbar h1 {
  margin: 0;
  font-size: 26px;
  font-weight: 600;
}

.navbar h1 a {
  color: white;
  text-decoration: none;
  transition: color 0.3s;
  
}

.navbar h1 a:hover {
  color: #ffd700;
}

.nav-right {
  display: flex;
  align-items: center;
}

.nav-links {
  display: flex;
  gap: 16px;
}

.nav-links a {
  color: white;
  text-decoration: none;
  font-weight: 500;
  padding: 6px 12px;
  border-radius: 4px;
  transition: background 0.2s;
}

.nav-links a:hover {
  background-color: rgba(255, 255, 255, 0.2);
}

.nav-profile {
  margin-left: 16px;
  width: 42px;
  height: 42px;
  border-radius: 50%;
  object-fit: cover;
  border: 2px solid #fff;
  transition: transform 0.3s;
}

.nav-profile:hover {
  transform: scale(1.05);
}


</style>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">

<div class="navbar">
  <h1><a href="../index.php">Sul Balcone</a></h1>
  <div class="nav-right">
    <div class="nav-links">
      <a href="profile.php">Profilo</a>
      <a href="friends.php">Amici</a>
      <a href="groups.php">Gruppi</a>
      <a href="logout.php">Esci</a>
    </div>
    <img class="nav-profile" src="../uploads/profile_pictures/<?php echo htmlspecialchars($profile_img); ?>" alt="Profilo">
  </div>
</div>
