<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

requireAdmin();

include 'header-admin.php';

// Statistiques
$total_products = $pdo->query("SELECT COUNT(*) as count FROM products")->fetch()['count'];
$total_categories = $pdo->query("SELECT COUNT(*) as count FROM categories")->fetch()['count'];
$total_orders = $pdo->query("SELECT COUNT(*) as count FROM orders")->fetch()['count'];
$total_revenue = $pdo->query("SELECT SUM(total) as sum FROM orders")->fetch()['sum'] ?? 0;
?>

<div class="admin-dashboard">
    <h1>Dashboard Admin</h1>
    
    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 1.5rem; margin: 2rem 0;">
        <!-- Statistiques -->
        <div style="background: white; padding: 1.5rem; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); text-align: center;">
            <div style="font-size: 2.5rem; color: #667eea; font-weight: bold;">📦</div>
            <p style="color: #666; margin: 0.5rem 0;">Produits</p>
            <h2 style="color: #1a1a2e; margin: 0;"><?php echo $total_products; ?></h2>
        </div>
        
        <div style="background: white; padding: 1.5rem; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); text-align: center;">
            <div style="font-size: 2.5rem; color: #667eea; font-weight: bold;">🏷️</div>
            <p style="color: #666; margin: 0.5rem 0;">Catégories</p>
            <h2 style="color: #1a1a2e; margin: 0;"><?php echo $total_categories; ?></h2>
        </div>
        
        <div style="background: white; padding: 1.5rem; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); text-align: center;">
            <div style="font-size: 2.5rem; color: #667eea; font-weight: bold;">📋</div>
            <p style="color: #666; margin: 0.5rem 0;">Commandes</p>
            <h2 style="color: #1a1a2e; margin: 0;"><?php echo $total_orders; ?></h2>
        </div>
        
        <div style="background: white; padding: 1.5rem; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); text-align: center;">
            <div style="font-size: 2.5rem; color: #00d4ff; font-weight: bold;">💰</div>
            <p style="color: #666; margin: 0.5rem 0;">Chiffre d'affaires</p>
            <h2 style="color: #00d4ff; margin: 0;"><?php echo number_format($total_revenue, 2, ',', ' '); ?> Fcfa</h2>
        </div>
    </div>
    
    <div class="dashboard-grid" style="margin-top: 2rem;">
        <div class="dashboard-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
            <h3>👕 Gestion des Produits</h3>
            <p>Ajouter, modifier ou supprimer des produits</p>
            <a href="products.php" class="btn btn-primary">Accéder →</a>
        </div>
        
        <div class="dashboard-card" style="background: linear-gradient(135deg, #764ba2 0%, #f093fb 100%);">
            <h3>🏷️ Gestion des Catégories</h3>
            <p>Organiser les catégories de produits</p>
            <a href="categories.php" class="btn btn-primary">Accéder →</a>
        </div>
        
        <div class="dashboard-card" style="background: linear-gradient(135deg, #f093fb 0%, #4facfe 100%);">
            <h3>📦 Gestion des Commandes</h3>
            <p>Voir et gérer toutes les commandes</p>
            <a href="orders.php" class="btn btn-primary">Accéder →</a>
        </div>
    </div>
</div>

<?php include 'footer-admin.php'; ?>
