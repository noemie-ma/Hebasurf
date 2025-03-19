<?php
require_once 'header-logged.php';
require_once 'functions.php';

requireLogin();
$userEmail = $_SESSION['user_email'];
$userFiles = getUserFiles($userEmail);
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Mes fichiers</title>
</head>

<body>
    <div class="container">
        <h1>Bienvenue, <?php echo htmlspecialchars($userEmail); ?></h1>
        <div class="user-nav">
            <a href="profil.php">Modifier le profil</a>
            <a href="upload.php">Envoyer un fichier</a>
            <a href="logout.php">Se déconnecter</a>
        </div>

        <h2>Liste des fichiers</h2>

        <?php if (empty($userFiles)): ?>
            <p class="no-files">Aucun fichier envoyé.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Nom original</th>
                        <th>Téléchargements</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($userFiles as $file): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($file['original_name']); ?></td>
                            <td><?php echo intval($file['downloads']); ?></td>
                            <td class="actions">
                                <?php
                                $authorized = (strtolower($file['owner']) === strtolower($userEmail)) || (isset($file['reserved_to']) && strtolower($file['reserved_to']) === strtolower($userEmail));
                                if ($authorized):
                                    ?>
                                    <a href="telecharger.php?id=<?php echo urlencode($file['id']); ?>">Télécharger</a>
                                <?php else: ?>
                                    <span>Non autorisé</span>
                                <?php endif; ?>
                                <?php if (strtolower($file['owner']) === strtolower($userEmail)): ?>
                                    <a href="delete.php?id=<?php echo urlencode($file['id']); ?>" class="delete-link"
                                        onclick="return confirm('Confirmer la suppression ?');">Supprimer</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>


    </div>
    <?php require_once 'footer.php'; ?>