<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

include 'includes/header.php';

requireLogin();

$orders = [];

try {
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->execute([$_SESSION['user_id']]);
    $orders = $stmt->fetchAll();
} catch (PDOException $e) {
    $orders = [];
}
?>

<section class="my-orders">
    <div class="container">
        <h1 style="color: #1a1a2e; margin-bottom: 2rem;">📋 Mes Commandes</h1>
        
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success"><?php echo escape($_GET['success']); ?></div>
        <?php endif; ?>
        
        <?php if (empty($orders)): ?>
            <div style="text-align: center; padding: 3rem 0;">
                <p style="font-size: 3rem; margin: 1rem 0;">📦</p>
                <h2 style="color: #666;">Vous n'avez pas encore de commande</h2>
                <p style="color: #999; margin-bottom: 2rem;">Découvrez nos produits et passez votre première commande</p>
                <a href="index.php" class="btn btn-primary" style="text-decoration: none;">Continuer les achats →</a>
            </div>
        <?php else: ?>
            <div style="background: white; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); overflow: hidden;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead style="background-color: #f5f5f5; border-bottom: 2px solid #ddd;">
                        <tr>
                            
                            <th style="padding: 1.5rem; text-align: left;">Date</th>
                            <th style="padding: 1.5rem; text-align: right;">Montant</th>
                            <th style="padding: 1.5rem; text-align: center;">Statut</th>
                            <th style="padding: 1.5rem; text-align: center;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $order_number = 1; ?>
                        <?php foreach ($orders as $order): ?>
                            <tr style="border-bottom: 1px solid #eee;">
                                <td style="padding: 1.5rem;">
                                    <?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?>
                                </td>
                                <td style="padding: 1.5rem; text-align: right; font-weight: bold; color: #00d4ff;">
                                    <?php echo formatPrice($order['total']); ?>
                                </td>
                                <td style="padding: 1.5rem; text-align: center;">
                                    <span style="
                                        padding: 0.4rem 0.8rem;
                                        border-radius: 4px;
                                        font-size: 0.85rem;
                                        font-weight: bold;
                                        <?php 
                                        if ($order['statut'] === 'livree') {
                                            echo 'background-color: #d4edda; color: #155724;';
                                        } elseif ($order['statut'] === 'en_attente') {
                                            echo 'background-color: #fff3cd; color: #856404;';
                                        } elseif ($order['statut'] === 'expediee') {
                                            echo 'background-color: #d1ecf1; color: #0c5460;';
                                        } elseif ($order['statut'] === 'payee') {
                                            echo 'background-color: #cfe2ff; color: #084298;';
                                        } elseif ($order['statut'] === 'annulee') {
                                            echo 'background-color: #f8d7da; color: #721c24;';
                                        }
                                        ?>
                                    ">
                                        <?php 
                                        $statuts = [
                                            'en_attente' => '⏳ En attente',
                                            'expediee' => '🚚 Expédiée',
                                            'livree' => '✓ Livrée',
                                            'annulee' => '✗ Annulée'
                                        ];
                                        echo $statuts[$order['statut']] ?? escape($order['statut']);
                                        ?>
                                    </span>
                                </td>
                                <td style="padding: 1.5rem; text-align: center;">
                                    <a href="order-detail.php?id=<?php echo $order['id']; ?>" style="display: inline-block; padding: 0.7rem 1.5rem; background-color: #667eea; color: white; text-decoration: none; border-radius: 4px; font-weight: 500; transition: background 0.3s;">
                                        👁️ Voir détails
                                    </a>
                                </td>
                            </tr>
                            <?php $order_number++; ?>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
