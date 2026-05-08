<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

requireAdmin();

include 'header-admin.php';

$categories = getAllCategories($pdo);
?>

<div class="admin-categories">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h1>Gestion des Catégories</h1>
        <a href="add-category.php" class="btn btn-primary">+ Ajouter une catégorie</a>
    </div>
    
    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success"><?php echo escape($_GET['success']); ?></div>
    <?php endif; ?>
    
    <?php if (empty($categories)): ?>
        <div class="alert alert-info">Aucune catégorie trouvée. <a href="add-category.php">Ajouter la première</a></div>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    
                    <th>Nom</th>
                    <th>Description</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($categories as $category): ?>
                    <tr>
                       
                        <td><strong><?php echo escape($category['nom']); ?></strong></td>
                        <td><?php echo escape(substr($category['description'] ?? '', 0, 50)) . (strlen($category['description'] ?? '') > 50 ? '...' : ''); ?></td>
                       
                        <td>
                            <div class="btn-group">
                                <a href="add-category.php?id=<?php echo $category['id']; ?>" class="btn btn-secondary" style="padding: 0.5rem 1rem; font-size: 0.85rem;">Modifier</a>
                                <a href="delete-category.php?id=<?php echo $category['id']; ?>" class="btn btn-danger" style="padding: 0.5rem 1rem; font-size: 0.85rem;" onclick="return confirm('Êtes-vous sûr ?');">Supprimer</a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php include 'footer-admin.php'; ?>
