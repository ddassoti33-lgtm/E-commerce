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
        header("Location: index.php");
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
?>
