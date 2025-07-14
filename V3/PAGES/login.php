<?php
session_start();
require_once '../includes/fonctions.php';
if (isset($_POST['connexion'])) {
    $mail = $_POST['mail'];
    $mdp = $_POST['mdp'];
    $membre = connecter_membre($mail, $mdp);
    if ($membre) {
        $_SESSION['id_membre'] = $membre['id_membre'];
        $_SESSION['nom'] = $membre['nom'];
        header("Location: liste_objets.php");
        exit();
    } else {
        echo '<div class="alert alert-danger">Erreur : Mail ou mot de passe incorrect. Vérifiez vos informations ou inscrivez-vous.</div>';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion</title>
    <link rel="stylesheet" href="../assets/styles.css">
    <link href="../bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <script src="../bootstrap/js/bootstrap.bundle.min.js"></script>
</head>
<body>
    <div class="container">
        <h1>Connexion</h1>
        <form method="POST" action="">
            <div class="mb-3">
                <label class="form-label">Mail :</label>
                <input type="email" name="mail" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Mot de passe :</label>
                <input type="password" name="mdp" class="form-control" required>
            </div>
            <button type="submit" name="connexion" class="btn btn-primary">Se connecter</button>
        </form>
        <a href="inscription.php" class="btn btn-link">Pas de compte ? S'inscrire</a>
    </div>
</body>
</html>