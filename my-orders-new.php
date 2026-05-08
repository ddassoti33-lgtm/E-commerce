<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Vérifier si l'utilisateur est connecté
requireLogin();

// Récupérer toutes les commandes de l'utilisateur
$stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$orders = $stmt->fetchAll();

include 'includes/header.php';
?>

<section class="my-orders">
    <h1>Mes Commandes</h1>
    
    <?php if (empty($orders)): ?>
        <div style="text-align: center; padding: 3rem;">
            <p style="color: #999; font-size: 1.1rem; margin-bottom: 1rem;">Vous n'avez pas encore de commandes.</p>
            <a href="index.php" class="btn btn-primary">Commencer vos achats</a>
        </div>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Numéro</th>
                    <th>Date</th>
                    <th>Montant</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                    <tr>
                        <td>#<?php echo $order['id']; ?></td>
                        <td><?php echo date('d/m/Y', strtotime($order['created_at'])); ?></td>
                        <td><?php echo formatPrice($order['total']); ?></td>
                        <td>
                            <span class="status-badge status-<?php echo $order['status']; ?>">
                                <?php 
                                    $statuses = [
                                        'pending' => 'En attente',
                                        'confirmed' => 'Confirmée',
                                        'shipped' => 'Expédiée',
                                        'delivered' => 'Livrée',
                                        'cancelled' => 'Annulée'
                                    ];
                                    echo $statuses[$order['status']] ?? $order['status'];
                                ?>
                            </span>
                        </td>
                        <td>
                            <a href="?view=<?php echo $order['id']; ?>" class="btn btn-primary btn-small">Détails</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <!-- Détails de la commande -->
        <?php if (isset($_GET['view'])): ?>
            <?php
            $order_id = intval($_GET['view']);
            $order = getOrderById($pdo, $order_id);
            if ($order && $order['user_id'] == $_SESSION['user_id']):
            ?>
                <div class="order-details">
                    <h2>Détails de la commande #<?php echo $order['id']; ?></h2>
                    <div class="details-grid">
                        <div>
                            <strong>Date:</strong> <?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?>
                        </div>
                        <div>
                            <strong>Statut:</strong> <span class="status-badge status-<?php echo $order['status']; ?>">
                                <?php 
                                    $statuses = [
                                        'pending' => 'En attente',
                                        'confirmed' => 'Confirmée',
                                        'shipped' => 'Expédiée',
                                        'delivered' => 'Livrée',
                                        'cancelled' => 'Annulée'
                                    ];
                                    echo $statuses[$order['status']] ?? $order['status'];
                                ?>
                            </span>
                        </div>
                        <div>
                            <strong>Montant total:</strong> <?php echo formatPrice($order['total']); ?>
                        </div>
                    </div>
                    
                    <h3 style="margin-top: 2rem;">Produits commandés</h3>
                    <?php $items = getOrderItems($pdo, $order['id']); ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Produit</th>
                                <th>Quantité</th>
                                <th>Prix unitaire</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $item): ?>
                                <tr>
                                    <td><?php echo escape($item['nom']); ?></td>
                                    <td><?php echo $item['quantity']; ?></td>
                                    <td><?php echo formatPrice($item['price']); ?></td>
                                    <td><?php echo formatPrice($item['price'] * $item['quantity']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    
                    <a href="?" class="btn btn-secondary" style="margin-top: 1rem;">Retour</a>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    <?php endif; ?>
</section>

<style>
.my-orders {
    padding: 2rem 0;
}

.my-orders h1 {
    margin-bottom: 2rem;
    color: #1a1a2e;
}

.my-orders table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 2rem;
    background: white;
}

.my-orders table thead {
    background-color: #f5f5f5;
}

.my-orders table th,
.my-orders table td {
    padding: 1rem;
    text-align: left;
    border-bottom: 1px solid #eee;
}

.my-orders table th {
    font-weight: 600;
    color: #1a1a2e;
}

.btn-small {
    padding: 0.5rem 1rem;
    font-size: 0.9rem;
}

.status-badge {
    display: inline-block;
    padding: 0.5rem 1rem;
    border-radius: 4px;
    font-weight: bold;
    color: white;
    font-size: 0.85rem;
}

.status-pending {
    background-color: #ffc107;
    color: #333;
}

.status-confirmed {
    background-color: #17a2b8;
}

.status-shipped {
    background-color: #007bff;
}

.status-delivered {
    background-color: #28a745;
}

.status-cancelled {
    background-color: #dc3545;
}

.order-details {
    background-color: #f9f9f9;
    padding: 2rem;
    border-radius: 8px;
    margin-top: 2rem;
}

.order-details h2 {
    color: #1a1a2e;
    margin-bottom: 1rem;
}

.details-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1rem;
    background-color: white;
    padding: 1rem;
    border-radius: 4px;
    margin-bottom: 1rem;
}

.order-details table {
    width: 100%;
    border-collapse: collapse;
    background: white;
    margin-top: 1rem;
}

.order-details table th,
.order-details table td {
    padding: 0.75rem;
    text-align: left;
    border-bottom: 1px solid #eee;
}

.order-details table th {
    background-color: #f5f5f5;
    font-weight: 600;
}
</style>

<?php include 'includes/footer.php'; ?>
