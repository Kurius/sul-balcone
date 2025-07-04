<?php
if (!isset($_SESSION)) {
    session_start();
}

$conn = new mysqli('localhost', 'root', '', 'my_dalbalcone');

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
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">

<div class="navbar">
  <div class="logo">Sul Balcone</div>

  <nav class="nav-links">
    <a href="home.php">Home</a>
    <a href="profile.php">Profilo</a>
    <a href="friends.php">Amici</a>
    <a href="groups.php">Gruppi</a>
    <a href="logout.php">Esci</a>
    <img src="../uploads/profile_pictures/<?php echo htmlspecialchars($profile_img); ?>" class="nav-avatar" alt="profilo">
  </nav>
</div>
