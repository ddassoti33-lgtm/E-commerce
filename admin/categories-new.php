<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Vérifier que l'utilisateur est admin
requireAdmin();

$errors = [];
$success = '';

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        
        if ($action === 'add') {
            // Ajouter une catégorie
            $nom = trim($_POST['nom'] ?? '');
            $description = trim($_POST['description'] ?? '');
            
            if (empty($nom)) {
                $errors[] = "Le nom de la catégorie est requis.";
            } else {
                try {
                    addCategory($pdo, $nom, $description);
                    $success = "Catégorie ajoutée avec succès!";
                } catch (Exception $e) {
                    $errors[] = "Erreur lors de l'ajout: " . $e->getMessage();
                }
            }
        } elseif ($action === 'update') {
            // Mettre à jour une catégorie
            $id = intval($_POST['id'] ?? 0);
            $nom = trim($_POST['nom'] ?? '');
            $description = trim($_POST['description'] ?? '');
            
            if (empty($nom)) {
                $errors[] = "Le nom de la catégorie est requis.";
            } else {
                try {
                    updateCategory($pdo, $id, $nom, $description);
                    $success = "Catégorie modifiée avec succès!";
                } catch (Exception $e) {
                    $errors[] = "Erreur lors de la modification: " . $e->getMessage();
                }
            }
        } elseif ($action === 'delete') {
            // Supprimer une catégorie
            $id = intval($_POST['id'] ?? 0);
            try {
                deleteCategory($pdo, $id);
                $success = "Catégorie supprimée avec succès!";
            } catch (Exception $e) {
                $errors[] = "Erreur lors de la suppression: " . $e->getMessage();
            }
        }
    }
}

// Récupérer toutes les catégories
$categories = getAllCategories($pdo);

include 'header-admin.php';
?>

<section class="admin-categories">
    <h1>Gestion des Catégories</h1>
    
    <?php displayErrors($errors); ?>
    <?php if ($success) displaySuccess($success); ?>
    
    <!-- Formulaire d'ajout de catégorie -->
    <div class="form-section">
        <h2>Ajouter une nouvelle catégorie</h2>
        <form method="POST" class="admin-form">
            <input type="hidden" name="action" value="add">
            
            <div class="form-group">
                <label for="nom">Nom de la catégorie *</label>
                <input type="text" id="nom" name="nom" required>
            </div>
            
            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description"></textarea>
            </div>
            
            <button type="submit" class="btn btn-success">Ajouter la catégorie</button>
        </form>
    </div>
    
    <!-- Liste des catégories -->
    <div class="table-section">
        <h2>Liste des catégories</h2>
        <?php if (empty($categories)): ?>
            <p style="color: #999; text-align: center; padding: 2rem;">Aucune catégorie trouvée.</p>
        <?php else: ?>
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
                    <?php foreach ($categories as $category): ?>
                        <tr>
                            <td><?php echo $category['id']; ?></td>
                            <td><?php echo escape($category['nom']); ?></td>
                            <td><?php echo escape(substr($category['description'] ?? '', 0, 100)); ?></td>
                            <td>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?php echo $category['id']; ?>">
                                    <button type="submit" class="btn btn-danger btn-small" onclick="return confirm('Êtes-vous sûr?')">Supprimer</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</section>

<style>
.form-section {
    background-color: #f9f9f9;
    padding: 2rem;
    border-radius: 8px;
    margin-bottom: 2rem;
}

.form-section h2 {
    margin-bottom: 1.5rem;
}

.table-section {
    margin-top: 2rem;
}

.btn-small {
    padding: 0.5rem 1rem;
    font-size: 0.9rem;
}
</style>

<?php include 'footer-admin.php'; ?>
