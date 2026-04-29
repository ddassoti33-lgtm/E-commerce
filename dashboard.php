<?php
session_start();
require "includes/config.php";

if(!isset($_SESSION['user'])){
    header("Location: login.php");
}

?>

<h2>Admin Dashboard</h2>

<a href="add_produit.php">Ajouter produit</a>

<h3>Commandes</h3>

<?php
$cmd = $conn->query("SELECT * FROM commandes");

while($c = $cmd->fetch()){
    echo "Commande N°".$c['id']."<br>";
}
?>