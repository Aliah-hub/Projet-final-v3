<?php
require_once '../includes/fonctions.php';
if (isset($_POST['inscription'])) {
    $nom = $_POST['nom'];
    $date_naissance = $_POST['date_naissance'];
    $genre = $_POST['genre'];
    $mail = $_POST['mail'];
    $ville = $_POST['ville'];
    $mdp = $_POST['mdp']; 

    $resultat = inscrire_membre($nom, $date_naissance, $genre, $mail, $ville, $mdp);
    if ($resultat) {
        echo '<div class="alert alert-success">Inscription réussie ! <a href="login.php" class="alert-link">Connectez-vous</a></div>';
    } else {
        echo '<div class="alert alert-danger">Erreur lors de l\'inscription : Vérifiez vos informations ou essayez un autre mail.</div>';
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Inscription</title>
    <link rel="stylesheet" href="../assets/styles.css">
    <link href="../bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <script src="../bootstrap/js/bootstrap.bundle.min.js"></script>
</head>
<body>
    <div class="container">
        <h1>Inscription</h1>
        <form method="POST" action="">
            <div class="mb-3">
                <label class="form-label">Nom :</label>
                <input type="text" name="nom" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Date de naissance :</label>
                <input type="date" name="date_naissance" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Genre :</label>
                <select name="genre" class="form-select" required>
                    <option value="">Choisir un genre</option>
                    <option value="Homme">Homme</option>
                    <option value="Femme">Femme</option>
                    <option value="Autre">Autre</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Mail :</label>
                <input type="email" name="mail" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Ville :</label>
                <input type="text" name="ville" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Mot de passe :</label>
                <input type="password" name="mdp" class="form-control" required>
            </div>
            <button type="submit" name="inscription" class="btn btn-primary">S'inscrire</button>
        </form>
        <a href="login.php" class="btn btn-link">Déjà un compte ? Se connecter</a>
    </div>
</body>
</html>