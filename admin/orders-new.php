<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Vérifier que l'utilisateur est admin
requireAdmin();

$errors = [];
$success = '';

// Traitement du formulaire pour le changement de statut
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'update_status') {
        $order_id = intval($_POST['order_id'] ?? 0);
        $status = $_POST['status'] ?? '';
        
        if (!empty($status)) {
            try {
                updateOrderStatus($pdo, $order_id, $status);
                $success = "Statut de la commande mis à jour!";
            } catch (Exception $e) {
                $errors[] = "Erreur lors de la mise à jour: " . $e->getMessage();
            }
        }
    }
}

// Récupérer toutes les commandes
$orders = getAllOrders($pdo);

include 'header-admin.php';
?>

<section class="admin-orders">
    <h1>Gestion des Commandes</h1>
    
    <?php displayErrors($errors); ?>
    <?php if ($success) displaySuccess($success); ?>
    
    <div class="table-section">
        <?php if (empty($orders)): ?>
            <p style="color: #999; text-align: center; padding: 2rem;">Aucune commande trouvée.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Client</th>
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
                            <td><?php echo escape($order['nom']); ?></td>
                            <td><?php echo date('d/m/Y', strtotime($order['created_at'])); ?></td>
                            <td><?php echo formatPrice($order['total']); ?></td>
                            <td>
                                <form method="POST" style="display: flex; gap: 0.5rem;">
                                    <input type="hidden" name="action" value="update_status">
                                    <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                    <select name="status" onchange="this.form.submit()">
                                        <option value="pending" <?php echo $order['status'] === 'pending' ? 'selected' : ''; ?>>En attente</option>
                                        <option value="confirmed" <?php echo $order['status'] === 'confirmed' ? 'selected' : ''; ?>>Confirmée</option>
                                        <option value="shipped" <?php echo $order['status'] === 'shipped' ? 'selected' : ''; ?>>Expédiée</option>
                                        <option value="delivered" <?php echo $order['status'] === 'delivered' ? 'selected' : ''; ?>>Livrée</option>
                                        <option value="cancelled" <?php echo $order['status'] === 'cancelled' ? 'selected' : ''; ?>>Annulée</option>
                                    </select>
                                </form>
                            </td>
                            <td>
                                <a href="?view=<?php echo $order['id']; ?>" class="btn btn-primary btn-small">Détails</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
    
    <!-- Détails de la commande -->
    <?php if (isset($_GET['view'])): ?>
        <?php $order = getOrderById($pdo, intval($_GET['view'])); ?>
        <?php if ($order): ?>
            <div class="order-details">
                <h2>Détails de la commande #<?php echo $order['id']; ?></h2>
                <div class="details-grid">
                    <div>
                        <strong>Client:</strong> <?php echo escape($order['nom']); ?>
                    </div>
                    <div>
                        <strong>Email:</strong> <?php echo escape($order['email']); ?>
                    </div>
                    <div>
                        <strong>Date:</strong> <?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?>
                    </div>
                    <div>
                        <strong>Statut:</strong> <span class="status-badge status-<?php echo $order['status']; ?>"><?php echo $order['status']; ?></span>
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
                <div style="text-align: right; margin-top: 1rem; font-size: 1.2rem; font-weight: bold;">
                    Total: <?php echo formatPrice($order['total']); ?>
                </div>
                
                <a href="?" class="btn btn-secondary" style="margin-top: 1rem;">Retour</a>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</section>

<style>
.form-section {
    background-color: #f9f9f9;
    padding: 2rem;
    border-radius: 8px;
    margin-bottom: 2rem;
}

.table-section {
    margin-top: 2rem;
}

.btn-small {
    padding: 0.5rem 1rem;
    font-size: 0.9rem;
}

.order-details {
    background-color: #f9f9f9;
    padding: 2rem;
    border-radius: 8px;
    margin-top: 2rem;
}

.details-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1rem;
    background-color: white;
    padding: 1rem;
    border-radius: 4px;
}

.status-badge {
    display: inline-block;
    padding: 0.5rem 1rem;
    border-radius: 4px;
    font-weight: bold;
    color: white;
}

.status-pending {
    background-color: #ffc107;
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

select {
    padding: 0.5rem;
    border-radius: 4px;
    border: 1px solid #e0e0e0;
}
</style>

<?php include 'footer-admin.php'; ?>
