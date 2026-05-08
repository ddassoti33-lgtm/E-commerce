<?php
// Fonctions utiles

// Vérifier si l'utilisateur est connecté
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Vérifier si l'utilisateur est admin
function isAdmin() {
    return isLoggedIn() && $_SESSION['role'] === 'admin';
}

// Rediriger vers la page de connexion si non connecté
function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: login.php");
        exit;
    }
}

// Rediriger vers l'accueil si pas admin
function requireAdmin() {
    if (!isAdmin()) {
        header("Location: ../index.php");
        exit;
    }
}

// Afficher les erreurs
function displayErrors($errors) {
    if (!empty($errors)) {
        echo '<div class="alert alert-danger">';
        foreach ($errors as $error) {
            echo '<p>' . htmlspecialchars($error) . '</p>';
        }
        echo '</div>';
    }
}

// Afficher les messages de succès
function displaySuccess($message) {
    echo '<div class="alert alert-success">';
    echo '<p>' . htmlspecialchars($message) . '</p>';
    echo '</div>';
}

// Échapper les données HTML
function escape($data) {
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

// Formater le prix en FCFA sans décimales
function formatPrice($price) {
    return number_format(intval($price), 0, ',', ' ') . ' Fcfa';
}

// Obtenir tous les produits
function getAllProducts($pdo) {
    $stmt = $pdo->query("SELECT p.*, c.nom as category_nom FROM products p LEFT JOIN categories c ON p.cat_id = c.id ");
    return $stmt->fetchAll();
}

// Obtenir tous les catégories
function getAllCategories($pdo) {
    $stmt = $pdo->query("SELECT * FROM categories ORDER BY nom ASC");
    return $stmt->fetchAll();
}

// Obtenir un produit par ID
function getProductById($pdo, $id) {
    $stmt = $pdo->prepare("SELECT p.*, c.nom as category_nom FROM products p LEFT JOIN categories c ON p.cat_id = c.id WHERE p.id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

// Obtenir une catégorie par ID
function getCategoryById($pdo, $id) {
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

// Ajouter un produit
function addProduct($pdo, $nom, $description, $prix, $stock, $image, $category_id) {
    $stmt = $pdo->prepare("INSERT INTO products (nom, description, prix, stock,image, cat_id) VALUES (?, ?, ?, ?, ?)");
    return $stmt->execute([$nom, $description, $prix, $stock, $image, $category_id]);
}

// Mettre à jour un produit
function updateProduct($pdo, $id, $nom, $description, $prix, $stock, $category_id) {
    $stmt = $pdo->prepare("UPDATE products SET nom = ?, description = ?, prix = ?, stock = ?, category_id = ? WHERE id = ?");
    return $stmt->execute([$nom, $description, $prix, $stock, $category_id, $id]);
}

// Supprimer un produit
function deleteProduct($pdo, $id) {
    $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
    return $stmt->execute([$id]);
}

// Ajouter une catégorie
function addCategory($pdo, $nom, $description) {
    $stmt = $pdo->prepare("INSERT INTO categories (nom, description) VALUES (?, ?)");
    return $stmt->execute([$nom, $description]);
}

// Mettre à jour une catégorie
function updateCategory($pdo, $id, $nom, $description) {
    $stmt = $pdo->prepare("UPDATE categories SET nom = ?, description = ? WHERE id = ?");
    return $stmt->execute([$nom, $description, $id]);
}

// Supprimer une catégorie
function deleteCategory($pdo, $id) {
    $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
    return $stmt->execute([$id]);
}

// Ajouter au panier
function addToCart($pdo, $user_id, $product_id, $quantity = 1) {
    $stmt = $pdo->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE quantity = quantity + ?");
    return $stmt->execute([$user_id, $product_id, $quantity, $quantity]);
}

// Obtenir le panier de l'utilisateur
function getCart($pdo, $user_id) {
    $stmt = $pdo->prepare("SELECT c.*, p.nom, p.prix, p.image FROM cart c JOIN products p ON c.product_id = p.id WHERE c.user_id = ? ORDER BY c.created_at DESC");
    $stmt->execute([$user_id]);
    return $stmt->fetchAll();
}

// Supprimer du panier
function removeFromCart($pdo, $cart_id) {
    $stmt = $pdo->prepare("DELETE FROM cart WHERE id = ?");
    return $stmt->execute([$cart_id]);
}

// Vider le panier
function clearCart($pdo, $user_id) {
    $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ?");
    return $stmt->execute([$user_id]);
}

// Obtenir tous les commandes
function getAllOrders($pdo) {
    $stmt = $pdo->query("SELECT o.*, u.nom FROM orders o JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC");
    return $stmt->fetchAll();
}

// Obtenir une commande par ID
function getOrderById($pdo, $id) {
    $stmt = $pdo->prepare("SELECT o.*, u.nom, u.email FROM orders o JOIN users u ON o.user_id = u.id WHERE o.id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

// Obtenir les items d'une commande
function getOrderItems($pdo, $order_id) {
    $stmt = $pdo->prepare("SELECT oi.*, p.nom FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?");
    $stmt->execute([$order_id]);
    return $stmt->fetchAll();
}

// Mettre à jour le statut d'une commande
function updateOrderStatus($pdo, $order_id, $status) {
    $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
    return $stmt->execute([$status, $order_id]);
}
?>
