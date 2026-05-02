<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

session_start();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';
    $name = $_POST['name'] ?? '';

// Validation
    if (empty($email)) {
        $errors[] = "L'email est requis";
    }
    if (empty($password)) {
        $errors[] = "Le mot de passe est requis";
    }
    if ($password !== $password_confirm) {
        $errors[] = "Les mots de passe ne correspondent pas";
    }
    if (empty($name)) {
        $errors[] = "Le nom est requis";
    }

    // Si pas d'erreur, insérer l'utilisateur
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'user')");
            $stmt->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT)]);
            
            $_SESSION['success'] = "Inscription réussie! Veuillez vous connecter.";
            header("Location: login.php");
            exit;
        } catch (PDOException $e) {
            $errors[] = "Une erreur s'est produite lors de l'inscription";
        }
    }
}

include 'includes/header.php';
?>

<section class="register">
    <div style="max-width: 500px; margin: 2rem auto;">
        <h1>Inscription</h1>
        
        <?php displayErrors($errors); ?>
        
        <form method="POST">
            <div class="form-group">
                <label>Nom:</label>
                <input type="text" name="name" required>
            </div>
            
            <div class="form-group">
                <label>Email:</label>
                <input type="email" name="email" required>
            </div>
            
            <div class="form-group">
                <label>Mot de passe:</label>
                <input type="password" name="password" required>
            </div>
            
            <div class="form-group">
                <label>Confirmer le mot de passe:</label>
                <input type="password" name="password_confirm" required>
            </div>
            
            <button type="submit" class="btn btn-primary">S'inscrire</button>
        </form>
        
        <p>Vous avez déjà un compte? <a href="login.php">Se connecter</a></p>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
