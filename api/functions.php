<?php
// Definition d'une constantes pour les fichiers enregistrés
define('UPLOADS_DIR', __DIR__ . '/uploads');

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
    if (session_status() === PHP_SESSION_NONE)
        session_start();
    if (!isset($_SESSION['user_email'])) {
        header('Location: login.php');
        exit;
    }
}

// Fonction pour trouver l'utilisateur / utilisatrice par email
function findUserByEmail($email)
{
    $pdo = get_db_connection();
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([strtolower($email)]);
    return $stmt->fetch();
}

// Récupéraation des fichiers mis en ligne par l'utilisateur / utilisatrice
function getUserFiles($email)
{
    $pdo = get_db_connection();
    $stmt = $pdo->prepare("SELECT * FROM files WHERE LOWER(owner) = ? OR LOWER(reserved_to) = ?");
    $stmt->execute([strtolower($email), strtolower($email)]);
    return $stmt->fetchAll();
}

// Génère le chemin du dossier utilisateur à partir de son email
function generateUserFolder($email)
{
    $safe = preg_replace('/[^a-z0-9._-]/i', '_', strtolower(trim($email)));
    $folder = UPLOADS_DIR . '/' . $safe;
    return $folder;
}