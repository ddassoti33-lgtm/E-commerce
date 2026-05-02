<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

session_start();

include 'includes/header.php';
?>

<section class="cart">
    <h1>Panier</h1>
    
    <table>
        <thead>
            <tr>
                <th>Produit</th>
                <th>Prix</th>
                <th>Quantité</th>
                <th>Total</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <!-- Les articles du panier seront affichés ici -->
        </tbody>
    </table>
    
    <div class="cart-summary">
        <h2>Résumé</h2>
        <p>Total: <strong>0 €</strong></p>
        <a href="checkout.php" class="btn btn-primary">Procéder au paiement</a>
        <a href="index.php" class="btn btn-secondary">Continuer les achats</a>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
