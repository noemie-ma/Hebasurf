<?php
require_once 'functions.php';

$title = 'Créer un compte';
require_once 'header.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL);
    $password = trim($_POST['password']);
    $confirm = trim($_POST['confirm']);

    if (!$email) {
        $error = "Adresse email invalide.";
    } elseif ($password !== $confirm) {
        $error = "Les mots de passe ne correspondent pas.";
    } else {
        if (findUserByEmail($email)) {
            $error = "Cette adresse email est déjà utilisée.";
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $pdo = get_db_connection();
            $stmt = $pdo->prepare("INSERT INTO users (email, password) VALUES (?, ?)");
            $stmt->execute([strtolower($email), $hashed]);

            header('Location: login.php?success=1');
            exit;
        }
    }
}
?>
<div class="container">
    <h1>Créer un compte</h1>
    <?php if ($error): ?>
        <p class="error-message"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>
    <form method="post">
        <div class="form-group">
            <label>Email:</label>
            <input type="email" name="email" required>
        </div>
        <div class="form-group">
            <label>Mot de passe:</label>
            <input type="password" name="password" required>
        </div>
        <div class="form-group">
            <label>Confirmer mot de passe:</label>
            <input type="password" name="confirm" required>
        </div>
        <button type="submit">Créer un compte</button>
    </form>
</div>

<?php require_once 'footer.php'; ?>