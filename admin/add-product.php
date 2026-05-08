<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

requireAdmin();

$errors = [];
$success = '';
$product = null;
$edit_mode = false;

if (isset($_GET['id'])) {
    $product = getProductById($pdo, $_GET['id']);
    if (!$product) {
        header("Location: products.php");
        exit;
    }
    $edit_mode = true;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $prix = trim($_POST['prix'] ?? '');
    $stock = trim($_POST['stock'] ?? '');
    $category_id = trim($_POST['cat_id'] ?? '');
    $image = '';

    if (empty($nom)) {
        $errors[] = "Le nom du produit est obligatoire.";
    }
    if (empty($prix) || !is_numeric($prix)) {
        $errors[] = "Le prix est obligatoire et doit être un nombre.";
    }
    if (empty($stock) || !is_numeric($stock)) {
        $errors[] = "Le stock est obligatoire et doit être un nombre.";
    }

    // Gestion du téléchargement d'image
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['image']['tmp_name'];
        $file_name = $_FILES['image']['name'];
        $file_size = $_FILES['image']['size'];
        $file_error = $_FILES['image']['error'];

        // Vérifier la taille (max 5MB)
        if ($file_size > 5 * 1024 * 1024) {
            $errors[] = "L'image ne doit pas dépasser 5MB.";
        }

        // Vérifier le type MIME
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $file_type = mime_content_type($file_tmp);
        
        if (!in_array($file_type, $allowed_types)) {
            $errors[] = "Le format d'image n'est pas accepté. Utilisez JPG, PNG, GIF ou WebP.";
        }

        if (empty($errors)) {
            // Créer un nom de fichier unique
            $file_extension = pathinfo($file_name, PATHINFO_EXTENSION);
            $image = 'product_' . time() . '_' . uniqid() . '.' . $file_extension;
            $upload_path = '../assets/images/' . $image;

            // Télécharger le fichier
            if (!move_uploaded_file($file_tmp, $upload_path)) {
                $errors[] = "Erreur lors du téléchargement de l'image.";
            }
        }
    } else if ($edit_mode && $product && isset($product['image'])) {
        // Garder l'image existante en cas de modification sans nouvelle image
        $image = $product['image'];
    }

    if (empty($errors)) {
        try {
            if ($edit_mode) {
                if (!empty($image)) {
                    $stmt = $pdo->prepare("UPDATE products SET nom = ?, description = ?, prix = ?, stock = ?, cat_id = ?, image = ? WHERE id = ?");
                    $stmt->execute([$nom, $description, $prix, $stock, $category_id ?: null, $image, $_GET['id']]);
                } else {
                    $stmt = $pdo->prepare("UPDATE products SET nom = ?, description = ?, prix = ?, stock = ?, cat_id = ? WHERE id = ?");
                    $stmt->execute([$nom, $description, $prix, $stock, $category_id ?: null, $_GET['id']]);
                }
                $success = "Produit modifié avec succès !";
                header("Location: products.php?success=" . urlencode($success));
                exit;
            } else {
                $stmt = $pdo->prepare("INSERT INTO products (nom, description, prix, stock, cat_id, image) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$nom, $description, $prix, $stock, $category_id ?: null, $image]);
                $success = "Produit ajouté avec succès !";
                header("Location: products.php?success=" . urlencode($success));
                exit;
            }
        } catch (PDOException $e) {
            $errors[] = "Erreur lors de l'enregistrement : " . $e->getMessage();
        }
    }
}

$categories = getAllCategories($pdo);

include 'header-admin.php';
?>

<div style="max-width: 700px; margin: 0 auto;">
    <h1><?php echo $edit_mode ? 'Modifier le produit' : 'Ajouter un produit'; ?></h1>
    
    <?php displayErrors($errors); ?>
    
    <form method="POST" class="admin-form" enctype="multipart/form-data">
        <div class="form-group">
            <label for="nom">Nom du produit *</label>
            <input type="text" id="nom" name="nom" required value="<?php echo $product ? escape($product['nom']) : ''; ?>" placeholder="ex: Laptop Gaming">
        </div>

        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description" placeholder="Description détaillée du produit..."><?php echo $product ? escape($product['description'] ?? '') : ''; ?></textarea>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div class="form-group">
                <label for="prix">Prix (Fcfa) *</label>
                <input type="number" id="prix" name="prix" required min="0" value="<?php echo $product ? escape($product['prix']) : ''; ?>" placeholder="0 ">
            </div>

            <div class="form-group">
                <label for="stock">Stock *</label>
                <input type="number" id="stock" name="stock" required min="0" value="<?php echo $product ? escape($product['stock']) : ''; ?>" placeholder="0">
            </div>
        </div>

        <div class="form-group">
            <label for="cat_id">Catégorie</label>
            <select id="cat_id" name="cat_id">
                <option value="">-- Sélectionner une catégorie --</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo $cat['id']; ?>" <?php echo ($product && $product['cat_id'] == $cat['id']) ? 'selected' : ''; ?>>
                        <?php echo escape($cat['nom']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="image">Image du produit</label>
            <?php if ($edit_mode && $product && isset($product['image']) && !empty($product['image'])): ?>
                <div style="margin-bottom: 1rem;">
                    <p style="font-size: 0.9rem; color: #666;">Image actuelle :</p>
                    <img src="../assets/images/<?php echo escape($product['image']); ?>" alt="<?php echo escape($product['nom']); ?>" style="max-width: 150px; border-radius: 4px;">
                </div>
            <?php endif; ?>
            <input type="file" id="image" name="image" accept="image/jpeg,image/png,image/gif,image/webp" placeholder="Sélectionner une image">
            <p style="font-size: 0.85rem; color: #666; margin-top: 0.5rem;">Formats acceptés : JPG, PNG, GIF, WebP. Taille max : 5MB</p>
        </div>

        <div class="form-group" style="display: flex; gap: 1rem;">
            <button type="submit" class="btn btn-success"><?php echo $edit_mode ? 'Modifier' : 'Ajouter'; ?></button>
            <a href="products.php" class="btn btn-secondary" style="text-decoration: none;">Annuler</a>
        </div>
    </form>
</div>

<?php include 'footer-admin.php'; ?>
