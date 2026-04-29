<?php
session_start();
require "includes/config.php";
include "includes/header.php";

if(!isset($_SESSION['panier'])){
    $_SESSION['panier'] = [];
}

/* SUPPRIMER */
if(isset($_GET['del'])){
    unset($_SESSION['panier'][$_GET['del']]);
}
?>

<link rel="stylesheet" href="assets/css/style.css">

<div class="container">
<h2>Votre Panier</h2>

<?php
$total = 0;

foreach($_SESSION['panier'] as $id => $qte){

    $req = $conn->prepare("SELECT * FROM produits WHERE id=?");
    $req->execute([$id]);
    $p = $req->fetch();

    $prix = $p['prix'] * $qte;
    $total += $prix;
?>

<div class="product">
    <h3><?= $p['nom'] ?></h3>
    <p><?= $qte ?> x <?= $p['prix'] ?> = <?= $prix ?> FCFA</p>
    <a href="?del=<?= $id ?>">Supprimer</a>
</div>

<?php } ?>

<h3>Total : <?= $total ?> FCFA</h3>

<a href="commande.php">Passer la commande</a>

</div>