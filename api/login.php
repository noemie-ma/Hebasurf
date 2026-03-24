<?php
require_once 'functions.php';

$title = 'Se connecter';
require_once 'header.php';
if (session_status() === PHP_SESSION_NONE)
    session_start();

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL);
    $password = trim($_POST['password']);

    $user = findUserByEmail($email);
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_email'] = $user['email'];
        header('Location: index.php');
        exit;
    } else {
        $error = "Identifiants incorrects.";
    }
}
?>
<div class="container">
    <h1>Se connecter</h1>
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
        <button type="submit">Se connecter</button>
    </form>

</div>
<?php require_once 'footer.php'; ?>