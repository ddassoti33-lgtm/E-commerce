<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

// Vérifier que l'utilisateur est admin
requireAdmin();

include '../includes/header.php';
?>

<section class="admin-categories">
    <h1>Gestion des Catégories</h1>
    
    <a href="#add-category" class="btn btn-primary">Ajouter une catégorie</a>
    
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nom</th>
                <th>Description</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <!-- Les catégories seront affichées ici -->
        </tbody>
    </table>
</section>

<?php include '../includes/footer.php'; ?>
