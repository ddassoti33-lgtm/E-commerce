<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

requireAdmin();

if (!isset($_GET['id'])) {
    header("Location: orders.php");
    exit;
}

$order_id = intval($_GET['id']);

// Traiter la modification du statut
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['statut'])) {
    $new_statut = $_POST['statut'];
    
    $valid_statuts = ['pending', 'sending', 'completed', 'cancelled'];
    if (!in_array($new_statut, $valid_statuts)) {
        header("Location: orders.php?error=" . urlencode("Statut invalide"));
        exit;
    }

    try {
        $stmt = $pdo->prepare("UPDATE orders SET statut = ? WHERE id = ?");
        $stmt->execute([$new_statut, $order_id]);
        
        $messages = [
            'pending' => '⏳ Passée à: En attente',
            'sending' => '🚚 Passée à: En cours de livraison',
            'completed' => '✓ Passée à: Complétée',
            'cancelled' => '✗ Passée à: Annulée'
        ];

        header("Location: orders.php?success=" . urlencode("Commande " . $messages[$new_statut]));
        exit;
    } catch (PDOException $e) {
        header("Location: orders.php?error=" . urlencode("Erreur lors de la mise à jour"));
        exit;
    }
}

// Récupérer la commande
try {
    $stmt = $pdo->prepare("SELECT o.*, u.nom, u.email FROM orders o LEFT JOIN users u ON o.user_id = u.id WHERE o.id = ?");
    $stmt->execute([$order_id]);
    $order = $stmt->fetch();

    if (!$order) {
        header("Location: orders.php");
        exit;
    }

    // Récupérer les articles de la commande
    $items_stmt = $pdo->prepare("SELECT oi.*, p.nom FROM order_items oi LEFT JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?");
    $items_stmt->execute([$order_id]);
    $items = $items_stmt->fetchAll();
} catch (PDOException $e) {
    header("Location: orders.php");
    exit;
}

include 'header-admin.php';
?>

<div class="order-update">
    <a href="orders.php" style="color: #667eea; text-decoration: none; margin-bottom: 1rem; display: inline-block;">← Retour</a>
    
    <div style="background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); margin-bottom: 2rem;">
        <h1>Modifier le statut de la commande #<?php echo $order['id']; ?></h1>
        
        <div style="background: #f9f9f9; padding: 1.5rem; border-radius: 5px; margin-bottom: 2rem; border-left: 4px solid #667eea;">
            <p style="margin: 0.5rem 0;"><strong>Client:</strong> <?php echo escape($order['nom']); ?></p>
            <p style="margin: 0.5rem 0;"><strong>Email:</strong> <?php echo escape($order['email']); ?></p>
            <p style="margin: 0.5rem 0;"><strong>Total:</strong> <span style="color: #00d4ff; font-weight: bold;"><?php echo formatPrice($order['total']); ?></span></p>
            <p style="margin: 0.5rem 0;"><strong>Date:</strong> <?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></p>
        </div>

        <h2>Articles de la commande</h2>
        <table style="width: 100%; border-collapse: collapse; margin-bottom: 2rem;">
            <thead style="background: #f5f5f5; border-bottom: 2px solid #ddd;">
                <tr>
                    <th style="padding: 0.75rem; text-align: left;">Produit</th>
                    <th style="padding: 0.75rem; text-align: center;">Quantité</th>
                    <th style="padding: 0.75rem; text-align: right;">Prix unitaire</th>
                    <th style="padding: 0.75rem; text-align: right;">Total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                    <tr style="border-bottom: 1px solid #eee;">
                        <td style="padding: 0.75rem;"><?php echo escape($item['nom']); ?></td>
                        <td style="padding: 0.75rem; text-align: center;"><?php echo $item['quantite'] ?? $item['quantity'] ?? 1; ?></td>
                        <td style="padding: 0.75rem; text-align: right;"><?php echo formatPrice($item['prix_unit'] ?? $item['price'] ?? 0); ?></td>
                        <td style="padding: 0.75rem; text-align: right; font-weight: bold;"><?php echo formatPrice(($item['prix_unit'] ?? $item['price'] ?? 0) * ($item['quantite'] ?? $item['quantity'] ?? 1)); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <h2>Changer le statut</h2>
        <form method="POST">
            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1rem; align-items: end;">
                <div>
                    <label for="statut" style="display: block; margin-bottom: 0.5rem; font-weight: bold;">Nouveau statut:</label>
                    <select name="statut" id="statut" required style="width: 100%; padding: 0.75rem; border: 2px solid #ddd; border-radius: 5px; font-size: 1rem;">
                        <option value="">-- Sélectionner un statut --</option>
                        <option value="pending" <?php echo $order['statut'] === 'pending' ? 'selected disabled' : ''; ?>>
                            ⏳ En attente
                        </option>
                        <option value="sending" <?php echo $order['statut'] === 'sending' ? 'selected disabled' : ''; ?>>
                            🚚 En cours de livraison
                        </option>
                        <option value="completed" <?php echo $order['statut'] === 'completed' ? 'selected disabled' : ''; ?>>
                            ✓ Complétée
                        </option>
                        <option value="cancelled" <?php echo $order['statut'] === 'cancelled' ? 'selected disabled' : ''; ?>>
                            ✗ Annulée
                        </option>
                    </select>
                    <p style="font-size: 0.9rem; color: #666; margin-top: 0.5rem;">
                        Statut actuel: <strong>
                            <?php 
                            $statuts = [
                                'pending' => '⏳ En attente',
                                'sending' => '🚚 En cours de livraison',
                                'completed' => '✓ Complétée',
                                'cancelled' => '✗ Annulée'
                            ];
                            echo $statuts[$order['statut']] ?? $order['statut'];
                            ?>
                        </strong>
                    </p>
                </div>
                <button type="submit" class="btn btn-primary" style="padding: 0.75rem 2rem; font-weight: bold;">
                    ✓ Mettre à jour
                </button>
            </div>
        </form>
    </div>
</div>

<?php include 'footer-admin.php'; ?>

