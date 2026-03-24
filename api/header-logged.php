<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?> Imgshare </title>
    <link rel="stylesheet" href="styles.css">
</head>

<body>
    <header class="header">
        <h1>Imgshare</h1>
        <nav class="nav">
            <a href="index.php" class="nav-link">Accueil</a>
            <a href="logout.php" class="nav-link">Se déconnecter</a>
        </nav>
    </header>