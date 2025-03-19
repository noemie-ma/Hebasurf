<?php
require_once 'functions.php';
requireLogin();

if (!isset($_GET['id'])) {
    die("Fichier non spécifié.");
}

$fileId = $_GET['id'];
$files = getFiles();
$targetFile = null;
foreach ($files as $file) {
    if ($file['id'] === $fileId) {
        $targetFile = $file;
        break;
    }
}

if (!$targetFile) {
    die("Fichier introuvable.");
}

$userEmail = $_SESSION['user_email'];
// Seul le propriétaire peut supprimer
if (strtolower($targetFile['owner']) !== strtolower($userEmail)) {
    die("Vous n'êtes pas autorisé à supprimer ce fichier.");
}

// Suppression du fichier physique
$userFolder = generateUserFolder($targetFile['owner']);
$filepath = $userFolder . '/' . $targetFile['stored_name'];
if (file_exists($filepath)) {
    unlink($filepath);
}

// Suppression des métadonnées
deleteFileMetadata($targetFile['id']);

header('Location: index.php');
exit;
?>