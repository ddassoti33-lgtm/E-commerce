<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

requireAdmin();

if (!isset($_GET['id']) || !isset($_GET['action'])) {
    header("Location: orders.php");
    exit;
}

$order_id = $_GET['id'];
$action = $_GET['action'];

// Actions valides
$valid_actions = ['complete', 'cancel'];
if (!in_array($action, $valid_actions)) {
    header("Location: orders.php");
    exit;
}

try {
    $status_map = [
        'complete' => 'completed',
        'cancel' => 'cancelled'
    ];

    $new_status = $status_map[$action];

    $stmt = $pdo->prepare("UPDATE orders SET statut = ? WHERE id = ?");
    $stmt->execute([$new_status, $order_id]);

    $messages = [
        'complete' => 'Commande marquée comme complétée',
        'cancel' => 'Commande annulée'
    ];

    header("Location: orders.php?success=" . urlencode($messages[$action]));
    exit;
} catch (PDOException $e) {
    header("Location: orders.php?error=" . urlencode("Erreur lors de la mise à jour"));
    exit;
}
?>
