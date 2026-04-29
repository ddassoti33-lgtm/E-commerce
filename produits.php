<?php
session_start();

if(isset($_POST['add'])){
    $id = $_POST['id'];

    if(!isset($_SESSION['panier'][$id])){
        $_SESSION['panier'][$id] = 1;
    } else {
        $_SESSION['panier'][$id]++;
    }
}
?>
<?php include "includes/header.php"; ?>
<link rel="stylesheet" href="assets/css/style.css">

<div class="container">
    <h2>Nos Produits</h2>

    <div class="products">
        <?php
        require "includes/config.php";

        $req = $conn->query("SELECT * FROM produits");

        while($p = $req->fetch()){
        ?>
        <div class="product">
            <img src="assets/images/<?= $p['image'] ?>">
            <h3><?= $p['nom'] ?></h3>
            <p><?= $p['prix'] ?> FCFA</p>
           <form method="POST">
    <input type="hidden" name="id" value="<?= $p['id'] ?>">
    <button name="add">Ajouter au panier</button>
</form>
        </div>
        <?php } ?>
    </div>
</div>

<?php include "includes/footer.php"; ?>