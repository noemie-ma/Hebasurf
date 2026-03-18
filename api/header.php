<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?> Hebasurf </title>
    <link rel="stylesheet" href="styles.css">
</head>

<body>
    <header class="header">
        <h1>Hebasurf</h1>
        <nav class="nav">
            <a href="login.php" class="nav-link">Se connecter</a>
            <a href="inscription.php" class="nav-link">S'inscrire</a>
        </nav>
    </header>