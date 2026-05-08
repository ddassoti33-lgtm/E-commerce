<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

requireAdmin();

include 'header-admin.php';

$products = getAllProducts($pdo);
$categories = getAllCategories($pdo);
?>

<div class="admin-products">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h1>Gestion des Produits</h1>
        <a href="add-product.php" class="btn btn-primary">+ Ajouter un produit</a>
    </div>
    
    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success"><?php echo escape($_GET['success']); ?></div>
    <?php endif; ?>
    
    <div style="margin-bottom: 1.5rem;">
        <label>Filtrer par catégorie:</label>
        <select id="categoryFilter" style="padding: 0.5rem; border-radius: 5px; border: 1px solid #ccc;">
            <option value="">Tous les produits</option>
            <?php foreach ($categories as $cat): ?>
                <option value="<?php echo $cat['id']; ?>"><?php echo escape($cat['nom']); ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    
    <?php if (empty($products)): ?>
        <div class="alert alert-info">Aucun produit trouvé. <a href="add-product.php">Ajouter le premier</a></div>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Image</th>
                    <th>Nom</th>
                    <th>Catégorie</th>
                    <th>Prix</th>
                    <th>Stock</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $product): ?>
                    <tr class="product-row" data-category="<?php echo $product['cat_id'] ?? ''; ?>">
                        <td style="text-align: center;">
                            <?php if ($product['image']): ?>
                                <img src="../assets/images/<?php echo escape($product['image']); ?>" alt="<?php echo escape($product['nom']); ?>" style="max-width: 80px; max-height: 80px; border-radius: 4px; object-fit: cover;">
                            <?php else: ?>
                                <span style="color: #999;">-</span>
                            <?php endif; ?>
                        </td>
                        <td><strong><?php echo escape($product['nom']); ?></strong></td>
                        <td><?php echo escape($product['category_nom'] ?? 'Non classé'); ?></td>
                        <td><?php echo formatPrice($product['prix']); ?></td>
                        <td>
                            <span style="<?php echo $product['stock'] < 5 ? 'color: red; font-weight: bold;' : ''; ?>">
                                <?php echo escape($product['stock']); ?>
                            </span>
                        </td>
                        <td>
                            <div class="btn-group">
                                <a href="add-product.php?id=<?php echo $product['id']; ?>" class="btn btn-secondary" style="padding: 0.5rem 1rem; font-size: 0.85rem;">Modifier</a>
                                <a href="delete-product.php?id=<?php echo $product['id']; ?>" class="btn btn-danger" style="padding: 0.5rem 1rem; font-size: 0.85rem;" onclick="return confirm('Êtes-vous sûr ?');">Supprimer</a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<script>
document.getElementById('categoryFilter').addEventListener('change', function() {
    const selectedCategory = this.value;
    document.querySelectorAll('.product-row').forEach(row => {
        if (!selectedCategory || row.dataset.category === selectedCategory) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
});
</script>

<?php include 'footer-admin.php'; ?>
