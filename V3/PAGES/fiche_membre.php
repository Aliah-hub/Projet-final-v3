<?php
require_once '../includes/fonctions.php';
session_start();
if (!isset($_SESSION['id_membre'])) {
    header("Location: login.php");
    exit();
}

$profil = obtenir_profil_et_objets($_SESSION['id_membre']);
$membre = $profil['membre'];
$objets = $profil['objets'];
$emprunts = $profil['emprunts'];
$erreur_retour = '';

if (isset($_POST['retourner_objet'])) {
    $id_objet = $_POST['id_objet'];
    $id_membre = $_SESSION['id_membre'];
    $etat = $_POST['etat'];
    
    $resultat = retourner_objet($id_objet, $id_membre, $etat);
    if ($resultat !== true) {
        $erreur_retour = $resultat;
    } else {
        header("Location: fiche_membre.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Profil de <?php echo htmlspecialchars($membre['nom']); ?></title>
    <link rel="stylesheet" href="../assets/styles.css">
    <link href="../bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <script src="../bootstrap/js/bootstrap.bundle.min.js"></script>
</head>
<body>
    <div class="container wide">
        <h1>Profil de <?php echo htmlspecialchars($membre['nom']); ?></h1>
        <div class="mb-3">
            <a href="liste_objets.php" class="btn btn-primary">Liste des objets</a>
            <a href="ajouter_objet.php" class="btn btn-success">Ajouter un objet</a>
            <a href="liste_retours.php" class="btn btn-info">Retours</a>
            <a href="login.php" class="btn btn-danger">Se deconnecter</a>
        </div>

        <h2>Informations personnelles</h2>
        <p><strong>Nom :</strong> <?php echo htmlspecialchars($membre['nom']); ?></p>
        <p><strong>Email :</strong> <?php echo htmlspecialchars($membre['mail']); ?></p>
        <p><strong>Ville :</strong> <?php echo htmlspecialchars($membre['ville']); ?></p>
        <p><strong>Date de naissance :</strong> <?php echo htmlspecialchars($membre['date_naissance']); ?></p>
        <p><strong>Genre :</strong> <?php echo htmlspecialchars($membre['genre']); ?></p>
        <img src="<?php echo htmlspecialchars($membre['image_profil']); ?>" alt="Photo de profil" width="100" class="img-fluid">

        <h2>Mes objets</h2>
        <?php if (empty($objets)) { ?>
            <p class="alert alert-info">Vous n'avez aucun objet.</p>
        <?php } else { ?>
            <?php foreach ($objets as $categorie => $liste_objets) { ?>
                <h3><?php echo htmlspecialchars($categorie); ?></h3>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Nom</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($liste_objets as $objet) { ?>
                            <tr>
                                <td><img src="<?php echo htmlspecialchars($objet['image_principale']); ?>" alt="Image principale" width="50" class="img-fluid"></td>
                                <td><?php echo htmlspecialchars($objet['nom_objet']); ?></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            <?php } ?>
        <?php } ?>

        <h2>Mes emprunts</h2>
        <?php if (empty($emprunts)) { ?>
            <p class="alert alert-info">Vous n'avez aucun emprunt en cours.</p>
        <?php } else { ?>
            <?php if ($erreur_retour) { ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($erreur_retour); ?></div>
            <?php } ?>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Objet</th>
                        <th>Categorie</th>
                        <th>Date de retour</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($emprunts as $emprunt) { ?>
                        <tr>
                            <td><?php echo htmlspecialchars($emprunt['nom_objet']); ?></td>
                            <td><?php echo htmlspecialchars($emprunt['nom_categorie']); ?></td>
                            <td><?php echo htmlspecialchars($emprunt['date_retour']); ?></td>
                            <td>
                                <?php if (!isset($_POST['confirmer_retour']) || $_POST['id_objet'] != $emprunt['id_objet']) { ?>
                                    <form method="POST" action="" class="d-inline">
                                        <input type="hidden" name="id_objet" value="<?php echo htmlspecialchars($emprunt['id_objet']); ?>">
                                        <button type="submit" name="confirmer_retour" class="btn btn-warning btn-sm">Retour</button>
                                    </form>
                                <?php } else { ?>
                                    <form method="POST" action="" class="d-inline">
                                        <input type="hidden" name="id_objet" value="<?php echo htmlspecialchars($emprunt['id_objet']); ?>">
                                        <select name="etat" class="form-select form-select-sm d-inline-block" style="width: 100px;" required>
                                            <option value="OK">OK</option>
                                            <option value="Abime">Abime</option>
                                        </select>
                                        <button type="submit" name="retourner_objet" class="btn btn-success btn-sm">Confirmer</button>
                                    </form>
                                <?php } ?>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        <?php } ?>
    </div>
</body>
</html>