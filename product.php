<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

include 'includes/header.php';

// Vérifier que l'ID du produit est fourni
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: index.php");
    exit;
}

// Récupérer le produit
$product = getProductById($pdo, $_GET['id']);
if (!$product) {
    header("Location: index.php");
    exit;
}

// Ajouter au panier
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    if (!isLoggedIn()) {
        header("Location: login.php");
        exit;
    }

    $quantity = intval($_POST['quantity'] ?? 1);
    if ($quantity < 1) $quantity = 1;
    if ($quantity > $product['stock']) $quantity = $product['stock'];

    // Initialiser le panier en session
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    // Ajouter ou mettre à jour le produit dans le panier
    if (isset($_SESSION['cart'][$product['id']])) {
        $_SESSION['cart'][$product['id']] += $quantity;
    } else {
        $_SESSION['cart'][$product['id']] = $quantity;
    }

    header("Location: cart.php?success=" . urlencode("Produit ajouté au panier !"));
    exit;
}

// Récupérer les produits similaires de la même catégorie
$related_products = [];
if ($product['cat_id']) {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE cat_id = ? AND id != ? LIMIT 4");
    $stmt->execute([$product['cat_id'], $product['id']]);
    $related_products = $stmt->fetchAll();
}
?>

<section class="product-detail">
    <div class="container">
        <a href="index.php" style="color: #667eea; text-decoration: none; margin-bottom: 1rem;">← Retour aux produits</a>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 3rem; margin: 2rem 0;">
            <!-- Image du produit -->
            <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 8px; height: 400px; display: flex; align-items: center; justify-content: center; color: white; font-size: 8rem; overflow: hidden;">
                <?php if ($product['image']): ?>
                    <img src="assets/images/<?php echo escape($product['image']); ?>" alt="<?php echo escape($product['nom']); ?>" style="width: 100%; height: 100%; object-fit: cover;">
                <?php else: ?>
                    📦
                <?php endif; ?>
            </div>

            <!-- Détails du produit -->
            <div>
                <h1 style="color: #1a1a2e; margin-bottom: 0.5rem;"><?php echo escape($product['nom']); ?></h1>
                
                <?php if ($product['category_nom']): ?>
                    <p style="color: #667eea; font-size: 0.95rem; margin-bottom: 1rem;">
                        📂 <?php echo escape($product['category_nom']); ?>
                    </p>
                <?php endif; ?>

                <p style="color: #666; font-size: 1.05rem; line-height: 1.6; margin-bottom: 1.5rem;">
                    <?php echo nl2br(escape($product['description'] ?? 'Pas de description')); ?>
                </p>

                <div style="background: #f5f5f5; padding: 1.5rem; border-radius: 8px; margin-bottom: 1.5rem;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                        <span style="font-size: 1rem; color: #666;">Prix :</span>
                        <span style="font-size: 2rem; color: #00d4ff; font-weight: bold;">
                            <?php echo formatPrice($product['prix']); ?>
                        </span>
                    </div>

                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="font-size: 1rem; color: #666;">Stock :</span>
                        <?php if ($product['stock'] > 0): ?>
                            <span style="color: #51cf66; font-weight: bold;">
                                ✓ <?php echo $product['stock']; ?> unité<?php echo $product['stock'] > 1 ? 's' : ''; ?> disponible<?php echo $product['stock'] > 1 ? 's' : ''; ?>
                            </span>
                        <?php else: ?>
                            <span style="color: #ff6b6b; font-weight: bold;">
                                ✗ Rupture de stock
                            </span>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if ($product['stock'] > 0): ?>
                    <form method="POST" style="margin-bottom: 2rem;">
                        <div class="form-group" style="margin-bottom: 1rem;">
                            <label for="quantity" style="display: block; margin-bottom: 0.5rem; color: #333; font-weight: 600;">
                                Quantité :
                            </label>
                            <input type="number" id="quantity" name="quantity" min="1" max="<?php echo $product['stock']; ?>" value="1" required style="padding: 0.75rem; border: 2px solid #ddd; border-radius: 5px; width: 100px; font-size: 1rem;">
                        </div>

                        <?php if (isLoggedIn()): ?>
                            <button type="submit" name="add_to_cart" class="btn btn-primary" style="width: 100%; padding: 1rem; font-size: 1.1rem; font-weight: bold;">
                                🛒 Ajouter au panier
                            </button>
                        <?php else: ?>
                            <a href="login.php" class="btn btn-primary" style="width: 100%; padding: 1rem; font-size: 1.1rem; font-weight: bold; text-align: center; display: block; text-decoration: none;">
                                Connectez-vous pour acheter
                            </a>
                        <?php endif; ?>
                    </form>
                <?php else: ?>
                    <button disabled class="btn" style="width: 100%; padding: 1rem; background-color: #ccc; color: #999; cursor: not-allowed;">
                        Indisponible
                    </button>
                <?php endif; ?>
            </div>
        </div>

        <!-- Produits similaires -->
        <?php if (!empty($related_products)): ?>
            <div style="margin-top: 4rem; padding-top: 2rem; border-top: 2px solid #eee;">
                <h2 style="color: #1a1a2e; margin-bottom: 2rem;">Produits similaires</h2>
                
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 2rem;">
                    <?php foreach ($related_products as $rel_product): ?>
                        <div class="product-card" style="background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1); transition: transform 0.3s;">
                            <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); height: 150px; display: flex; align-items: center; justify-content: center; color: white; font-size: 2.5rem; overflow: hidden;">
                                <?php if ($rel_product['image']): ?>
                                    <img src="assets/images/<?php echo escape($rel_product['image']); ?>" alt="<?php echo escape($rel_product['nom']); ?>" style="width: 100%; height: 100%; object-fit: cover;">
                                <?php else: ?>
                                    📦
                                <?php endif; ?>
                            </div>
                            
                            <div style="padding: 1rem;">
                                <h3 style="margin: 0 0 0.5rem 0; color: #1a1a2e; font-size: 1rem;">
                                    <?php echo escape(substr($rel_product['nom'], 0, 30)); ?>
                                </h3>
                                
                                <p style="color: #00d4ff; font-size: 1.1rem; font-weight: bold; margin: 0.5rem 0;">
                                    <?php echo formatPrice($rel_product['prix']); ?>
                                </p>
                                
                                <a href="product.php?id=<?php echo $rel_product['id']; ?>" class="btn btn-primary" style="width: 100%; padding: 0.5rem; text-align: center; text-decoration: none; display: block; margin-top: 0.5rem;">
                                    Voir
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
