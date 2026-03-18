<?php
session_start();

define('USERS_FILE', __DIR__ . '/data/users.json');
define('FILES_FILE', __DIR__ . '/data/files.json');
define('UPLOADS_DIR', __DIR__ . '/uploads');

// Création des dossiers de données et d’uploads si nécessaire
if (!file_exists(__DIR__ . '/data')) {
    mkdir(__DIR__ . '/data', 0755, true);
}
if (!file_exists(UPLOADS_DIR)) {
    mkdir(UPLOADS_DIR, 0755, true);
}

// Fonctions de gestion des utilisateurs
function getUsers()
{
    if (!file_exists(USERS_FILE)) {
        return [];
    }
    $json = file_get_contents(USERS_FILE);
    $users = json_decode($json, true);
    return is_array($users) ? $users : [];
}

function saveUsers($users)
{
    file_put_contents(USERS_FILE, json_encode($users, JSON_PRETTY_PRINT));
}

function findUserByEmail($email)
{
    $users = getUsers();
    foreach ($users as $user) {
        if (strtolower($user['email']) === strtolower($email)) {
            return $user;
        }
    }
    return null;
}

function updateUser($email, $newData)
{
    $users = getUsers();
    foreach ($users as &$user) {
        if (strtolower($user['email']) === strtolower($email)) {
            $user = array_merge($user, $newData);
            saveUsers($users);
            return true;
        }
    }
    return false;
}

// Fonctions de gestion des métadonnées de fichiers
function getFiles()
{
    if (!file_exists(FILES_FILE)) {
        return [];
    }
    $json = file_get_contents(FILES_FILE);
    $files = json_decode($json, true);
    return is_array($files) ? $files : [];
}

function saveFiles($files)
{
    file_put_contents(FILES_FILE, json_encode($files, JSON_PRETTY_PRINT));
}

function addFileMetadata($metadata)
{
    $files = getFiles();
    $files[] = $metadata;
    saveFiles($files);
}

function updateFileMetadata($fileId, $newData)
{
    $files = getFiles();
    foreach ($files as &$file) {
        if ($file['id'] === $fileId) {
            $file = array_merge($file, $newData);
            saveFiles($files);
            return true;
        }
    }
    return false;
}

function deleteFileMetadata($fileId)
{
    $files = getFiles();
    foreach ($files as $index => $file) {
        if ($file['id'] === $fileId) {
            array_splice($files, $index, 1);
            saveFiles($files);
            return $file;
        }
    }
    return false;
}

function getUserFiles($email)
{
    $files = getFiles();
    $result = [];
    foreach ($files as $file) {
        // On affiche les fichiers dont l'utilisateur est propriétaire ou pour lesquels il est le destinataire réservé
        if (strtolower($file['owner']) === strtolower($email) || (isset($file['reserved_to']) && strtolower($file['reserved_to']) === strtolower($email))) {
            $result[] = $file;
        }
    }
    return $result;
}

function generateUserFolder($email)
{
    return UPLOADS_DIR . '/' . md5(strtolower($email));
}

function isLoggedIn()
{
    return isset($_SESSION['user_email']);
}

function requireLogin()
{
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}
?>