<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

// Vérifier que l'utilisateur est admin
requireAdmin();

include '../includes/header.php';
?>

<section class="admin-dashboard">
    <h1>Dashboard Admin</h1>
    
    <div class="dashboard-grid">
        <div class="dashboard-card">
            <h3>Produits</h3>
            <p>Gérer les produits</p>
            <a href="products.php" class="btn btn-primary">Voir</a>
        </div>
        
        <div class="dashboard-card">
            <h3>Catégories</h3>
            <p>Gérer les catégories</p>
            <a href="categories.php" class="btn btn-primary">Voir</a>
        </div>
        
        <div class="dashboard-card">
            <h3>Commandes</h3>
            <p>Voir toutes les commandes</p>
            <a href="orders.php" class="btn btn-primary">Voir</a>
        </div>
    </div>
</section>

<?php include '../includes/footer.php'; ?>
