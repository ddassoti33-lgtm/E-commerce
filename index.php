<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

include 'includes/header.php';

$categories = getAllCategories($pdo);
$products = getAllProducts($pdo);

// Récupérer les filtres
$selected_category = $_GET['category'] ?? '';
$search_term = $_GET['search'] ?? '';

// Filtrer les produits
$filtered_products = [];
foreach ($products as $product) {
    $match_category = empty($selected_category) || $product['cat_id'] == $selected_category;
    $match_search = empty($search_term) || stripos($product['nom'], $search_term) !== false;
    
    if ($match_category && $match_search) {
        $filtered_products[] = $product;
    }
}
?>

<section class="home">
    <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 3rem 0; margin-bottom: 2rem; text-align: center;">
        <h1 style="font-size: 2.5rem; margin-bottom: 0.5rem;">Bienvenue sur Ibn'Shop</h1>
        <p style="font-size: 1.1rem;">Découvrez notre sélection de produits de qualité</p>
    </div>
    
    <div class="container" style="margin-bottom: 2rem;">
        <div class="filters" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 2rem;">
            <div>
                <input type="text" placeholder="🔍 Rechercher un produit..." id="search" style="width: 100%; padding: 0.75rem; border: 2px solid #ddd; border-radius: 5px; font-size: 1rem;">
            </div>
            <div>
                <select id="category" style="width: 100%; padding: 0.75rem; border: 2px solid #ddd; border-radius: 5px; font-size: 1rem;">
                    <option value="">📦 Toutes les catégories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>" <?php echo $selected_category == $cat['id'] ? 'selected' : ''; ?>>
                            <?php echo escape($cat['nom']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <?php if (empty($filtered_products)): ?>
            <div class="alert alert-info" style="text-align: center; padding: 2rem;">
                <p style="font-size: 1.1rem;">Aucun produit trouvé. Essayez de modifier votre recherche.</p>
            </div>
        <?php else: ?>
            <div style="margin-bottom: 1.5rem; color: #666;">
                <p><?php echo count($filtered_products); ?> produit<?php echo count($filtered_products) > 1 ? 's' : ''; ?> trouvé<?php echo count($filtered_products) > 1 ? 's' : ''; ?></p>
            </div>

            <div class="products-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem;">
                <?php foreach ($filtered_products as $product): ?>
                    <div class="product-card" style="background: whitesmoke; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1); transition: transform 0.3s, box-shadow 0.3s; cursor: pointer;" onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 6px 16px rgba(0,0,0,0.2)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 8px rgba(0,0,0,0.1)'"">
                        <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); height: 200px; display: flex; align-items: center; justify-content: center; color: white; font-size: 3rem; overflow: hidden;">
                            <?php if ($product['image']): ?>
                                <img src="assets/images/<?php echo escape($product['image']); ?>" alt="<?php echo escape($product['nom']); ?>" style="width: 100%; height: 100%; object-fit: cover;">
                            <?php else: ?>
                                📦
                            <?php endif; ?>
                        </div>
                        
                        <div style="padding: 1.5rem;">
                            <h3 style="margin: 0 0 0.5rem 0; color: #1a1a2e;"><?php echo escape($product['nom']); ?></h3>
                            
                            <p style="color: #666; font-size: 0.9rem; margin: 0.5rem 0; line-height: 1.4;">
                                <?php echo escape(substr($product['description'] ?? '', 0, 80)); ?>...
                            </p>
                            
                            <?php if ($product['category_nom']): ?>
                                <p style="color: #667eea; font-size: 0.85rem; margin: 0.5rem 0;">
                                    📂 <?php echo escape($product['category_nom']); ?>
                                </p>
                            <?php endif; ?>
                            
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 1rem; padding-top: 1rem; border-top: 1px solid #eee; ">
                                <div>
                                    <span style="font-size: 1.5rem; color: #00d4ff; font-weight: bold;">
                                        <?php echo formatPrice($product['prix']); ?>
                                    </span>
                                    <?php if ($product['stock'] > 0): ?>
                                        <p style="color: #51cf66; font-size: 0.85rem; margin: 0.25rem 0;">
                                            ✓ En stock
                                        </p>
                                    <?php else: ?>
                                        <p style="color: #ff6b6b; font-size: 0.85rem; margin: 0.25rem 0;">
                                            ✗ Rupture de stock
                                        </p>
                                    <?php endif; ?>
                                </div>
                                
                                <a href="product.php?id=<?php echo $product['id']; ?>" class="btn btn-primary" style="padding: 0.6rem 1.2rem; text-decoration: none; display: inline-block;">
                                    Voir +
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<script>
// Filtres en temps réel
document.getElementById('search').addEventListener('keyup', function() {
    const searchTerm = this.value.toLowerCase();
    document.querySelectorAll('.product-card').forEach(card => {
        const productName = card.querySelector('h3').textContent.toLowerCase();
        card.style.display = productName.includes(searchTerm) ? '' : 'none';
    });
});

document.getElementById('category').addEventListener('change', function() {
    const url = new URL(window.location);
    if (this.value) {
        url.searchParams.set('category', this.value);
    } else {
        url.searchParams.delete('category');
    }
    window.location.href = url.toString();
});
</script>

<?php include 'includes/footer.php'; ?>
