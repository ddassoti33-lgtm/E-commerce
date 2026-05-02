<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

include 'includes/header.php';
?>

<section class="home">
    <h1>Bienvenue sur E-Commerce</h1>
    <p>Découvrez notre sélection de produits</p>
    
    <div class="filters">
        <input type="text" placeholder="Rechercher un produit..." id="search">
        <select id="category">
            <option value="">Toutes les catégories</option>
        </select>
    </div>
    
    <div class="products-grid">
        <!-- Les produits seront affichés ici -->
    </div>
</section>

<?php include 'includes/footer.php'; ?>
