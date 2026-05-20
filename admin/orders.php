<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

requireAdmin();

include 'header-admin.php';

// Récupérer toutes les commandes avec les infos utilisateur
$orders = [];
try {
    $stmt = $pdo->query("
        SELECT o.id, o.user_id, o.total, o.statut, o.created_at, u.nom, u.email 
        FROM orders o 
        LEFT JOIN users u ON o.user_id = u.id 
        ORDER BY o.created_at DESC
    ");
    $orders = $stmt->fetchAll();
} catch (PDOException $e) {
    // Si la table n'existe pas ou erreur
    $orders = [];
}
?>

<div class="admin-orders">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h1>Gestion des Commandes</h1>
        <?php if (!empty($orders)): ?>
            <span style="background: #667eea; color: white; padding: 0.5rem 1rem; border-radius: 5px; font-weight: bold;">
                Total: <?php echo count($orders); ?> commande<?php echo count($orders) > 1 ? 's' : ''; ?>
            </span>
        <?php endif; ?>
    </div>
    
    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success"><?php echo escape($_GET['success']); ?></div>
    <?php endif; ?>
    
    <?php if (empty($orders)): ?>
        <div class="alert alert-info">
            Aucune commande trouvée. Les commandes apparaîtront ici une fois que les clients commenceront à acheter.
        </div>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    
                    <th>Client</th>
                    <th>Email</th>
                    <th>Date</th>
                    <th>Montant</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                    <tr>
                        
                        <td><?php echo escape($order['nom'] ?? 'Utilisateur #' . $order['user_id']); ?></td>
                        <td><?php echo escape($order['email'] ?? 'N/A'); ?></td>
                        <td><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></td>
                        <td style="font-weight: bold; color: #00d4ff;">
                            <?php echo number_format($order['total'], 2, ',', ' '); ?> Fcfa
                        </td>
                        <td>
                            <span style="
                                padding: 0.4rem 0.8rem;
                                border-radius: 4px;
                                font-size: 0.85rem;
                                font-weight: bold;
                                <?php 
                                if ($order['statut'] === 'completed') {
                                    echo 'background-color: #d4edda; color: #155724;';
                                } elseif ($order['statut'] === 'pending') {
                                    echo 'background-color: #fff3cd; color: #856404;';
                                } 
                                elseif ($order['statut'] === 'sending') {
                                    echo 'background-color: #f8d7da; color: #1c5a72;';
                                }   
                                elseif ($order['statut'] === 'cancelled') {
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
                        </td>
                        <td>
                            <a href="order-details.php?id=<?php echo $order['id']; ?>" class="btn btn-secondary" style="padding: 0.5rem 1rem; font-size: 0.85rem; text-decoration: none; background: #6c757d; color: white; border-radius: 4px;">👁️ Détails</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php include 'footer-admin.php'; ?>
