<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

requireAdmin();

$errors = [];
$success = '';
$category = null;
$edit_mode = false;

if (isset($_GET['id'])) {
    $category = getCategoryById($pdo, $_GET['id']);
    if (!$category) {
        header("Location: categories.php");
        exit;
    }
    $edit_mode = true;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if (empty($nom)) {
        $errors[] = "Le nom de la catégorie est obligatoire.";
    }

    if (empty($errors)) {
        try {
            if ($edit_mode) {
                $stmt = $pdo->prepare("UPDATE categories SET nom = ?, description = ? WHERE id = ?");
                $stmt->execute([$nom, $description, $_GET['id']]);
                $success = "Catégorie modifiée avec succès !";
                header("Location: categories.php?success=" . urlencode($success));
                exit;
            } else {
                $stmt = $pdo->prepare("INSERT INTO categories (nom, description) VALUES (?, ?)");
                $stmt->execute([$nom, $description]);
                $success = "Catégorie ajoutée avec succès !";
                header("Location: categories.php?success=" . urlencode($success));
                exit;
            }
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'UNIQUE constraint failed') !== false || strpos($e->getMessage(), 'Duplicate entry') !== false) {
                $errors[] = "Cette catégorie existe déjà.";
            } else {
                $errors[] = "Erreur lors de l'enregistrement : " . $e->getMessage();
            }
        }
    }
}

include 'header-admin.php';
?>

<div style="max-width: 600px; margin: 0 auto;">
    <h1><?php echo $edit_mode ? 'Modifier la catégorie' : 'Ajouter une catégorie'; ?></h1>
    
    <?php displayErrors($errors); ?>
    
    <form m.ethod="POST" class="admin-form">
        <div class="form-group">
            <label for="nom">Nom de la catégorie *</label>
            <input type="text" id="nom" name="nom" required value="<?php echo $category ? escape($category['nom']) : ''; ?>" placeholder="ex: Électronique">
        </div>

        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description" placeholder="Description de la catégorie..."><?php echo $category ? escape($category['description'] ?? '') : ''; ?></textarea>
        </div>

        <div class="form-group" style="display: flex; gap: 1rem;">
            <button type="submit" class="btn btn-success"><?php echo $edit_mode ? 'Modifier' : 'Ajouter'; ?></button>
            <a href="categories.php" class="btn btn-secondary" style="text-decoration: none;">Annuler</a>
        </div>
    </form>
</div>

<?php include 'footer-admin.php'; ?>
