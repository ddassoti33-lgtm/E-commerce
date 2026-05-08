<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Vérifier si l'utilisateur est connecté
requireLogin();

$errors = [];
$success = '';

// Traitement des actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    
    if ($action === 'add') {
        // Ajouter au panier
        $product_id = intval($_POST['product_id'] ?? 0);
        $quantity = intval($_POST['quantity'] ?? 1);
        
        if ($quantity > 0) {
            try {
                addToCart($pdo, $_SESSION['user_id'], $product_id, $quantity);
                $success = "Produit ajouté au panier!";
            } catch (Exception $e) {
                $errors[] = "Erreur: " . $e->getMessage();
            }
        }
    } elseif ($action === 'remove') {
        // Supprimer du panier
        $cart_id = intval($_POST['cart_id'] ?? 0);
        try {
            removeFromCart($pdo, $cart_id);
            $success = "Produit retiré du panier.";
        } catch (Exception $e) {
            $errors[] = "Erreur: " . $e->getMessage();
        }
    }
}

// Récupérer le panier
$cart_items = getCart($pdo, $_SESSION['user_id']);

include 'includes/header.php';
?>

<section class="cart">
    <h1>Panier</h1>
    
    <?php displayErrors($errors); ?>
    <?php if ($success) displaySuccess($success); ?>
    
    <?php if (empty($cart_items)): ?>
        <div style="text-align: center; padding: 3rem;">
            <p style="color: #999; font-size: 1.1rem; margin-bottom: 1rem;">Votre panier est vide.</p>
            <a href="index.php" class="btn btn-primary">Continuer les achats</a>
        </div>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Produit</th>
                    <th>Prix</th>
                    <th>Quantité</th>
                    <th>Total</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php $total = 0; ?>
                <?php foreach ($cart_items as $item): ?>
                    <?php $item_total = $item['prix'] * $item['quantity']; ?>
                    <?php $total += $item_total; ?>
                    <tr>
                        <td><?php echo escape($item['nom']); ?></td>
                        <td><?php echo formatPrice($item['prix']); ?></td>
                        <td><?php echo $item['quantity']; ?></td>
                        <td><?php echo formatPrice($item_total); ?></td>
                        <td>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="action" value="remove">
                                <input type="hidden" name="cart_id" value="<?php echo $item['id']; ?>">
                                <button type="submit" class="btn btn-danger btn-small">Supprimer</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <div class="cart-summary">
            <h2>Résumé</h2>
            <div class="summary-line">
                <span>Sous-total:</span>
                <strong><?php echo number_format($total, 2, ',', ' '); ?> DZD</strong>
            </div>
            <div class="summary-line total-line">
                <span>Total:</span>
                <strong><?php echo number_format($total, 2, ',', ' '); ?> DZD</strong>
            </div>
            <a href="checkout.php" class="btn btn-primary btn-large">Procéder au paiement</a>
            <a href="index.php" class="btn btn-secondary btn-large">Continuer les achats</a>
        </div>
    <?php endif; ?>
</section>

<style>
.cart {
    padding: 2rem 0;
}

.cart h1 {
    margin-bottom: 2rem;
    color: #1a1a2e;
}

.cart table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 2rem;
    background: white;
}

.cart table thead {
    background-color: #f5f5f5;
}

.cart table th,
.cart table td {
    padding: 1rem;
    text-align: left;
    border-bottom: 1px solid #eee;
}

.cart table th {
    font-weight: 600;
    color: #1a1a2e;
}

.btn-small {
    padding: 0.5rem 1rem;
    font-size: 0.9rem;
}

.cart-summary {
    background-color: #f9f9f9;
    padding: 2rem;
    border-radius: 8px;
    max-width: 400px;
    margin-left: auto;
}

.cart-summary h2 {
    margin-bottom: 1.5rem;
    color: #1a1a2e;
}

.summary-line {
    display: flex;
    justify-content: space-between;
    padding: 0.75rem 0;
    border-bottom: 1px solid #ddd;
}

.summary-line.total-line {
    border-bottom: 2px solid #00d4ff;
    font-size: 1.2rem;
    font-weight: bold;
    margin: 1rem 0;
    padding: 1rem 0;
}

.btn-large {
    display: block;
    width: 100%;
    margin-top: 1rem;
    text-align: center;
}
</style>

<?php include 'includes/footer.php'; ?>
