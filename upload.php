<?php
require_once 'header-logged.php';
require_once 'functions.php';
requireLogin();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        $error = "Erreur lors du téléchargement du fichier.";
    } else {
        $file = $_FILES['file'];
        // Vérification de la taille (20 Mo max)
        if ($file['size'] > 20 * 1024 * 1024) {
            $error = "Le fichier dépasse la taille maximale autorisée (20 Mo).";
        } else {
            $originalName = basename($file['name']);
            $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
            // Interdiction des fichiers PHP
            if ($extension === 'php') {
                $error = "Les fichiers PHP ne sont pas autorisés.";
            } else {
                // Traitement éventuel de l’email de réservation (optionnel)
                $reserved_to = isset($_POST['reserved_to']) ? filter_var(trim($_POST['reserved_to']), FILTER_VALIDATE_EMAIL) : null;

                // Génération d’un nom de fichier unique
                $newFileName = uniqid() . '.' . $extension;
                $userEmail = $_SESSION['user_email'];
                $userFolder = generateUserFolder($userEmail);
                if (!file_exists($userFolder)) {
                    mkdir($userFolder, 0755, true);
                }
                $destination = $userFolder . '/' . $newFileName;
                if (move_uploaded_file($file['tmp_name'], $destination)) {
                    $metadata = [
                        'id' => uniqid(),
                        'owner' => $userEmail,
                        'original_name' => $originalName,
                        'stored_name' => $newFileName,
                        'reserved_to' => $reserved_to,
                        'downloads' => 0,
                        'upload_date' => date('Y-m-d H:i:s')
                    ];
                    addFileMetadata($metadata);
                    $success = "Fichier envoyé avec succès.";
                } else {
                    $error = "Erreur lors du déplacement du fichier.";
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Envoyer un fichier</title>
</head>

<body>
    <div class="container">
        <h1>Envoyer un fichier</h1>

        <?php if ($error): ?>
            <p class="error-message"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>

        <?php if ($success): ?>
            <p class="success-message"><?php echo htmlspecialchars($success); ?></p>
        <?php endif; ?>

        <form method="post" enctype="multipart/form-data" class="upload-form">
            <div class="form-group">
                <div class="file-input-wrapper">
                    <label class="file-input-label">
                        📁 Choisir un fichier (max 20 Mo)
                        <input type="file" name="file" required>
                    </label>
                </div>
            </div>

            <div class="form-group">
                <label>Réserver le téléchargement à (email, optionnel):</label>
                <input type="email" name="reserved_to" placeholder="exemple@email.com">
            </div>

            <button type="submit" class="upload-button">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                    class="feather feather-upload">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                    <polyline points="17 8 12 3 7 8"></polyline>
                    <line x1="12" y1="3" x2="12" y2="15"></line>
                </svg>
                Envoyer le fichier
            </button>
        </form>

        <p class="upload-info">Formats autorisés : tous sauf .php • Taille max : 20 Mo</p>

        <a href="index.php" class="back-link">Retour à l'accueil</a>
    </div>
</body>

</html>