<?php
require "includes/config.php";

if(isset($_POST['register'])){
    $nom = $_POST['nom'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $conn->prepare("INSERT INTO users(nom,email,password) VALUES (?,?,?)")
         ->execute([$nom,$email,$password]);

    echo "Compte créé";
}
?>

<div class="form-box">
    <h2>Inscription</h2>
    <form method="POST">
        <input type="text" name="nom" placeholder="Nom" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Mot de passe" required>
        <button type="submit" name="register">S'inscrire</button>
        <p>deja inscrit?? <a href="login.php">se connecter</a></p>
    </form>
</div>