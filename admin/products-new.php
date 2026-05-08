<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Vérifier que l'utilisateur est admin
requireAdmin();

$errors = [];
$success = '';
$categories = getAllCategories($pdo);

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        
        if ($action === 'add') {
            // Ajouter un produit
            $nom = trim($_POST['nom'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $prix = floatval($_POST['prix'] ?? 0);
            $stock = intval($_POST['stock'] ?? 0);
            $category_id = !empty($_POST['cat_id']) ? intval($_POST['cat_id']) : null;
            
            if (empty($nom)) {
                $errors[] = "Le nom du produit est requis.";
            } elseif ($prix <= 0) {
                $errors[] = "Le prix doit être supérieur à 0.";
            } elseif ($stock < 0) {
                $errors[] = "Le stock ne peut pas être négatif.";
            } else {
                try {
                    addProduct($pdo, $nom, $description, $prix, $stock, $category_id);
                    $success = "Produit ajouté avec succès!";
                } catch (Exception $e) {
                    $errors[] = "Erreur lors de l'ajout du produit: " . $e->getMessage();
                }
            }
        } elseif ($action === 'update') {
            // Mettre à jour un produit
            $id = intval($_POST['id'] ?? 0);
            $nom = trim($_POST['nom'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $prix = floatval($_POST['prix'] ?? 0);
            $stock = intval($_POST['stock'] ?? 0);
            $category_id = !empty($_POST['cat_id']) ? intval($_POST['cat_id']) : null;
            
            if (empty($nom)) {
                $errors[] = "Le nom du produit est requis.";
            } elseif ($prix <= 0) {
                $errors[] = "Le prix doit être supérieur à 0.";
            } elseif ($stock < 0) {
                $errors[] = "Le stock ne peut pas être négatif.";
            } else {
                try {
                    updateProduct($pdo, $id, $nom, $description, $prix, $stock, $category_id);
                    $success = "Produit modifié avec succès!";
                } catch (Exception $e) {
                    $errors[] = "Erreur lors de la modification: " . $e->getMessage();
                }
            }
        } elseif ($action === 'delete') {
            // Supprimer un produit
            $id = intval($_POST['id'] ?? 0);
            try {
                deleteProduct($pdo, $id);
                $success = "Produit supprimé avec succès!";
            } catch (Exception $e) {
                $errors[] = "Erreur lors de la suppression: " . $e->getMessage();
            }
        }
    }
}

// Récupérer tous les produits
$products = getAllProducts($pdo);

include 'header-admin.php';
?>

<section class="admin-products">
    <h1>Gestion des Produits</h1>
    
    <?php displayErrors($errors); ?>
    <?php if ($success) displaySuccess($success); ?>
    
    <!-- Formulaire d'ajout de produit -->
    <div class="form-section">
        <h2>Ajouter un nouveau produit</h2>
        <form method="POST" class="admin-form">
            <input type="hidden" name="action" value="add">
            
            <div class="form-group">
                <label for="nom">Nom du produit *</label>
                <input type="text" id="nom" name="nom" required>
            </div>
            
            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description"></textarea>
            </div>
            
            <div class="form-group">
                <label for="prix">Prix (DZD) *</label>
                <input type="number" id="prix" name="prix" step="0.01" min="0" required>
            </div>
            
            <div class="form-group">
                <label for="stock">Stock *</label>
                <input type="number" id="stock" name="stock" min="0" value="0" required>
            </div>
            
            <div class="form-group">
                <label for="cat_id">Catégorie</label>
                <select id="cat_id" name="cat_id">
                    <option value="">-- Sélectionner une catégorie --</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>">
                            <?php echo escape($cat['nom']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <button type="submit" class="btn btn-success">Ajouter le produit</button>
        </form>
    </div>
    
    <!-- Liste des produits -->
    <div class="table-section">
        <h2>Liste des produits</h2>
        <?php if (empty($products)): ?>
            <p style="color: #999; text-align: center; padding: 2rem;">Aucun produit trouvé.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nom</th>
                        <th>Catégorie</th>
                        <th>Prix</th>
                        <th>Stock</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product): ?>
                        <tr>
                            <td><?php echo $product['id']; ?></td>
                            <td><?php echo escape($product['nom']); ?></td>
                            <td><?php echo escape($product['category_nom'] ?? 'N/A'); ?></td>
                            <td><?php echo formatPrice($product['prix']); ?></td>
                            <td>
                                <span class="stock-badge <?php echo $product['stock'] > 0 ? 'in-stock' : 'out-of-stock'; ?>">
                                    <?php echo $product['stock']; ?>
                                </span>
                            </td>
                            <td>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?php echo $product['id']; ?>">
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

.stock-badge {
    padding: 0.5rem 1rem;
    border-radius: 4px;
    font-weight: bold;
}

.stock-badge.in-stock {
    background-color: #d4edda;
    color: #155724;
}

.stock-badge.out-of-stock {
    background-color: #f8d7da;
    color: #721c24;
}

select {
    width: 100%;
    padding: 0.75rem;
    border: 2px solid #e0e0e0;
    border-radius: 5px;
    font-size: 1rem;
}

select:focus {
    outline: none;
    border-color: #00d4ff;
    box-shadow: 0 0 8px rgba(0, 212, 255, 0.2);
}
</style>

<?php include 'footer-admin.php'; ?>
