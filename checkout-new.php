<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Vérifier si l'utilisateur est connecté
requireLogin();

$errors = [];
$success = '';

// Récupérer le panier
$cart_items = getCart($pdo, $_SESSION['user_id']);

// Traitement du paiement
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'checkout') {
    if (empty($cart_items)) {
        $errors[] = "Votre panier est vide!";
    } else {
        try {
            // Calculer le total
            $total = 0;
            foreach ($cart_items as $item) {
                $total += $item['prix'] * $item['quantity'];
            }
            
            // Créer la commande
            $stmt = $pdo->prepare("INSERT INTO orders (user_id, total, status) VALUES (?, ?, 'pending')");
            $stmt->execute([$_SESSION['user_id'], $total]);
            $order_id = $pdo->lastInsertId();
            
            // Ajouter les articles à la commande
            foreach ($cart_items as $item) {
                $stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
                $stmt->execute([$order_id, $item['product_id'], $item['quantity'], $item['prix']]);
                
                // Réduire le stock
                $stmt = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
                $stmt->execute([$item['quantity'], $item['product_id']]);
            }
            
            // Vider le panier
            clearCart($pdo, $_SESSION['user_id']);
            
            $success = "Commande créée avec succès! Numéro de commande: #" . $order_id;
            $cart_items = [];
        } catch (Exception $e) {
            $errors[] = "Erreur lors du traitement de la commande: " . $e->getMessage();
        }
    }
}

include 'includes/header.php';
?>

<section class="checkout">
    <h1>Paiement</h1>
    
    <?php displayErrors($errors); ?>
    <?php if ($success) displaySuccess($success); ?>
    
    <?php if (!empty($cart_items)): ?>
        <div class="checkout-container">
            <div class="checkout-items">
                <h2>Résumé de votre commande</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Produit</th>
                            <th>Quantité</th>
                            <th>Prix</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $total = 0; ?>
                        <?php foreach ($cart_items as $item): ?>
                            <?php $item_total = $item['prix'] * $item['quantity']; ?>
                            <?php $total += $item_total; ?>
                            <tr>
                                <td><?php echo escape($item['nom']); ?></td>
                                <td><?php echo $item['quantity']; ?></td>
                                <td><?php echo formatPrice($item['prix']); ?></td>
                                <td><?php echo formatPrice($item_total); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="checkout-form">
                <h2>Informations de livraison</h2>
                <form method="POST" class="admin-form">
                    <div class="form-group">
                        <label>Nom complet</label>
                        <input type="text" value="<?php echo escape($_SESSION['nom'] ?? ''); ?>" disabled>
                    </div>
                    
                    <div class="form-group">
                        <label>Adresse de livraison</label>
                        <input type="text" name="address" placeholder="Entrez votre adresse" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Numéro de téléphone</label>
                        <input type="tel" name="phone" placeholder="Entrez votre numéro" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Wilaya</label>
                        <input type="text" name="wilaya" placeholder="Ex: Alger" required>
                    </div>
                    
                    <div class="order-total">
                        <h3>Montant total:</h3>
                        <p class="total-amount"><?php echo formatPrice($total); ?></p>
                    </div>
                    
                    <input type="hidden" name="action" value="checkout">
                    <button type="submit" class="btn btn-success btn-large">Confirmer la commande</button>
                    <a href="cart.php" class="btn btn-secondary btn-large">Retour au panier</a>
                </form>
            </div>
        </div>
    <?php else: ?>
        <div style="text-align: center; padding: 3rem;">
            <p style="color: #999; font-size: 1.1rem; margin-bottom: 1rem;">Votre panier est vide.</p>
            <a href="index.php" class="btn btn-primary">Continuer les achats</a>
        </div>
    <?php endif; ?>
</section>

<style>
.checkout {
    padding: 2rem 0;
}

.checkout h1 {
    margin-bottom: 2rem;
    color: #1a1a2e;
}

.checkout-container {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 2rem;
}

.checkout-items h2,
.checkout-form h2 {
    margin-bottom: 1.5rem;
    color: #1a1a2e;
}

.checkout-items table {
    width: 100%;
    border-collapse: collapse;
}

.checkout-items table th,
.checkout-items table td {
    padding: 0.75rem;
    text-align: left;
    border-bottom: 1px solid #eee;
}

.checkout-items table th {
    background-color: #f5f5f5;
    font-weight: 600;
}

.checkout-form {
    background-color: #f9f9f9;
    padding: 2rem;
    border-radius: 8px;
}

.order-total {
    background-color: white;
    padding: 1.5rem;
    border-radius: 4px;
    margin: 1.5rem 0;
    text-align: center;
    border-left: 4px solid #00d4ff;
}

.order-total h3 {
    margin: 0 0 0.5rem 0;
    color: #666;
}

.total-amount {
    font-size: 1.8rem;
    color: #00d4ff;
    font-weight: bold;
    margin: 0;
}

.btn-large {
    display: block;
    width: 100%;
    margin-top: 1rem;
    text-align: center;
}

@media (max-width: 768px) {
    .checkout-container {
        grid-template-columns: 1fr;
    }
}
</style>

<?php include 'includes/footer.php'; ?>
