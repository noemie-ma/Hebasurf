<?php
// Definition d'une constantes pour les fichiers enregistrés
// Sur Vercel, le filesystem n'est pas persistant; utiliser le dossier temporaire système
define('UPLOADS_DIR', sys_get_temp_dir());

// Connexion à la database sur Vercel
function get_db_connection()
{
    $host = getenv('POSTGRES_HOST');
    $db = getenv('POSTGRES_DATABASE');
    $user = getenv('POSTGRES_USER');
    $pass = getenv('POSTGRES_PASSWORD');
    $dsn = "pgsql:host=$host;port=5432;dbname=$db;sslmode=require";

    try {
        return new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
    } catch (PDOException $e) {
        die("Erreur de connexion : " . $e->getMessage());
    }
}
// Fonction pour vérifier si l'utilisateur / utilisatrice est connecté
function requireLogin()
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Si l'email n'est pas en session, on redirige vers le login
    if (!isset($_SESSION['user_email'])) {
        header('Location: login.php');
        exit;
    }
}

// Fonction pour trouver l'utilisateur / utilisatrice par email
function findUserByEmail($email)
{
    $pdo = get_db_connection();
    $stmt = $pdo->prepare("SELECT * FROM users WHERE LOWER(email) = ?");
    $stmt->execute([strtolower($email)]);
    return $stmt->fetch();
}

// Récupération des fichiers mis en ligne par l'utilisateur / utilisatrice
function getUserFiles($email)
{
    $pdo = get_db_connection();
    $stmt = $pdo->prepare("SELECT * FROM files WHERE LOWER(owner) = ? OR LOWER(reserved_to) = ? ORDER BY created_at DESC");
    $stmt->execute([strtolower($email), strtolower($email)]);
    $rows = $stmt->fetchAll();
    $out = [];
    foreach ($rows as $r) {
        $out[] = array(
            'id' => (string) $r['id'],
            'filename' => $r['filename'],
            'stored_name' => $r['filename'],
            'original_name' => $r['original_name'],
            'owner' => $r['owner'],
            'reserved_to' => $r['reserved_to'],
            'downloads' => (int) $r['downloads'],
            'created_at' => $r['created_at']
        );
    }
    return $out;
}

// Récupère tous les fichiers
function getFiles()
{
    $pdo = get_db_connection();
    $stmt = $pdo->query("SELECT * FROM files ORDER BY created_at DESC");
    $rows = $stmt->fetchAll();
    $out = [];
    foreach ($rows as $r) {
        $out[] = array(
            'id' => (string) $r['id'],
            'filename' => $r['filename'],
            'stored_name' => $r['filename'],
            'original_name' => $r['original_name'],
            'owner' => $r['owner'],
            'reserved_to' => $r['reserved_to'],
            'downloads' => (int) $r['downloads'],
            'created_at' => $r['created_at']
        );
    }
    return $out;
}

// Récupère un fichier par id
function getFileById($id)
{
    $pdo = get_db_connection();
    $stmt = $pdo->prepare("SELECT * FROM files WHERE id = ?");
    $stmt->execute([$id]);
    $r = $stmt->fetch();
    if (!$r)
        return null;
    return array(
        'id' => (string) $r['id'],
        'filename' => $r['filename'],
        'stored_name' => $r['filename'],
        'original_name' => $r['original_name'],
        'owner' => $r['owner'],
        'reserved_to' => $r['reserved_to'],
        'downloads' => (int) $r['downloads'],
        'created_at' => $r['created_at']
    );
}

// Met à jour les métadonnées d'un fichier
function updateFileMetadata($id, $fields)
{
    $allowed = ['original_name' => 'original_name', 'stored_name' => 'filename', 'filename' => 'filename', 'owner' => 'owner', 'reserved_to' => 'reserved_to', 'downloads' => 'downloads'];
    $set = [];
    $params = [];
    foreach ($fields as $k => $v) {
        if (isset($allowed[$k])) {
            $set[] = $allowed[$k] . " = ?";
            $params[] = $v;
        }
    }
    if (empty($set))
        return false;
    $params[] = $id;
    $sql = "UPDATE files SET " . implode(', ', $set) . " WHERE id = ?";
    $pdo = get_db_connection();
    $stmt = $pdo->prepare($sql);
    return $stmt->execute($params);
}

// Supprime les métadonnées d'un fichier
function deleteFileMetadata($id)
{
    $pdo = get_db_connection();
    $stmt = $pdo->prepare("DELETE FROM files WHERE id = ?");
    return $stmt->execute([$id]);
}

// Sauvegarde une liste de fichiers (utilisé lors du changement d'email)
function saveFiles($files)
{
    foreach ($files as $f) {
        if (!isset($f['id']))
            continue;
        $fields = [];
        if (isset($f['owner']))
            $fields['owner'] = $f['owner'];
        if (isset($f['reserved_to']))
            $fields['reserved_to'] = $f['reserved_to'];
        updateFileMetadata($f['id'], $fields);
    }
}

// Mise à jour d'un utilisateur
function updateUser($currentEmail, $fields)
{
    $allowed = ['email' => 'email', 'password' => 'password'];
    $set = [];
    $params = [];
    foreach ($fields as $k => $v) {
        if (isset($allowed[$k])) {
            $set[] = $allowed[$k] . " = ?";
            $params[] = $v;
        }
    }
    if (empty($set))
        return false;
    $params[] = strtolower($currentEmail);
    $sql = "UPDATE users SET " . implode(', ', $set) . " WHERE LOWER(email) = ?";
    $pdo = get_db_connection();
    $stmt = $pdo->prepare($sql);
    return $stmt->execute($params);
}

// Génère le chemin du dossier utilisateur à partir de son email
function generateUserFolder($email)
{
    // Pour Vercel et environnements sans stockage persistant, stocker dans le répertoire temporaire
    return UPLOADS_DIR;
}