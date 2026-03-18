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
// Vérifier l'autorisation : propriétaire ou réservé à l'utilisateur
if (!(strtolower($targetFile['owner']) === strtolower($userEmail) || (isset($targetFile['reserved_to']) && strtolower($targetFile['reserved_to']) === strtolower($userEmail)))) {
    die("Accès non autorisé.");
}

// Recherche du fichier sur le serveur
$userFolder = generateUserFolder($targetFile['owner']);
$filepath = $userFolder . '/' . $targetFile['stored_name'];
if (!file_exists($filepath)) {
    die("Fichier introuvable sur le serveur.");
}

// Incrémentation du compteur
updateFileMetadata($targetFile['id'], ['downloads' => $targetFile['downloads'] + 1]);

// Envoi des headers et du fichier
header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . basename($targetFile['original_name']) . '"');
header('Content-Length: ' . filesize($filepath));
readfile($filepath);
exit;
?>