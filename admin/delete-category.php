<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

requireAdmin();

if (!isset($_GET['id'])) {
    header("Location: categories.php");
    exit;
}

try {
    $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    header("Location: categories.php?success=" . urlencode("Catégorie supprimée avec succès !"));
    exit;
} catch (PDOException $e) {
    header("Location: categories.php?error=" . urlencode("Erreur lors de la suppression"));
    exit;
}
?>
