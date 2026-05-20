<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

requireAdmin();

include 'header-admin.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: orders.php");
    exit;
}

try {
    // Récupérer la commande
    $stmt = $pdo->prepare("
        SELECT o.id, o.user_id, o.total, o.statut, o.created_at, u.nom, u.email 
        FROM orders o 
        LEFT JOIN users u ON o.user_id = u.id 
        WHERE o.id = ?
    ");
    $stmt->execute([$_GET['id']]);
    $order = $stmt->fetch();

    if (!$order) {
        header("Location: orders.php");
        exit;
    }

    // Récupérer les articles de la commande
    $items_stmt = $pdo->prepare("
        SELECT oi.id, oi.quantite, oi.prix, p.nom, p.id as product_id 
        FROM order_items oi 
        JOIN products p ON oi.product_id = p.id 
        WHERE oi.order_id = ?
    ");
    $items_stmt->execute([$_GET['id']]);
    $items = $items_stmt->fetchAll();

} catch (PDOException $e) {
    header("Location: orders.php");
    exit;
}
?>

<div style="max-width: 900px; margin: 0 auto;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h1>Détails de la Commande #<?php echo escape($order['id']); ?></h1>
        <a href="orders.php" class="btn btn-secondary">← Retour</a>
    </div>

    <!-- Informations de la commande -->
    <div style="background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); margin-bottom: 2rem;">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
            <div>
                <h3 style="color: #1a1a2e; margin-bottom: 1rem;">Informations Client</h3>
                <p><strong>Nom:</strong> <?php echo escape($order['nom'] ?? 'N/A'); ?></p>
                <p><strong>Email:</strong> <?php echo escape($order['email'] ?? 'N/A'); ?></p>
                <p><strong>ID Client:</strong> #<?php echo escape($order['user_id']); ?></p>
            </div>
            
            <div>
                <h3 style="color: #1a1a2e; margin-bottom: 1rem;">Informations Commande</h3>
                <p><strong>Date:</strong> <?php echo date('d/m/Y H:i:s', strtotime($order['created_at'])); ?></p>
                <p><strong>Statut:</strong> 
                    <span style="
                        padding: 0.4rem 0.8rem;
                        border-radius: 4px;
                        font-size: 0.9rem;
                        font-weight: bold;
                        <?php 
                        if ($order['statut'] === 'completed') {
                            echo 'background-color: #d4edda; color: #155724;';
                        } elseif ($order['statut'] === 'pending') {
                            echo 'background-color: #fff3cd; color: #856404;';
                        } elseif ($order['statut'] === 'sending') {
                            echo 'background-color: #cfe2ff; color: #084298;';
                        } elseif ($order['statut'] === 'cancelled') {
                            echo 'background-color: #f8d7da; color: #721c24;';
                        }
                        ?>
                    ">
                        <?php 
                        $statuts = [
                            'pending' => '⏳ En attente',
                            'sending' => '🚚 En cours de livraison',
                            'completed' => '✓ Complétée',
                            'cancelled' => '✗ Annulée'
                        ];
                        echo $statuts[$order['statut']] ?? $order['statut'];
                        ?>
                    </span>
                </p>
            </div>
        </div>
    </div>

    <!-- Articles de la commande -->
    <div style="background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); margin-bottom: 2rem;">
        <h3 style="color: #1a1a2e; margin-bottom: 1rem;">Articles Commandés</h3>
        
        <?php if (empty($items)): ?>
            <p style="color: #666;">Aucun article trouvé.</p>
        <?php else: ?>
            <table style="width: 100%;">
                <thead>
                    <tr style="background-color: #f5f5f5; border-bottom: 2px solid #ddd;">
                        <th style="padding: 0.75rem; text-align: left;">Produit</th>
                        <th style="padding: 0.75rem; text-align: center;">Quantité</th>
                        <th style="padding: 0.75rem; text-align: right;">Prix unitaire</th>
                        <th style="padding: 0.75rem; text-align: right;">Sous-total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                        <tr style="border-bottom: 1px solid #eee;">
                            <td style="padding: 0.75rem;">
                                <a href="../product.php?id=<?php echo $item['product_id']; ?>" style="color: #667eea; text-decoration: none;">
                                    <?php echo escape($item['nom']); ?>
                                </a>
                            </td>
                            <td style="padding: 0.75rem; text-align: center;"><?php echo $item['quantite']; ?></td>
                            <td style="padding: 0.75rem; text-align: right;"><?php echo number_format($item['prix'], 2, ',', ' '); ?> €</td>
                            <td style="padding: 0.75rem; text-align: right; font-weight: bold;">
                                <?php echo number_format($item['prix'] * $item['quantite'], 2, ',', ' '); ?> €
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <!-- Total et actions -->
    <div style="background: #f5f5f5; padding: 2rem; border-radius: 8px;">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <h3 style="color: #1a1a2e; margin: 0;">Montant Total</h3>
            <span style="font-size: 1.8rem; color: #00d4ff; font-weight: bold;">
                <?php echo number_format($order['total'], 2, ',', ' '); ?> €
            </span>
        </div>
    </div>
</div>

<?php include 'footer-admin.php'; ?>
