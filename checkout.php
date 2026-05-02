<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

session_start();

// Vérifier que l'utilisateur est connecté
requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Traiter la commande
}

include 'includes/header.php';
?>

<section class="checkout">
    <h1>Passer une Commande</h1>
    
    <div style="max-width: 800px; margin: 2rem auto;">
        <h2>Adresse de livraison</h2>
        
        <form method="POST">
            <div class="form-group">
                <label>Adresse:</label>
                <input type="text" name="address" required>
            </div>
            
            <div class="form-group">
                <label>Ville:</label>
                <input type="text" name="city" required>
            </div>
            
            <div class="form-group">
                <label>Code postal:</label>
                <input type="text" name="zip" required>
            </div>
            
            <div class="form-group">
                <label>Téléphone:</label>
                <input type="tel" name="phone" required>
            </div>
            
            <h2>Méthode de paiement</h2>
            
            <div class="form-group">
                <label>
                    <input type="radio" name="payment_method" value="card" required>
                    Carte bancaire
                </label>
            </div>
            
            <div class="form-group">
                <label>
                    <input type="radio" name="payment_method" value="bank_transfer" required>
                    Virement bancaire
                </label>
            </div>
            
            <button type="submit" class="btn btn-primary">Valider la commande</button>
        </form>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
