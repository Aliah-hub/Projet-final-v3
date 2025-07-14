<?php
require_once '../includes/fonctions.php';
session_start();
if (!isset($_SESSION['id_membre'])) {
    header("Location: login.php");
    exit();
}

$uploadDir = __DIR__ . '/../Uploads/images/';
$maxSize = 2 * 1024 * 1024; 
$allowedMimeTypes = ['image/jpeg', 'image/png'];
$errors = [];

if (isset($_POST['ajouter_objet'])) {
    $nom_objet = $_POST['nom_objet'];
    $id_categorie = $_POST['categorie'];
    $id_membre = $_SESSION['id_membre'];
    
    if (!is_dir($uploadDir)) {
        if (!mkdir($uploadDir, 0777, true)) {
            $errors[] = "Erreur : Impossible de créer le dossier $uploadDir.";
        }
    } elseif (!is_writable($uploadDir)) {
        $errors[] = "Erreur : Le dossier $uploadDir n'est pas accessible en écriture. Essayez : chmod -R 0777 $uploadDir";
    }
    
    $images = [];
    $image_principale = '../Uploads/images/default.png';
    
    if (isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {
        foreach ($_FILES['images']['name'] as $i => $nom_image) {
            if ($_FILES['images']['error'][$i] === UPLOAD_ERR_OK) {
                if ($_FILES['images']['size'][$i] > $maxSize) {
                    $errors[] = "Erreur : Le fichier $nom_image est trop volumineux (max 2 Mo).";
                    continue;
                }
                
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mime = finfo_file($finfo, $_FILES['images']['tmp_name'][$i]);
                finfo_close($finfo);
                if (!in_array($mime, $allowedMimeTypes)) {
                    $errors[] = "Erreur : Type de fichier non autorisé pour $nom_image ($mime).";
                    continue;
                }
                
                $originalName = pathinfo($nom_image, PATHINFO_FILENAME);
                $extension = pathinfo($nom_image, PATHINFO_EXTENSION);
                $newName = $originalName . '_' . uniqid() . '.' . $extension;
                $targetFile = $uploadDir . $newName;
                
                if (move_uploaded_file($_FILES['images']['tmp_name'][$i], $targetFile)) {
                    $relativePath = '../Uploads/images/' . $newName;
                    $images[] = $relativePath;
                } else {
                    $errors[] = "Erreur : Échec du déplacement du fichier $nom_image. Vérifiez les permissions du dossier $uploadDir.";
                }
            } elseif ($_FILES['images']['error'][$i] !== UPLOAD_ERR_NO_FILE) {
                $error_codes = [
                    UPLOAD_ERR_INI_SIZE => "Fichier trop grand (limite php.ini).",
                    UPLOAD_ERR_FORM_SIZE => "Fichier trop grand (limite du formulaire).",
                    UPLOAD_ERR_PARTIAL => "Upload partiel.",
                    UPLOAD_ERR_NO_FILE => "Aucun fichier uploadé.",
                    UPLOAD_ERR_NO_TMP_DIR => "Dossier temporaire manquant.",
                    UPLOAD_ERR_CANT_WRITE => "Échec d'écriture sur le disque.",
                    UPLOAD_ERR_EXTENSION => "Extension PHP a arrêté l'upload."
                ];
                $errors[] = "Erreur lors de l'upload du fichier $nom_image : " . ($error_codes[$_FILES['images']['error'][$i]] ?? "Erreur inconnue");
            }
        }
    }
    
    $nom_objet_escaped = mysqli_real_escape_string($bdd, $nom_objet);
    $requete_check = "SELECT id_objet, image_principale FROM vobjet WHERE nom_objet = '$nom_objet_escaped' AND id_membre = '$id_membre'";
    $resultat_check = mysqli_query($bdd, $requete_check);
    if (!$resultat_check) {
        $errors[] = 'Erreur SQL lors de la vérification de l\'objet : ' . mysqli_error($bdd);
    } else {
        $objet_existant = mysqli_fetch_assoc($resultat_check);
        if ($objet_existant) {
            $id_objet = $objet_existant['id_objet'];
            if (!empty($images) && $objet_existant['image_principale'] == '../Uploads/images/default.png') {
                $image_principale = mysqli_real_escape_string($bdd, $images[0]);
                $requete_update = "UPDATE vobjet SET image_principale = '$image_principale' WHERE id_objet = '$id_objet'";
                if (!mysqli_query($bdd, $requete_update)) {
                    $errors[] = "Erreur SQL lors de la mise à jour de l'image principale : " . mysqli_error($bdd);
                }
                $images_to_add = array_slice($images, 1);
            } else {
                $images_to_add = $images;
            }
            
            foreach ($images_to_add as $image) {
                $image = mysqli_real_escape_string($bdd, $image);
                $requete_image = "INSERT INTO vimages_objet (id_objet, nom_image) 
                                VALUES ('$id_objet', '$image')";
                if (!mysqli_query($bdd, $requete_image)) {
                    $errors[] = "Erreur SQL lors de l'ajout de l'image : " . mysqli_error($bdd);
                }
            }
        } else {
            $id_categorie = mysqli_real_escape_string($bdd, $id_categorie);
            if (!empty($images)) {
                $image_principale = mysqli_real_escape_string($bdd, $images[0]);
            } else {
                $image_principale = mysqli_real_escape_string($bdd, $image_principale);
            }
            
            $requete = "INSERT INTO vobjet (nom_objet, id_categorie, id_membre, image_principale) 
                        VALUES ('$nom_objet_escaped', '$id_categorie', '$id_membre', '$image_principale')";
            $resultat = mysqli_query($bdd, $requete);
            if (!$resultat) {
                $errors[] = 'Erreur SQL lors de l\'ajout de l\'objet : ' . mysqli_error($bdd) . '<br>Requête : ' . $requete;
            } else {
                $id_objet = mysqli_insert_id($bdd);
                
                foreach (array_slice($images, 1) as $image) {
                    $image = mysqli_real_escape_string($bdd, $image);
                    $requete_image = "INSERT INTO vimages_objet (id_objet, nom_image) 
                                    VALUES ('$id_objet', '$image')";
                    if (!mysqli_query($bdd, $requete_image)) {
                        $errors[] = "Erreur SQL lors de l'ajout de l'image : " . mysqli_error($bdd);
                    }
                }
            }
        }
    }
    
    if (empty($errors)) {
        header("Location: liste_objets.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter un objet</title>
    <link rel="stylesheet" href="../assets/styles.css">
    <link href="../bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <script src="../bootstrap/js/bootstrap.bundle.min.js"></script>
</head>
<body>
    <div class="container">
        <h1>Ajouter un objet</h1>
        <?php if (!empty($errors)) { ?>
            <div class="alert alert-danger">
                <p>Erreurs lors de l'ajout de l'objet :</p>
                <ul>
                    <?php foreach ($errors as $error) { ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php } ?>
                </ul>
            </div>
        <?php } ?>
        <form method="POST" action="" enctype="multipart/form-data">
            <div class="mb-3">
                <label class="form-label">Nom de l'objet :</label>
                <input type="text" name="nom_objet" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Catégorie :</label>
                <select name="categorie" class="form-select" required>
                    <option value="">Choisir une catégorie</option>
                    <?php
                    $categories = lister_categories();
                    foreach ($categories as $categorie) {
                        echo "<option value='{$categorie['id_categorie']}'>{$categorie['nom_categorie']}</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Images (max 2 Mo, JPEG/PNG) :</label>
                <input type="file" name="images[]" class="form-control" multiple accept="image/jpeg,image/png">
            </div>
            <button type="submit" name="ajouter_objet" class="btn btn-primary">Ajouter</button>
        </form>
        <a href="liste_objets.php" class="btn btn-secondary">Retour à la liste</a>
    </div>
</body>
</html>