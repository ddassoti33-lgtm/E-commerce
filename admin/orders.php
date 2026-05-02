<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

// Vérifier que l'utilisateur est admin
requireAdmin();

include '../includes/header.php';
?>

<section class="admin-orders">
    <h1>Gestion des Commandes</h1>
    
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Client</th>
                <th>Date</th>
                <th>Montant</th>
                <th>Statut</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <!-- Les commandes seront affichées ici -->
        </tbody>
    </table>
</section>

<?php include '../includes/footer.php'; ?>
