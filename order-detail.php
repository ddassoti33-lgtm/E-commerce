<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

requireLogin();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: my-orders.php");
    exit;
}

$order = null;
$items = [];
$error = '';
$order_number = 0;

try {
    // Récupérer la commande
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
    $stmt->execute([$_GET['id'], $_SESSION['user_id']]);
    $order = $stmt->fetch();

    if (!$order) {
        header("Location: my-orders.php");
        exit;
    }

    // Calculer le numéro séquentiel de la commande
    $count_stmt = $pdo->prepare("SELECT COUNT(*) as count FROM orders WHERE user_id = ? AND created_at >= ? ORDER BY created_at DESC");
    $count_stmt->execute([$_SESSION['user_id'], $order['created_at']]);
    $count_result = $count_stmt->fetch();
    $order_number = $count_result['count'];

    // Récupérer les articles de la commande
    $items_stmt = $pdo->prepare("
        SELECT oi.id, oi.quantite, oi.prix_unit as prix, p.nom, p.id as product_id 
        FROM order_items oi 
        JOIN products p ON oi.product_id = p.id 
        WHERE oi.order_id = ?
    ");
    $items_stmt->execute([$_GET['id']]);
    $items = $items_stmt->fetchAll();

    // Récupérer l'historique des statuts (si la table existe)
    $history = [];
    try {
        $history_stmt = $pdo->prepare("
            SELECT ancien_statut, nouveau_statut, changed_at 
            FROM order_status_history 
            WHERE order_id = ? 
            ORDER BY changed_at DESC
        ");
        $history_stmt->execute([$_GET['id']]);
        $history = $history_stmt->fetchAll();
    } catch (PDOException $e) {
        // La table peut ne pas exister, c'est normal
        $history = [];
    }

} catch (PDOException $e) {
    $error = "Erreur lors du chargement de la commande.";
}

include 'includes/header.php';
?>


<section class="order-detail">
    <div class="container">
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger" style="margin-bottom: 2rem;">
                <p><?php echo htmlspecialchars($error); ?></p>
                <a href="my-orders.php" class="btn btn-secondary" style="text-decoration: none; margin-top: 1rem;">← Retour à mes commandes</a>
            </div>
        <?php elseif ($order): ?>
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <h1 style="color: #1a1a2e;">Commande #<?php echo $order_number; ?></h1>
            <a href="my-orders.php" class="btn btn-secondary" style="text-decoration: none;">← Retour</a>
        </div>

        <!-- Informations de la commande -->
        <div style="background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); margin-bottom: 2rem;">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
                <div>
                    <h3 style="color: #1a1a2e; margin-bottom: 1rem;">📋 Informations Commande</h3>
                    <p><strong>Date:</strong> <?php echo date('d/m/Y H:i:s', strtotime($order['created_at'])); ?></p>
                    <p><strong>Statut:</strong> 
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
                    </p>
                </div>
                
                <div>
                    <h3 style="color: #1a1a2e; margin-bottom: 1rem;">💰 Montant</h3>
                    <p style="font-size: 1.5rem; color: #00d4ff; font-weight: bold;">
                        <?php echo formatPrice($order['total']); ?>
                    </p>
                </div>
            </div>
        </div>

        <!-- Articles de la commande -->
        <div style="background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
            <h2 style="color: #1a1a2e; margin-top: 0;">📦 Articles commandés</h2>
            
            <?php if (empty($items)): ?>
                <p style="color: #666;">Aucun article trouvé.</p>
            <?php else: ?>
                <table style="width: 100%;">
                    <thead style="background-color: #f5f5f5; border-bottom: 2px solid #ddd;">
                        <tr>
                            <th style="padding: 1rem; text-align: left;">Produit</th>
                            <th style="padding: 1rem; text-align: center;">Quantité</th>
                            <th style="padding: 1rem; text-align: right;">Prix unitaire</th>
                            <th style="padding: 1rem; text-align: right;">Sous-total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item): ?>
                            <tr style="border-bottom: 1px solid #eee;">
                                <td style="padding: 1rem;">
                                    <a href="product.php?id=<?php echo $item['product_id']; ?>" style="color: #667eea; text-decoration: none; font-weight: 500;">
                                        <?php echo escape($item['nom']); ?>
                                    </a>
                                </td>
                                <td style="padding: 1rem; text-align: center;"><?php echo $item['quantite']; ?></td>
                                <td style="padding: 1rem; text-align: right;"><?php echo formatPrice($item['prix']); ?></td>
                                <td style="padding: 1rem; text-align: right; font-weight: bold; color: #00d4ff;">
                                    <?php echo formatPrice($item['prix'] * $item['quantite']); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>

            <!-- Total -->
            <div style="text-align: right; padding-top: 1.5rem; border-top: 2px solid #eee; margin-top: 1.5rem;">
                <div style="display: flex; justify-content: flex-end; align-items: center; gap: 2rem;">
                    <h3 style="color: #1a1a2e; margin: 0;">Montant Total:</h3>
                    <span style="font-size: 1.8rem; color: #00d4ff; font-weight: bold;">
                        <?php echo formatPrice($order['total']); ?>
                    </span>
                </div>
            </div>
        </div>

        <!-- Historique des statuts -->
        <?php if (!empty($history)): ?>
        <div style="background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); margin-top: 2rem;">
            <h2 style="color: #1a1a2e; margin-top: 0;">📜 Historique de votre Commande</h2>
            
            <div style="border-left: 3px solid #667eea; padding-left: 1.5rem;">
                <?php 
                $statuts = [
                    'en_attente' => ['label' => '⏳ En attente', 'color' => '#fff3cd', 'text' => '#856404'],
                    'payee' => ['label' => '✓ Payée', 'color' => '#cfe2ff', 'text' => '#084298'],
                    'expediee' => ['label' => '🚚 Expédiée', 'color' => '#d1ecf1', 'text' => '#0c5460'],
                    'livree' => ['label' => '✓ Livrée', 'color' => '#d4edda', 'text' => '#155724'],
                    'annulee' => ['label' => '✗ Annulée', 'color' => '#f8d7da', 'text' => '#721c24'],
                ];
                foreach ($history as $entry): 
                    $from = $statuts[$entry['ancien_statut']] ?? ['label' => $entry['ancien_statut'], 'color' => '#e9ecef', 'text' => '#666'];
                    $to = $statuts[$entry['nouveau_statut']] ?? ['label' => $entry['nouveau_statut'], 'color' => '#e9ecef', 'text' => '#666'];
                ?>
                    <div style="margin-bottom: 1.5rem; position: relative;">
                        <div style="position: absolute; left: -1.95rem; top: 0; width: 1rem; height: 1rem; background: #667eea; border-radius: 50%; border: 3px solid white;"></div>
                        
                        <p style="margin: 0; font-size: 0.9rem; color: #666;">
                            <?php echo date('d/m/Y H:i:s', strtotime($entry['changed_at'])); ?>
                        </p>
                        
                        <p style="margin: 0.5rem 0; font-weight: 500;">
                            <span style="padding: 0.2rem 0.6rem; border-radius: 3px; background-color: <?php echo $from['color']; ?>; color: <?php echo $from['text']; ?>; font-size: 0.85rem;">
                                <?php echo $from['label']; ?>
                            </span>
                            →
                            <span style="padding: 0.2rem 0.6rem; border-radius: 3px; background-color: <?php echo $to['color']; ?>; color: <?php echo $to['text']; ?>; font-size: 0.85rem;">
                                <?php echo $to['label']; ?>
                            </span>
                        </p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <div style="text-align: center; margin-top: 2rem;">
            <a href="index.php" class="btn btn-primary" style="text-decoration: none;">← Continuer les achats</a>
        </div>
        <?php else: ?>
            <div class="alert alert-danger" style="margin-bottom: 2rem;">
                <p>Commande non trouvée.</p>
                <a href="my-orders.php" class="btn btn-secondary" style="text-decoration: none; margin-top: 1rem;">← Retour à mes commandes</a>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
