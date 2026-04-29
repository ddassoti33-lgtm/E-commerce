<?php
session_start();
require "includes/config.php";

if(!isset($_SESSION['user'])){
    header("Location: login.php");
}

if(isset($_POST['commander'])){

    $user = $_SESSION['user'];

    $conn->prepare("INSERT INTO commandes(user_id, date) VALUES (?, NOW())")
         ->execute([$user]);

    $commande_id = $conn->lastInsertId();

    foreach($_SESSION['panier'] as $id => $qte){

        $conn->prepare("INSERT INTO ligne_commandes(commande_id, produit_id, quantite)
                        VALUES (?,?,?)")
             ->execute([$commande_id, $id, $qte]);
    }

    unset($_SESSION['panier']);

    echo "Commande validée ✅";
}
?>

<form method="POST">
    <button name="commander">Confirmer la commande</button>
</form>