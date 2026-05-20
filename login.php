<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

session_start();

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $errors[] = "L'email et le mot de passe sont requis";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['role'] = $user['role'];
                
                header("Location: index.php");
                exit;
            } else {
                $errors[] = "Email ou mot de passe incorrect";
            }
        } catch (PDOException $e) {
            $errors[] = "Une erreur s'est produite";
        }
    }
}

if (isset($_SESSION['success'])) {
    $success = $_SESSION['success'];
    unset($_SESSION['success']);
}

include 'includes/header.php';
?>

<section class="login">
    <div style="max-width: 500px; margin: 2rem auto;">
        <h1>Connexion</h1>
        
        <?php if ($success): ?>
            <?php displaySuccess($success); ?>
        <?php endif; ?>
        
        <?php displayErrors($errors); ?>
        
        <form method="POST">
            <div class="form-group">
                <label>Email:</label>
                <input type="email" name="email"  placeholder="exemple@gmail.com" required>
            </div>
            
            <div class="form-group">
                <label>Mot de passe:</label>
                <input type="password" name="password" required>
            </div>
            
            <button type="submit" class="btn btn-primary">Se connecter</button>
        </form>
        
        <p>Pas encore de compte? <a href="register.php">S'inscrire</a></p>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
