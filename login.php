<?php
session_start();
require "includes/config.php";

if(isset($_POST['login'])){
    $email = $_POST['email'];
    $password = $_POST['password'];

    $req = $conn->prepare("SELECT * FROM users WHERE email=?");
    $req->execute([$email]);
    $user = $req->fetch();

    if($user && password_verify($password, $user['password'])){
        $_SESSION['user'] = $user['id'];
        header("Location: produits.php");
    } else {
        echo "Erreur de connexion";
    }
}
?>

<?php include "includes/header.php"; ?>
<link rel="stylesheet" href="assets/css/style.css">

<div class="form-box">
    <h2>Connexion</h2>
    <form method="POST">
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Mot de passe" required>
        <button type="submit" name="login">Se connecter</button>
        <p><a href="register.php">se connecter</a></p>
    </form>
</div>

<?php include "includes/footer.php"; ?>