<?php
require_once 'connexion.php';

function inscrire_membre($nom, $date_naissance, $genre, $mail, $ville, $mdp, $image_profil = '../Uploads/images/default_profile.png') {
    global $bdd;
    if (!$bdd) {
        echo 'Erreur : Connexion à la base de données non établie.';
        return false;
    }
    $mail = mysqli_real_escape_string($bdd, $mail);
    $requete_verif = "SELECT * FROM vmembre WHERE mail = '$mail'";
    $resultat_verif = mysqli_query($bdd, $requete_verif);
    if (mysqli_num_rows($resultat_verif) > 0) {
        echo "Erreur : Ce mail est déjà utilisé.";
        return false;
    }

    $nom = mysqli_real_escape_string($bdd, $nom);
    $date_naissance = mysqli_real_escape_string($bdd, $date_naissance);
    $genre = mysqli_real_escape_string($bdd, $genre);
    $ville = mysqli_real_escape_string($bdd, $ville);
    $mdp = mysqli_real_escape_string($bdd, $mdp);
    $image_profil = mysqli_real_escape_string($bdd, $image_profil);

    $requete = "INSERT INTO vmembre (nom, date_naissance, genre, mail, ville, mdp, image_profil) 
                VALUES ('$nom', '$date_naissance', '$genre', '$mail', '$ville', '$mdp', '$image_profil')";
    $resultat = mysqli_query($bdd, $requete);
    if (!$resultat) {
        echo 'Erreur SQL : ' . mysqli_error($bdd);
        return false;
    }
    return true;
}

function connecter_membre($mail, $mdp) {
    global $bdd;
    if (!$bdd) {
        echo 'Erreur : Connexion à la base de données non établie.';
        return false;
    }
    $mail = mysqli_real_escape_string($bdd, $mail);
    $mdp = mysqli_real_escape_string($bdd, $mdp);
    $requete = "SELECT * FROM vmembre WHERE mail = '$mail' AND mdp = '$mdp'";
    $resultat = mysqli_query($bdd, $requete);
    if (!$resultat) {
        echo 'Erreur SQL : ' . mysqli_error($bdd);
        return false;
    }
    $membre = mysqli_fetch_assoc($resultat);
    if ($membre) {
        return $membre;
    }
    return false;
}

function lister_categories() {
    global $bdd;
    if (!$bdd) {
        echo 'Erreur : Connexion à la base de données non établie.';
        return [];
    }
    $requete = "SELECT * FROM vcategorie_objet";
    $resultat = mysqli_query($bdd, $requete);
    $categories = [];
    while ($row = mysqli_fetch_assoc($resultat)) {
        $categories[] = $row;
    }
    return $categories;
}

function lister_objets($categorie_id, $nom_objet = '', $disponible = false) {
    global $bdd;
    if (!$bdd) {
        echo 'Erreur : Connexion à la base de données non établie.';
        return [];
    }
    $requete = "SELECT 
                    o.id_objet,
                    o.nom_objet,
                    c.nom_categorie,
                    m.nom AS proprietaire,
                    m.id_membre AS proprietaire_id,
                    e.date_retour,
                    o.id_categorie,
                    o.image_principale
                FROM vobjet o
                JOIN vcategorie_objet c ON o.id_categorie = c.id_categorie
                JOIN vmembre m ON o.id_membre = m.id_membre
                LEFT JOIN vemprunt e ON o.id_objet = e.id_objet AND e.date_retour >= CURDATE()";
    $conditions = [];
    if ($categorie_id) {
        $categorie_id = mysqli_real_escape_string($bdd, $categorie_id);
        $conditions[] = "o.id_categorie = '$categorie_id'";
    }
    if ($nom_objet) {
        $nom_objet = mysqli_real_escape_string($bdd, $nom_objet);
        $conditions[] = "o.nom_objet LIKE '%$nom_objet%'";
    }
    if ($disponible) {
        $conditions[] = "e.date_retour IS NULL";
    }
    if (!empty($conditions)) {
        $requete .= " WHERE " . implode(" AND ", $conditions);
    }
    $resultat = mysqli_query($bdd, $requete);
    if (!$resultat) {
        echo 'Erreur SQL : ' . mysqli_error($bdd);
        return [];
    }
    $objets = [];
    while ($row = mysqli_fetch_assoc($resultat)) {
        $objets[] = $row;
    }
    return $objets;
}

function obtenir_profil_et_objets($id_membre) {
    global $bdd;
    if (!$bdd) {
        echo 'Erreur : Connexion à la base de données non établie.';
        return ['membre' => null, 'objets' => []];
    }
    
    $id_membre = mysqli_real_escape_string($bdd, $id_membre);
    $requete_membre = "SELECT * FROM vmembre WHERE id_membre = '$id_membre'";
    $resultat_membre = mysqli_query($bdd, $requete_membre);
    if (!$resultat_membre) {
        echo 'Erreur SQL : ' . mysqli_error($bdd);
        return ['membre' => null, 'objets' => []];
    }
    $membre = mysqli_fetch_assoc($resultat_membre);
    
    if (!$membre) {
        return ['membre' => null, 'objets' => []];
    }

    $requete_objets = "SELECT 
                           o.id_objet,
                           o.nom_objet,
                           c.nom_categorie,
                           o.image_principale
                       FROM vobjet o
                       JOIN vcategorie_objet c ON o.id_categorie = c.id_categorie
                       WHERE o.id_membre = '$id_membre'
                       ORDER BY c.nom_categorie";
    $resultat_objets = mysqli_query($bdd, $requete_objets);
    if (!$resultat_objets) {
        echo 'Erreur SQL : ' . mysqli_error($bdd);
        return ['membre' => $membre, 'objets' => []];
    }
    
    $objets = [];
    while ($row = mysqli_fetch_assoc($resultat_objets)) {
        $objets[$row['nom_categorie']][] = $row;
    }
    
    return ['membre' => $membre, 'objets' => $objets];
}

function emprunter_objet($id_objet, $id_membre, $duree_jours) {
    global $bdd;
    if (!$bdd) {
        echo 'Erreur : Connexion à la base de données non établie.';
        return 'Erreur : Connexion à la base de données non établie.';
    }
    $id_objet = mysqli_real_escape_string($bdd, $id_objet);
    $id_membre = mysqli_real_escape_string($bdd, $id_membre);
    $duree_jours = mysqli_real_escape_string($bdd, $duree_jours);
    
    $requete_check = "SELECT e.date_retour 
                     FROM vemprunt e 
                     WHERE e.id_objet = '$id_objet' AND e.date_retour >= CURDATE()";
    $resultat_check = mysqli_query($bdd, $requete_check);
    if (!$resultat_check) {
        return 'Erreur SQL : ' . mysqli_error($bdd);
    }
    if (mysqli_num_rows($resultat_check) > 0) {
        return "Erreur : Cet objet est déjà emprunté.";
    }

    $date_emprunt = date('Y-m-d'); 
    $date_retour = date('Y-m-d', strtotime("+$duree_jours days")); 

    $requete = "INSERT INTO vemprunt (id_objet, id_membre, date_emprunt, date_retour) 
                VALUES ('$id_objet', '$id_membre', '$date_emprunt', '$date_retour')";
    $resultat = mysqli_query($bdd, $requete);
    if (!$resultat) {
        return 'Erreur SQL : ' . mysqli_error($bdd);
    }
    return true;
}
?>