<?php
require_once '../includes/fonctions.php';
session_start();
if (!isset($_SESSION['id_membre'])) {
    header("Location: login.php");
    exit();
}

$id_membre = $_SESSION['id_membre'];
$data = obtenir_profil_et_objets($id_membre);
$membre = $data['membre'];
$objets = $data['objets'];

if (!$membre) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Fiche du membre</title>
    <link rel="stylesheet" href="../assets/styles.css">
    <link href="../bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <script src="../bootstrap/js/bootstrap.bundle.min.js"></script>
</head>
<body>
    <div class="container">
        <h1>Profil de <?php echo htmlspecialchars($membre['nom']); ?></h1>
        <p><strong>Mail :</strong> <?php echo htmlspecialchars($membre['mail']); ?></p>
        <p><strong>Date de naissance :</strong> <?php echo htmlspecialchars($membre['date_naissance']); ?></p>
        <p><strong>Genre :</strong> <?php echo htmlspecialchars($membre['genre']); ?></p>
        <p><strong>Ville :</strong> <?php echo htmlspecialchars($membre['ville']); ?></p>
        <p><img src="<?php echo htmlspecialchars($membre['image_profil']); ?>" alt="Photo de profil" width="100"></p>
        <h2>Mes objets</h2>
        <?php if (empty($objets)) { ?>
            <p>Vous n'avez aucun objet.</p>
        <?php } else { ?>
            <?php foreach ($objets as $categorie => $liste_objets) { ?>
                <h3><?php echo htmlspecialchars($categorie); ?></h3>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Nom de l'objet</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($liste_objets as $objet) { ?>
                            <tr>
                                <td><img src="<?php echo htmlspecialchars($objet['image_principale']); ?>" alt="Image principale" width="50"></td>
                                <td><?php echo htmlspecialchars($objet['nom_objet']); ?></td>
                                <td><a href="fiche_objet.php?id=<?php echo $objet['id_objet']; ?>" class="btn btn-primary btn-sm">Voir détails</a></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            <?php } ?>
        <?php } ?>
        <a href="liste_objets.php" class="btn btn-secondary">Retour à la liste</a>
    </div>
</body>
</html>