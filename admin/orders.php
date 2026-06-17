<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

requireAdmin();

include 'header-admin.php';

// Traiter la modification du statut
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id']) && isset($_POST['statut'])) {
    $order_id = intval($_POST['order_id']);
    $new_status = trim($_POST['statut']);
    $statuts_valides = ['en_attente', 'payee', 'expediee', 'livree', 'annulee'];
    
    if (in_array($new_status, $statuts_valides)) {
        try {
            $stmt = $pdo->prepare("UPDATE orders SET statut = ? WHERE id = ?");
            $stmt->execute([$new_status, $order_id]);
            header("Location: orders.php?success=" . urlencode("Statut mis à jour !"));
            exit;
        } catch (PDOException $e) {
            $error = "Erreur : " . $e->getMessage();
        }
    }
}

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
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                <select name="statut" onchange="this.form.submit();" style="padding: 0.5rem; border: 1px solid #ddd; border-radius: 4px; font-weight: bold;" <?php echo $order['statut'] === 'livree'  ? 'disabled' : ''; ?>>
                                    <option value="en_attente" <?php echo $order['statut'] === 'en_attente' ? 'selected' : ''; ?>>⏳ En attente</option>
                                    <option value="expediee" <?php echo $order['statut'] === 'expediee' ? 'selected' : ''; ?>>🚚 Expédiée</option>
                                    <option value="livree" <?php echo $order['statut'] === 'livree' ? 'selected' : ''; ?>>✓ Livrée</option>
                                    <option value="annulee" <?php echo $order['statut'] === 'annulee' ? 'selected' : ''; ?>>✗ Annulée</option>
                                </select>
                            </form>
                        </td>
                        <td>
                            <button type="button" onclick="window.location.href='order-details.php?id=<?php echo $order['id']; ?>';" style="padding: 0.7rem 1.2rem; font-size: 0.9rem; text-decoration: none; background: #667eea; color: white; border-radius: 4px; display: inline-block; font-weight: 500; border: none; cursor: pointer;">👁️ Voir détails</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php include 'footer-admin.php'; ?>
