<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

session_start();

$product = null;
$product_id = $_GET['id'] ?? null;

if ($product_id) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->execute([$product_id]);
        $product = $stmt->fetch();
    } catch (PDOException $e) {
        echo "Erreur: " . $e->getMessage();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $product) {
    // Ajouter au panier
    $quantity = $_POST['quantity'] ?? 1;
    
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    
    if (isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart'][$product_id] += $quantity;
    } else {
        $_SESSION['cart'][$product_id] = $quantity;
    }
}

include 'includes/header.php';
?>

<?php if ($product): ?>
    <section class="product-detail">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
            <div>
                <img src="assets/images/placeholder.jpg" alt="<?php echo escape($product['name']); ?>" style="width: 100%;">
            </div>
            
            <div>
                <h1><?php echo escape($product['name']); ?></h1>
                <p><?php echo escape($product['description']); ?></p>
                
                <p class="price" style="font-size: 2rem; color: #e74c3c; margin: 1rem 0;">
                    <?php echo number_format($product['price'], 2); ?> €
                </p>
                
                <p style="color: <?php echo $product['stock'] > 0 ? 'green' : 'red'; ?>;">
                    Stock: <?php echo $product['stock']; ?>
                </p>
                
                <?php if ($product['stock'] > 0): ?>
                    <form method="POST">
                        <div class="form-group">
                            <label>Quantité:</label>
                            <input type="number" name="quantity" value="1" min="1" max="<?php echo $product['stock']; ?>" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Ajouter au panier</button>
                    </form>
                <?php else: ?>
                    <p style="color: red; font-weight: bold;">Rupture de stock</p>
                <?php endif; ?>
            </div>
        </div>
    </section>
<?php else: ?>
    <p>Produit non trouvé</p>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
