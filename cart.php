<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

include 'includes/header.php';

requireLogin();

// Traiter la suppression d'un article
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_item'])) {
    $product_id = $_POST['remove_item'];
    unset($_SESSION['cart'][$product_id]);
    header("Location: cart.php?success=" . urlencode("Produit supprimé du panier"));
    exit;
}

// Traiter la modification de quantité
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_quantity'])) {
    $product_id = $_POST['product_id'];
    $quantity = intval($_POST['quantity']);
    
    if ($quantity < 1) {
        unset($_SESSION['cart'][$product_id]);
    } else {
        $_SESSION['cart'][$product_id] = $quantity;
    }
    header("Location: cart.php");
    exit;
}

// Vider le panier
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['clear_cart'])) {
    $_SESSION['cart'] = [];
    header("Location: cart.php?success=" . urlencode("Panier vidé"));
    exit;
}

// Récupérer les produits du panier
$cart_products = [];
$total = 0;

if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $product_id => $quantity) {
        $product = getProductById($pdo, $product_id);
        if ($product) {
            $product_total = $product['prix'] * $quantity;
            $total += $product_total;
            $cart_products[] = array_merge($product, ['quantity' => $quantity, 'product_total' => $product_total]);
        }
    }
}
?>

<section class="cart">
    <div class="container">
        <h1 style="color: #1a1a2e; margin-bottom: 2rem;">🛒 Mon Panier</h1>
        
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success"><?php echo escape($_GET['success']); ?></div>
        <?php endif; ?>
        
        <?php if (empty($cart_products)): ?>
            <div style="text-align: center; padding: 3rem 0;">
                <p style="font-size: 3rem; margin: 1rem 0;">📦</p>
                <h2 style="color: #666;">Votre panier est vide</h2>
                <p style="color: #999; margin-bottom: 2rem;">Découvrez nos produits et ajoutez-les à votre panier</p>
                <a href="index.php" class="btn btn-primary" style="text-decoration: none;">← Continuer les achats</a>
            </div>
        <?php else: ?>
            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem; margin-bottom: 2rem;">
                <!-- Table du panier -->
                <div>
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead style="background-color: #f5f5f5; border-bottom: 2px solid #ddd;">
                            <tr>
                                <th style="padding: 1rem; text-align: left;">Produit</th>
                                <th style="padding: 1rem; text-align: center;">Quantité</th>
                                <th style="padding: 1rem; text-align: right;">Prix unitaire</th>
                                <th style="padding: 1rem; text-align: right;">Total</th>
                                <th style="padding: 1rem; text-align: center;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($cart_products as $product): ?>
                                <tr style="border-bottom: 1px solid #eee;">
                                    <td style="padding: 1rem;">
                                        <a href="product.php?id=<?php echo $product['id']; ?>" style="color: #667eea; text-decoration: none; font-weight: 500;">
                                            <?php echo escape($product['nom']); ?>
                                        </a>
                                    </td>
                                    <td style="padding: 1rem; text-align: center;">
                                        <form method="POST" style="display: inline-flex; gap: 0.5rem;">
                                            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                            <input type="number" name="quantity" value="<?php echo $product['quantity']; ?>" min="1" max="<?php echo $product['stock']; ?>" style="width: 60px; padding: 0.4rem; border: 1px solid #ddd; border-radius: 4px; text-align: center;">
                                            <button type="submit" name="update_quantity" style="padding: 0.4rem 0.8rem; background: #667eea; color: white; border: none; border-radius: 4px; cursor: pointer;">✓</button>
                                        </form>
                                    </td>
                                    <td style="padding: 1rem; text-align: right;">
                                        <?php echo formatPrice($product['prix']); ?>
                                    </td>
                                    <td style="padding: 1rem; text-align: right; font-weight: bold; color: #00d4ff;">
                                        <?php echo formatPrice($product['product_total']); ?>
                                    </td>
                                    <td style="padding: 1rem; text-align: center;">
                                        <form method="POST" style="display: inline;">
                                            <button type="submit" name="remove_item" value="<?php echo $product['id']; ?>" class="btn btn-danger" style="padding: 0.4rem 0.8rem; font-size: 0.85rem;">🗑️</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    
                    <div style="margin-top: 1.5rem;">
                        <form method="POST">
                            <button type="submit" name="clear_cart" class="btn btn-secondary" style="padding: 0.6rem 1.2rem;">Vider le panier</button>
                        </form>
                    </div>
                </div>

                <!-- Résumé du panier -->
                <div style="background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); height: fit-content;">
                    <h2 style="color: #1a1a2e; margin-top: 0;">Résumé</h2>
                    
                    <div style="margin: 1.5rem 0; padding: 1.5rem 0; border-bottom: 1px solid #eee;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 0.8rem;">
                            <span>Sous-total:</span>
                            <span><?php echo number_format($total, 2, ',', ' '); ?> Fcfa</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 0.8rem;">
                            <span>Frais de port:</span>
                            <span>Gratuit</span>
                        </div>
                    </div>

                    <div style="display: flex; justify-content: space-between; margin: 1.5rem 0; font-size: 1.3rem; font-weight: bold; color: #00d4ff;">
                        <span>TOTAL:</span>
                        <span><?php echo number_format($total, 2, ',', ' '); ?> Fcfa</span>
                    </div>

                    <a href="checkout.php" class="btn btn-primary" style="width: 100%; padding: 1rem; text-align: center; text-decoration: none; display: block; margin-bottom: 1rem;">
                        ✓ Procéder au paiement
                    </a>
                    <a href="index.php" class="btn btn-secondary" style="width: 100%; padding: 1rem; text-align: center; text-decoration: none; display: block;">
                        ← Continuer les achats
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
