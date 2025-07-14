<?php
require_once '../includes/fonctions.php';
session_start();
if (!isset($_SESSION['id_membre'])) {
    header("Location: login.php");
    exit();
}

$categorie_id = isset($_GET['categorie']) && is_numeric($_GET['categorie']) ? $_GET['categorie'] : '';
$nom_objet = isset($_GET['nom_objet']) ? trim($_GET['nom_objet']) : '';
$disponible = isset($_GET['disponible']) ? 1 : 0;
$erreur_emprunt = '';

if (isset($_POST['emprunter_objet'])) {
    $id_objet = $_POST['id_objet'];
    $duree_jours = $_POST['duree_jours'];
    $id_membre = $_SESSION['id_membre'];
    
    if (!is_numeric($duree_jours) || $duree_jours <= 0) {
        $erreur_emprunt = "Erreur : La durée doit être un nombre positif.";
    } else {
        $resultat = emprunter_objet($id_objet, $id_membre, $duree_jours);
        if ($resultat !== true) {
            $erreur_emprunt = $resultat;
        } else {
            header("Location: liste_objets.php");
            exit();
        }
    }
}

$objets = lister_objets($categorie_id, $nom_objet, $disponible);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Liste des objets</title>
    <link rel="stylesheet" href="../assets/styles.css">
    <link href="../bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <script src="../bootstrap/js/bootstrap.bundle.min.js"></script>
</head>
<body>
    <div class="container wide">
        <h1>Liste des objets</h1>
        <div class="mb-3">
            <a href="fiche_membre.php" class="btn btn-primary">Mon profil</a>
            <a href="ajouter_objet.php" class="btn btn-success">Ajouter un objet</a>
            <a href="login.php" class="btn btn-danger">Se déconnecter</a>
        </div>
        <form method="GET" action="">
            <div class="row mb-3">
                <div class="col-md-4">
                    <label class="form-label">Filtrer par catégorie :</label>
                    <select name="categorie" class="form-select">
                        <option value="">Toutes</option>
                        <?php
                        $categories = lister_categories();
                        foreach ($categories as $categorie) {
                            $selected = ($categorie_id == $categorie['id_categorie']) ? 'selected' : '';
                            echo "<option value='{$categorie['id_categorie']}' $selected>{$categorie['nom_categorie']}</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Nom de l'objet :</label>
                    <input type="text" name="nom_objet" value="<?php echo htmlspecialchars($nom_objet); ?>" class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Disponible uniquement :</label>
                    <input type="checkbox" name="disponible" <?php echo $disponible ? 'checked' : ''; ?> class="form-check-input">
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Filtrer</button>
            <a href="liste_objets.php" class="btn btn-secondary">Réinitialiser</a>
        </form>
        <?php if ($erreur_emprunt) { ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($erreur_emprunt); ?></div>
        <?php } ?>
        <?php if (empty($objets)) { ?>
            <p class="alert alert-info">Aucun objet trouvé.</p>
        <?php } else { ?>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Objet</th>
                        <th>Catégorie</th>
                        <th>Propriétaire</th>
                        <th>Statut</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($objets as $objet) { ?>
                        <tr>
                            <td><img src="<?php echo htmlspecialchars($objet['image_principale']); ?>" alt="Image principale" width="50" class="img-fluid"></td>
                            <td><?php echo htmlspecialchars($objet['nom_objet']); ?></td>
                            <td><?php echo htmlspecialchars($objet['nom_categorie']); ?></td>
                            <td><?php echo htmlspecialchars($objet['proprietaire']); ?></td>
                            <td><?php echo $objet['date_retour'] ? "Emprunté (retour le {$objet['date_retour']})" : "Disponible"; ?></td>
                            <td>
                                <a href="fiche_objet.php?id=<?php echo htmlspecialchars($objet['id_objet']); ?>" class="btn btn-primary btn-sm">🔍</a>
                                <?php if (!$objet['date_retour'] && $objet['proprietaire_id'] != $_SESSION['id_membre']) { ?>
                                    <form method="POST" action="" class="d-inline">
                                        <input type="hidden" name="id_objet" value="<?php echo htmlspecialchars($objet['id_objet']); ?>">
                                        <input type="number" name="duree_jours" class="form-control form-control-sm d-inline-block" style="width: 70px;" placeholder="Jours" required min="1">
                                        <button type="submit" name="emprunter_objet" class="btn btn-success btn-sm">Emprunter</button>
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