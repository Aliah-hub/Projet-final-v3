<?php
require_once 'connexion.php';

function inscrire_membre($nom, $date_naissance, $genre, $mail, $ville, $mdp, $image_profil = '../Uploads/images/default_profile.png') {
    global $bdd;
    if (!$bdd) {
        echo 'Erreur : Connexion a la base de donnees non etablie.';
        return false;
    }
    $mail = mysqli_real_escape_string($bdd, $mail);
    $requete_verif = "SELECT * FROM vmembre WHERE mail = '$mail'";
    $resultat_verif = mysqli_query($bdd, $requete_verif);
    if (mysqli_num_rows($resultat_verif) > 0) {
        echo "Erreur : Ce mail est deja utilise.";
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
        echo 'Erreur : Connexion a la base de donnees non etablie.';
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
        echo 'Erreur : Connexion a la base de donnees non etablie.';
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
        echo 'Erreur : Connexion a la base de donnees non etablie.';
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
        echo 'Erreur : Connexion a la base de donnees non etablie.';
        return ['membre' => null, 'objets' => [], 'emprunts' => []];
    }
    
    $id_membre = mysqli_real_escape_string($bdd, $id_membre);
    $requete_membre = "SELECT * FROM vmembre WHERE id_membre = '$id_membre'";
    $resultat_membre = mysqli_query($bdd, $requete_membre);
    if (!$resultat_membre) {
        echo 'Erreur SQL : ' . mysqli_error($bdd);
        return ['membre' => null, 'objets' => [], 'emprunts' => []];
    }
    $membre = mysqli_fetch_assoc($resultat_membre);
    
    if (!$membre) {
        return ['membre' => null, 'objets' => [], 'emprunts' => []];
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
        return ['membre' => $membre, 'objets' => [], 'emprunts' => []];
    }
    
    $objets = [];
    while ($row = mysqli_fetch_assoc($resultat_objets)) {
        $objets[$row['nom_categorie']][] = $row;
    }

    $requete_emprunts = "SELECT 
                            o.id_objet,
                            o.nom_objet,
                            c.nom_categorie,
                            e.date_retour
                         FROM vemprunt e
                         JOIN vobjet o ON e.id_objet = o.id_objet
                         JOIN vcategorie_objet c ON o.id_categorie = c.id_categorie
                         WHERE e.id_membre = '$id_membre' AND e.date_retour >= CURDATE()
                         ORDER BY e.date_retour";
    $resultat_emprunts = mysqli_query($bdd, $requete_emprunts);
    if (!$resultat_emprunts) {
        echo 'Erreur SQL : ' . mysqli_error($bdd);
        return ['membre' => $membre, 'objets' => $objets, 'emprunts' => []];
    }
    
    $emprunts = [];
    while ($row = mysqli_fetch_assoc($resultat_emprunts)) {
        $emprunts[] = $row;
    }
    
    return ['membre' => $membre, 'objets' => $objets, 'emprunts' => $emprunts];
}

function emprunter_objet($id_objet, $id_membre, $duree_jours) {
    global $bdd;
    if (!$bdd) {
        echo 'Erreur : Connexion a la base de donnees non etablie.';
        return 'Erreur : Connexion a la base de donnees non etablie.';
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
        return "Erreur : Cet objet est deja emprunte.";
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

function retourner_objet($id_objet, $id_membre, $etat) {
    global $bdd;
    if (!$bdd) {
        echo 'Erreur : Connexion a la base de donnees non etablie.';
        return 'Erreur : Connexion a la base de donnees non etablie.';
    }
    $id_objet = mysqli_real_escape_string($bdd, $id_objet);
    $id_membre = mysqli_real_escape_string($bdd, $id_membre);
    $etat = mysqli_real_escape_string($bdd, $etat);
    
    if ($etat != 'OK' && $etat != 'Abime') {
        return "Erreur : Etat invalide.";
    }

    $date_retour = date('Y-m-d');
    $requete = "INSERT INTO vretour (id_objet, id_membre, date_retour, etat) 
                VALUES ('$id_objet', '$id_membre', '$date_retour', '$etat')";
    $resultat = mysqli_query($bdd, $requete);
    if (!$resultat) {
        return 'Erreur SQL : ' . mysqli_error($bdd);
    }

    $requete_delete = "DELETE FROM vemprunt 
                       WHERE id_objet = '$id_objet' AND id_membre = '$id_membre' AND date_retour >= CURDATE()";
    $resultat_delete = mysqli_query($bdd, $requete_delete);
    if (!$resultat_delete) {
        return 'Erreur SQL : ' . mysqli_error($bdd);
    }

    return true;
}

function lister_retours() {
    global $bdd;
    if (!$bdd) {
        echo 'Erreur : Connexion a la base de donnees non etablie.';
        return ['ok' => 0, 'abime' => 0];
    }
    $requete = "SELECT etat, COUNT(*) as nombre 
                FROM vretour 
                GROUP BY etat";
    $resultat = mysqli_query($bdd, $requete);
    if (!$resultat) {
        echo 'Erreur SQL : ' . mysqli_error($bdd);
        return ['ok' => 0, 'abime' => 0];
    }
    
    $retours = ['ok' => 0, 'abime' => 0];
    while ($row = mysqli_fetch_assoc($resultat)) {
        if ($row['etat'] == 'OK') {
            $retours['ok'] = $row['nombre'];
        } elseif ($row['etat'] == 'Abime') {
            $retours['abime'] = $row['nombre'];
        }
    }
    return $retours;
}
?>