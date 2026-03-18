<?php
require_once 'header-logged.php';
require_once 'functions.php';
requireLogin();

$error = '';
$success = '';
$currentEmail = $_SESSION['user_email'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newEmail = filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL);
    $password = trim($_POST['password']);
    $confirm = trim($_POST['confirm']);

    // Modification de l'email si différent
    if ($newEmail && strtolower($newEmail) !== strtolower($currentEmail)) {
        if (findUserByEmail($newEmail)) {
            $error = "Cette adresse email est déjà utilisée.";
        } else {
            // Mise à jour de l’utilisateur et déplacement du dossier d’uploads
            $oldFolder = generateUserFolder($currentEmail);
            $newFolder = generateUserFolder($newEmail);
            if (!file_exists($newFolder)) {
                mkdir($newFolder, 0755, true);
            }
            if (file_exists($oldFolder)) {
                foreach (scandir($oldFolder) as $file) {
                    if ($file === '.' || $file === '..')
                        continue;
                    rename($oldFolder . '/' . $file, $newFolder . '/' . $file);
                }
                rmdir($oldFolder);
            }
            updateUser($currentEmail, ['email' => $newEmail]);

            // Mise à jour des métadonnées de fichiers (propriété et réservation)
            $files = getFiles();
            foreach ($files as &$file) {
                if (strtolower($file['owner']) === strtolower($currentEmail)) {
                    $file['owner'] = $newEmail;
                }
                if (isset($file['reserved_to']) && strtolower($file['reserved_to']) === strtolower($currentEmail)) {
                    $file['reserved_to'] = $newEmail;
                }
            }
            saveFiles($files);

            $_SESSION['user_email'] = $newEmail;
            $currentEmail = $newEmail;
            $success = "Email mis à jour avec succès.";
        }
    }

    // Mise à jour du mot de passe s'il est fourni
    if ($password) {
        if ($password !== $confirm) {
            $error = "Les mots de passe ne correspondent pas.";
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            updateUser($currentEmail, ['password' => $hashed]);
            $success = "Mot de passe mis à jour.";
        }
    }
}
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Modifier le profil</title>
</head>

<body>
    <div class="container">
        <h1>Modifier le profil</h1>
        <p class="current-email">Email actuel: <?php echo htmlspecialchars($currentEmail); ?></p>

        <?php if ($error): ?>
            <p class="error-message"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>

        <?php if ($success): ?>
            <p class="success-message"><?php echo htmlspecialchars($success); ?></p>
        <?php endif; ?>

        <form method="post">
            <div class="form-group">
                <label>Nouvel email:</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($currentEmail); ?>" required>
            </div>

            <div class="form-group">
                <label>Nouveau mot de passe (laisser vide pour ne pas changer):</label>
                <input type="password" name="password">
            </div>

            <div class="form-group">
                <label>Confirmer mot de passe:</label>
                <input type="password" name="confirm">
            </div>

            <button type="submit">Mettre à jour</button>
        </form>

        <a href="index.php" class="back-link">Retour</a>
    </div>
</body>

</html>