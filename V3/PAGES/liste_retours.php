<?php
require_once '../includes/fonctions.php';
session_start();
if (!isset($_SESSION['id_membre'])) {
    header("Location: login.php");
    exit();
}

$retours = lister_retours();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Liste des retours</title>
    <link rel="stylesheet" href="../assets/styles.css">
    <link href="../bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <script src="../bootstrap/js/bootstrap.bundle.min.js"></script>
</head>
<body>
    <div class="container wide">
        <h1>Liste des retours</h1>
        <div class="mb-3">
            <a href="fiche_membre.php" class="btn btn-primary">Mon profil</a>
            <a href="liste_objets.php" class="btn btn-primary">Liste des objets</a>
            <a href="ajouter_objet.php" class="btn btn-success">Ajouter un objet</a>
            <a href="login.php" class="btn btn-danger">Se deconnecter</a>
        </div>
        <h2>Statistiques des retours</h2>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Etat</th>
                    <th>Nombre</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>OK</td>
                    <td><?php echo $retours['ok']; ?></td>
                </tr>
                <tr>
                    <td>Abime</td>
                    <td><?php echo $retours['abime']; ?></td>
                </tr>
            </tbody>
        </table>
    </div>
</body>
</html>