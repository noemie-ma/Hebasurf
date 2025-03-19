<?php
require_once 'header.php';
require_once 'functions.php';

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
            $users = getUsers();
            $users[] = [
                'email' => $email,
                'password' => $hashed,
                'created_at' => date('Y-m-d H:i:s')
            ];
            saveUsers($users);
            // Création du dossier d’uploads de l’utilisateur
            $userFolder = generateUserFolder($email);
            if (!file_exists($userFolder)) {
                mkdir($userFolder, 0755, true);
            }
            header('Location: login.php');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Créer un compte</title>
</head>

<body>
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

</body>