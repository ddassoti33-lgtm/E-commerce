<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

requireAdmin();

if (!isset($_GET['id'])) {
    header("Location: products.php");
    exit;
}

try {
    $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    header("Location: products.php?success=" . urlencode("Produit supprimé avec succès !"));
    exit;
} catch (PDOException $e) {
    header("Location: products.php?error=" . urlencode("Erreur lors de la suppression"));
    exit;
}
?>
