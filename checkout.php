<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

include 'includes/header.php';

requireLogin();

$errors = [];
$success = '';

// Vérifier que le panier n'est pas vide
if (empty($_SESSION['cart'])) {
    header("Location: cart.php");
    exit;
}

// Traiter la commande
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $address = trim($_POST['address'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $zip = trim($_POST['zip'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $payment_method = trim($_POST['payment_method'] ?? '');

    if (empty($address)) $errors[] = "L'adresse est obligatoire.";
    if (empty($city)) $errors[] = "La ville est obligatoire.";
    if (empty($zip)) $errors[] = "Le code postal est obligatoire.";
    if (empty($phone)) $errors[] = "Le téléphone est obligatoire.";
    if (empty($payment_method)) $errors[] = "La méthode de paiement est obligatoire.";

    if (empty($errors)) {
        try {
            // Calculer le total
            $total = 0;
            foreach ($_SESSION['cart'] as $product_id => $quantity) {
                $product = getProductById($pdo, $product_id);
                if ($product) {
                    $total += $product['prix'] * $quantity;
                }
            }

            // Créer la commande
            $stmt = $pdo->prepare("INSERT INTO orders (user_id, total, statut, adresse) VALUES (?, ?, 'en_attente', ?)");
            $stmt->execute([$_SESSION['user_id'], $total, $address]);
            $order_id = $pdo->lastInsertId();

            // Ajouter les articles de la commande
            foreach ($_SESSION['cart'] as $product_id => $quantity) {
                $product = getProductById($pdo, $product_id);
                if ($product) {
                    $item_stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantite, prix_unit) VALUES (?, ?, ?, ?)");
                    $item_stmt->execute([$order_id, $product_id, $quantity, $product['prix']]);

                    // Réduire le stock
                    $update_stmt = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
                    $update_stmt->execute([$quantity, $product_id]);
                }
            }

            // Vider le panier
            $_SESSION['cart'] = [];

            header("Location: my-orders.php?success=" . urlencode("Commande créée avec succès ! Numéro : #" . $order_id));
            exit;

        } catch (PDOException $e) {
            $errors[] = "Erreur lors de la création de la commande : " . $e->getMessage();
        }
    }
}

// Récupérer le total du panier
$total = 0;
foreach ($_SESSION['cart'] as $product_id => $quantity) {
    $product = getProductById($pdo, $product_id);
    if ($product) {
        $total += $product['prix'] * $quantity;
    }
}

// Récupérer les infos utilisateur
$user_stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$user_stmt->execute([$_SESSION['user_id']]);
$user = $user_stmt->fetch();
?>

<section class="checkout">
    <div class="container">
        <h1 style="color: #1a1a2e; margin-bottom: 2rem;">📋 Finaliser votre commande</h1>
        
        <?php displayErrors($errors); ?>
        
        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem;">
            <!-- Formulaire de commande -->
            <div>
                <form method="POST">
                    <div style="background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); margin-bottom: 2rem;">
                        <h2 style="color: #1a1a2e; margin-top: 0;">📍 Adresse de livraison</h2>
                        
                        <div class="form-group">
                            <label for="address">Adresse complète *</label>
                            <input type="text" id="address" name="address" required value="<?php echo $user ? escape($user['adresse'] ?? '') : ''; ?>" placeholder="agoe-legbassito, non loin de ..">
                        </div>

                        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1rem;">
                            <div class="form-group">
                                <label for="city">Ville *</label>
                                <input type="text" id="city" name="city" required value="<?php echo $user ? escape($user['ville'] ?? '') : ''; ?>" placeholder="ex: lomé">
                            </div>

                            <div class="form-group">
                                <label for="zip">Code postal *</label>
                                <input type="text" id="zip" name="zip" required value="<?php echo $user ? escape($user['code_postal'] ?? '') : ''; ?>" placeholder="ex: 18 BP 231">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="phone">Téléphone *</label>
                            <input type="tel" id="phone" name="phone" required value="<?php echo $user ? escape($user['telephone'] ?? '') : ''; ?>" placeholder="ex: +228 90 22 14 52">
                        </div>
                    </div>

                    <div style="background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                        <h2 style="color: #1a1a2e; margin-top: 0;">💳 Méthode de paiement</h2>
                        
                        <div class="form-group">
                            <label style="display: flex; align-items: center; padding: 1rem; border: 2px solid #ddd; border-radius: 5px; cursor: pointer; margin-bottom: 1rem;">
                                <input type="radio" name="payment_method" value="card" required style="margin-right: 0.5rem;">
                                <span><strong>Carte bancaire</strong></span>
                            </label>

                            <label style="display: flex; align-items: center; padding: 1rem; border: 2px solid #ddd; border-radius: 5px; cursor: pointer;">
                                <input type="radio" name="payment_method" value="bank_transfer" required style="margin-right: 0.5rem;">
                                <span><strong>Virement bancaire</strong></span>
                            </label>
                            <label style="display: flex; align-items: center; padding: 1rem; border: 2px solid #ddd; border-radius: 5px; cursor: pointer;">
                                <input type="radio" name="payment_method" value="bank_transfer" required style="margin-right: 0.5rem;">
                                <span><strong>Paiement mobile</strong></span>
                            </label>
                        </div>

                        <button type="submit" class="btn btn-success" style="width: 100%; padding: 1rem; font-size: 1.1rem; font-weight: bold;">
                            ✓ Valider la commande
                        </button>
                    </div>
                </form>
            </div>

            <!-- Résumé de la commande -->
            <div style="background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); height: fit-content;">
                <h2 style="color: #1a1a2e; margin-top: 0;">📦 Résumé de la commande</h2>
                
                <div style="border-bottom: 2px solid #eee; padding-bottom: 1.5rem; margin-bottom: 1.5rem;">
                    <?php foreach ($_SESSION['cart'] as $product_id => $quantity): ?>
                        <?php $product = getProductById($pdo, $product_id); ?>
                        <?php if ($product): ?>
                            <div style="display: flex; justify-content: space-between; margin-bottom: 1rem; font-size: 0.9rem;">
                                <div>
                                    <p style="margin: 0; font-weight: 500;"><?php echo escape($product['nom']); ?></p>
                                    <p style="margin: 0.2rem 0 0 0; color: #666;">× <?php echo $quantity; ?></p>
                                </div>
                                <span style="font-weight: bold;">
                                    <?php echo number_format($product['prix'] * $quantity, 2, ',', ' '); ?> Fcfa
                                </span>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>

                <div style="padding: 1.5rem 0; border-bottom: 2px solid #eee; margin-bottom: 1.5rem;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.8rem;">
                        <span>Sous-total:</span>
                        <span><?php echo number_format($total, 2, ',', ' '); ?> Fcfa</span>
                    </div>
                    <div style="display: flex; justify-content: space-between;">
                        <span>Frais de port:</span>
                        <span style="color: #51cf66; font-weight: bold;">Gratuit</span>
                    </div>
                </div>

                <div style="display: flex; justify-content: space-between; font-size: 1.3rem; font-weight: bold; color: #00d4ff;">
                    <span>TOTAL:</span>
                    <span><?php echo number_format($total, 2, ',', ' '); ?> Fcfa</span>
                </div>

                <a href="cart.php" style="display: block; text-align: center; margin-top: 1.5rem; color: #667eea; text-decoration: none; font-size: 0.9rem;">
                    ← Modifier le panier
                </a>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
