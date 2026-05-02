<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

// Vérifier que l'utilisateur est admin
requireAdmin();

include '../includes/header.php';
?>

<section class="admin-products">
    <h1>Gestion des Produits</h1>
    
    <a href="#add-product" class="btn btn-primary">Ajouter un produit</a>
    
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nom</th>
                <th>Prix</th>
                <th>Stock</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <!-- Les produits seront affichés ici -->
        </tbody>
    </table>
</section>

<?php include '../includes/footer.php'; ?>
