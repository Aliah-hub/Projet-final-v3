<?php
require_once '../includes/fonctions.php';
session_start();
if (!isset($_SESSION['id_membre'])) {
    header("Location: login.php");
    exit();
}

$id_objet = isset($_GET['id']) && is_numeric($_GET['id']) ? mysqli_real_escape_string($bdd, $_GET['id']) : '';
if (!$id_objet) {
    header("Location: liste_objets.php");
    exit();
}

$requete = "SELECT 
                o.id_objet,
                o.nom_objet,
                c.nom_categorie,
                m.nom AS proprietaire,
                o.image_principale,
                o.id_membre
            FROM vobjet o
            JOIN vcategorie_objet c ON o.id_categorie = c.id_categorie
            JOIN vmembre m ON o.id_membre = m.id_membre
            WHERE o.id_objet = '$id_objet'";
$resultat = mysqli_query($bdd, $requete);
if (!$resultat) {
    die('Erreur SQL : ' . mysqli_error($bdd) . '<br>Requête : ' . $requete);
}
$objet = mysqli_fetch_assoc($resultat);
if (!$objet) {
    header("Location: liste_objets.php");
    exit();
}

$requete_images = "SELECT id_image, nom_image FROM vimages_objet WHERE id_objet = '$id_objet'";
$resultat_images = mysqli_query($bdd, $requete_images);
if (!$resultat_images) {
    die('Erreur SQL : ' . mysqli_error($bdd) . '<br>Requête : ' . $requete_images);
}
$images = [];
while ($row = mysqli_fetch_assoc($resultat_images)) {
    $images[] = $row;
}

$requete_emprunts = "SELECT 
                        e.date_emprunt,
                        e.date_retour,
                        m.nom AS emprunteur
                    FROM vemprunt e
                    JOIN vmembre m ON e.id_membre = m.id_membre
                    WHERE e.id_objet = '$id_objet'
                    ORDER BY e.date_emprunt DESC";
$resultat_emprunts = mysqli_query($bdd, $requete_emprunts);
if (!$resultat_emprunts) {
    die('Erreur SQL : ' . mysqli_error($bdd) . '<br>Requête : ' . $requete_emprunts);
}
$emprunts = [];
while ($row = mysqli_fetch_assoc($resultat_emprunts)) {
    $emprunts[] = $row;
}

if (isset($_POST['supprimer_image']) && $_SESSION['id_membre'] == $objet['id_membre']) {
    $id_image = mysqli_real_escape_string($bdd, $_POST['id_image']);
    $requete_image = "SELECT nom_image FROM vimages_objet WHERE id_image = '$id_image' AND id_objet = '$id_objet'";
    $resultat_image = mysqli_query($bdd, $requete_image);
    if ($resultat_image && $image = mysqli_fetch_assoc($resultat_image)) {
        if (file_exists($image['nom_image']) && $image['nom_image'] != '../Uploads/images/default.jpg
        ') {
            unlink($image['nom_image']);
        }
        $requete_suppr = "DELETE FROM vimages_objet WHERE id_image = '$id_image'";
        if (mysqli_query($bdd, $requete_suppr)) {
            header("Location: fiche_objet.php?id=$id_objet");
            exit();
        } else {
            echo '<div class="alert alert-danger">Erreur SQL lors de la suppression : ' . mysqli_error($bdd) . '</div>';
        }
    } else {
        echo '<div class="alert alert-danger">Erreur : Image introuvable ou vous n\'avez pas les permissions.</div>';
    }
}

if (isset($_POST['supprimer_image_principale']) && $_SESSION['id_membre'] == $objet['id_membre']) {
    if ($objet['image_principale'] != '../Uploads/images/default.jpg
    ') {
        if (file_exists($objet['image_principale'])) {
            unlink($objet['image_principale']);
        }
        $requete_update = "UPDATE vobjet SET image_principale = '../Uploads/images/default.jpg
        ' WHERE id_objet = '$id_objet'";
        if (mysqli_query($bdd, $requete_update)) {
            header("Location: fiche_objet.php?id=$id_objet");
            exit();
        } else {
            echo '<div class="alert alert-danger">Erreur SQL lors de la suppression de l\'image principale : ' . mysqli_error($bdd) . '</div>';
        }
    } else {
        echo '<div class="alert alert-danger">Erreur : L\'image principale est déjà l\'image par défaut.</div>';
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Fiche de l'objet</title>
    <link rel="stylesheet" href="../assets/styles.css">
    <link href="../bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <script src="../bootstrap/js/bootstrap.bundle.min.js"></script>
</head>
<body>
    <div class="container">
        <h1><?php echo htmlspecialchars($objet['nom_objet']); ?></h1>
        <p><strong>Catégorie :</strong> <?php echo htmlspecialchars($objet['nom_categorie']); ?></p>
        <p><strong>Propriétaire :</strong> <?php echo htmlspecialchars($objet['proprietaire']); ?></p>
        <img src="<?php echo htmlspecialchars($objet['image_principale']); ?>" alt="Image principale" width="200" class="img-fluid">
        <?php if ($_SESSION['id_membre'] == $objet['id_membre'] && $objet['image_principale'] != '../Uploads/images/default.jpg
        ') { ?>
            <form method="POST" action="">
                <button type="submit" name="supprimer_image_principale" class="btn btn-danger mt-2">Supprimer l'image principale</button>
            </form>
        <?php } ?>
        <h3>Images supplémentaires :</h3>
        <?php if (empty($images)) { ?>
            <p class="alert alert-info">Aucune image supplémentaire.</p>
        <?php } else { ?>
            <div class="row">
                <?php foreach ($images as $image) { ?>
                    <div class="col-md-3">
                        <img src="<?php echo htmlspecialchars($image['nom_image']); ?>" alt="Image" width="100" class="img-fluid">
                        <?php if ($_SESSION['id_membre'] == $objet['id_membre']) { ?>
                            <form method="POST" action="">
                                <input type="hidden" name="id_image" value="<?php echo htmlspecialchars($image['id_image']); ?>">
                                <button type="submit" name="supprimer_image" class="btn btn-danger btn-sm mt-2">Supprimer</button>
                            </form>
                        <?php } ?>
                    </div>
                <?php } ?>
            </div>
        <?php } ?>
        <h3>Historique des emprunts :</h3>
        <?php if (empty($emprunts)) { ?>
            <p class="alert alert-info">aucun emprunt .</p>
        <?php } else { ?>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Date d'emprunt</th>
                        <th>Date de retour</th>
                        <th>Emprunteur</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($emprunts as $emprunt) { ?>
                        <tr>
                            <td><?php echo htmlspecialchars($emprunt['date_emprunt']); ?></td>
                            <td><?php echo $emprunt['date_retour'] ? htmlspecialchars($emprunt['date_retour']) : 'Non retourné'; ?></td>
                            <td><?php echo htmlspecialchars($emprunt['emprunteur']); ?></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        <?php } ?>
        <a href="liste_objets.php" class="btn btn-secondary">Retour à la liste</a>
    </div>
</body>
</html>