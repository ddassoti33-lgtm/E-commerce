<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

session_start();

// Vérifier que l'utilisateur est connecté
requireLogin();

$orders = [];

try {
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->execute([$_SESSION['user_id']]);
    $orders = $stmt->fetchAll();
} catch (PDOException $e) {
    echo "Erreur: " . $e->getMessage();
}

include 'includes/header.php';
?>

<section class="my-orders">
    <h1>Mes Commandes</h1>
    
    <?php if (empty($orders)): ?>
        <p>Vous n'avez pas encore de commande.</p>
        <a href="index.php" class="btn btn-primary">Continuer les achats</a>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Date</th>
                    <th>Montant</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                    <tr>
                        <td><?php echo $order['id']; ?></td>
                        <td><?php echo date('d/m/Y', strtotime($order['created_at'])); ?></td>
                        <td><?php echo number_format($order['total'], 2); ?> €</td>
                        <td><?php echo escape($order['status']); ?></td>
                        <td>
                            <a href="#" class="btn btn-primary">Voir</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</section>

<?php include 'includes/footer.php'; ?>
