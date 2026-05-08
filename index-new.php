<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

include 'includes/header.php';

// Récupérer tous les produits
$products = getAllProducts($pdo);
$categories = getAllCategories($pdo);
?>

<section class="home">
    <h1>Bienvenue sur EsaShop</h1>
    <p>Découvrez notre sélection de produits de qualité</p>
    
    <div class="filters">
        <input type="text" placeholder="Rechercher un produit..." id="search">
        <select id="category">
            <option value="">Toutes les catégories</option>
            <?php foreach ($categories as $cat): ?>
                <option value="<?php echo $cat['id']; ?>">
                    <?php echo escape($cat['nom']); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    
    <div class="products-grid">
        <?php if (empty($products)): ?>
            <p style="color: #999; text-align: center; padding: 2rem; grid-column: 1/-1;">Aucun produit disponible pour le moment.</p>
        <?php else: ?>
            <?php foreach ($products as $product): ?>
                <div class="product-card">
                    <div class="product-image">
                        <?php if (!empty($product['image'])): ?>
                            <img src="assets/images/<?php echo escape($product['image']); ?>" alt="<?php echo escape($product['nom']); ?>">
                        <?php else: ?>
                            <div class="placeholder-image">Pas d'image</div>
                        <?php endif; ?>
                    </div>
                    <div class="product-info">
                        <h3><?php echo escape($product['nom']); ?></h3>
                        <p class="product-category"><?php echo escape($product['category_nom'] ?? 'Catégorie non définie'); ?></p>
                        <p class="product-description"><?php echo escape(substr($product['description'] ?? '', 0, 100)); ?>...</p>
                        
                        <div class="product-footer">
                            <span class="product-price"><?php echo formatPrice($product['prix']); ?></span>
                            <?php if ($product['stock'] > 0): ?>
                                <span class="stock-info">Stock: <?php echo $product['stock']; ?></span>
                            <?php else: ?>
                                <span class="stock-info out-of-stock">Rupture de stock</span>
                            <?php endif; ?>
                        </div>
                        
                        <?php if ($product['stock'] > 0): ?>
                            <?php if (isLoggedIn()): ?>
                                <form method="POST" action="cart.php" class="add-to-cart-form">
                                    <input type="hidden" name="action" value="add">
                                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                    <input type="number" name="quantity" min="1" max="<?php echo $product['stock']; ?>" value="1">
                                    <button type="submit" class="btn btn-primary">Ajouter au panier</button>
                                </form>
                            <?php else: ?>
                                <a href="login.php" class="btn btn-primary" style="width: 100%; text-align: center;">Se connecter pour acheter</a>
                            <?php endif; ?>
                        <?php else: ?>
                            <button disabled class="btn btn-secondary" style="width: 100%; opacity: 0.5; cursor: not-allowed;">Indisponible</button>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</section>

<style>
.home {
    padding: 2rem 0;
}

.home h1 {
    text-align: center;
    margin-bottom: 1rem;
    color: #1a1a2e;
}

.home > p {
    text-align: center;
    color: #666;
    margin-bottom: 2rem;
}

.filters {
    display: flex;
    gap: 1rem;
    margin: 2rem 0;
    justify-content: center;
}

.filters input,
.filters select {
    padding: 0.75rem;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 1rem;
}

.filters input {
    flex: 1;
    max-width: 300px;
}

.products-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 2rem;
    margin: 2rem 0;
}

.product-card {
    background: white;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s, box-shadow 0.3s;
    display: flex;
    flex-direction: column;
}

.product-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.product-image {
    width: 100%;
    height: 200px;
    background-color: #f0f0f0;
    overflow: hidden;
}

.product-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.placeholder-image {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    font-weight: bold;
}

.product-info {
    padding: 1.5rem;
    display: flex;
    flex-direction: column;
    flex: 1;
}

.product-info h3 {
    margin: 0 0 0.5rem 0;
    color: #1a1a2e;
    font-size: 1.1rem;
}

.product-category {
    color: #667eea;
    font-size: 0.9rem;
    margin: 0 0 0.5rem 0;
}

.product-description {
    color: #666;
    font-size: 0.9rem;
    margin: 0 0 1rem 0;
    flex: 1;
}

.product-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
}

.product-price {
    font-size: 1.3rem;
    color: #00d4ff;
    font-weight: bold;
}

.stock-info {
    background-color: #d4edda;
    color: #155724;
    padding: 0.25rem 0.75rem;
    border-radius: 4px;
    font-size: 0.85rem;
}

.stock-info.out-of-stock {
    background-color: #f8d7da;
    color: #721c24;
}

.add-to-cart-form {
    display: flex;
    gap: 0.5rem;
}

.add-to-cart-form input[type="number"] {
    width: 60px;
    padding: 0.5rem;
    border: 1px solid #ddd;
    border-radius: 4px;
}

.add-to-cart-form .btn {
    flex: 1;
}
</style>

<?php include 'includes/footer.php'; ?>
