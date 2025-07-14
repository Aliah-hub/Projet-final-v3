CREATE TABLE vmembre (
    id_membre INT PRIMARY KEY AUTO_INCREMENT,
    nom VARCHAR(50),
    date_naissance DATE,
    genre VARCHAR(10),
    mail VARCHAR(100) UNIQUE,
    ville VARCHAR(50),
    mdp VARCHAR(255),
    image_profil VARCHAR(255)
);

CREATE TABLE vcategorie_objet (
    id_categorie INT PRIMARY KEY AUTO_INCREMENT,
    nom_categorie VARCHAR(50)
);

CREATE TABLE vobjet (
    id_objet INT PRIMARY KEY AUTO_INCREMENT,
    nom_objet VARCHAR(100),
    id_categorie INT,
    id_membre INT,
    FOREIGN KEY (id_categorie) REFERENCES vcategorie_objet(id_categorie),
    FOREIGN KEY (id_membre) REFERENCES vmembre(id_membre)
);

CREATE TABLE vimages_objet (
    id_image INT PRIMARY KEY AUTO_INCREMENT,
    id_objet INT,
    nom_image VARCHAR(255),
    FOREIGN KEY (id_objet) REFERENCES vobjet(id_objet)
);

CREATE TABLE vemprunt (
    id_emprunt INT PRIMARY KEY AUTO_INCREMENT,
    id_objet INT,
    id_membre INT,
    date_emprunt DATE,
    date_retour DATE,
    FOREIGN KEY (id_objet) REFERENCES vobjet(id_objet),
    FOREIGN KEY (id_membre) REFERENCES vmembre(id_membre)
);

CREATE VIEW vue_objets AS
SELECT 
    o.id_objet,
    o.nom_objet,
    c.nom_categorie,
    m.nom AS proprietaire,
    e.date_retour,
    o.id_categorie 
FROM vobjet o
JOIN vcategorie_objet c ON o.id_categorie = c.id_categorie
JOIN vmembre m ON o.id_membre = m.id_membre
LEFT JOIN vemprunt e ON o.id_objet = e.id_objet AND e.date_retour >= CURDATE();

INSERT INTO vmembre (id_membre, nom, date_naissance, genre, mail, ville, mdp, image_profil) VALUES
(1, 'Aliah', '2006-10-30', 'Femme', 'Aliah@gmail.com', 'Iavoloha', '123', '../assets/images/1.png'),
(2, 'Aydane', '2005-11-23', 'Femme', 'Aydane@gmail.com', 'Lyon', 'mdp', '../assets/images/2.png'),
(3, 'Angela', '2002-03-10', 'Femme', 'Angela@gmail.com', 'ITU', '789', '../assets/images/3.png'),
(4, 'Voahary', '2000-07-30', 'Homme', 'vovo@gmail.com', 'Vohemara', '101', '../assets/images/4.png');

INSERT INTO vcategorie_objet (id_categorie, nom_categorie) VALUES
(1, 'Esthetique'),
(2, 'Bricolage'),
(3, 'Mecanique'),
(4, 'Cuisine');

INSERT INTO vobjet (id_objet, nom_objet, id_categorie, id_membre) VALUES
-- Membre 1 
(1, 'Miroir', 1, 1), (2, 'Parfum', 1, 1), (3, 'Pinceau maquillage', 1, 1),
(4, 'Perceuse', 2, 1), (5, 'Marteau', 2, 1), (6, 'Tournevis', 2, 1),
(7, 'Cle a molette', 3, 1), (8, 'Pistolet a peinture', 3, 1),
(9, 'Mixeur', 4, 1), (10, 'Poele', 4, 1),
-- Membre 2 
(11, 'Seche-cheveux', 1, 2), (12, 'Lisseur', 1, 2), (13, 'Vernis', 1, 2),
(14, 'Scie', 2, 2), (15, 'Niveau a bulle', 2, 2), (16, 'Pince', 2, 2),
(17, 'Pompe a velo', 3, 2), (18, 'Cle dynamometrique', 3, 2),
(19, 'Blender', 4, 2), (20, 'Couteau de chef', 4, 2),
-- Membre 3 
(21, 'Maquillage', 1, 3), (22, 'Creme', 1, 3), (23, 'Brosse', 1, 3),
(24, 'Visseuse', 2, 3), (25, 'Cutter', 2, 3), (26, 'Metre ruban', 2, 3),
(27, 'Cric', 3, 3), (28, 'Cle a pipe', 3, 3),
(29, 'Grill', 4, 3), (30, 'Moule a gateau', 4, 3),
-- Membre 4 
(31, 'Palette maquillage', 1, 4), (32, 'Epilateur', 1, 4), (33, 'Masque visage', 1, 4),
(34, 'Ponceuse', 2, 4), (35, 'Clous', 2, 4), (36, 'Perforateur', 2, 4),
(37, 'Compresseur', 3, 4), (38, 'Cle plate', 3, 4),
(39, 'Robot cuiseur', 4, 4), (40, 'Planche a decouper', 4, 4);

INSERT INTO vemprunt (id_emprunt, id_objet, id_membre, date_emprunt, date_retour) VALUES
(1, 1, 2, '2025-07-01', '2025-07-15'),
(2, 5, 3, '2025-07-02', '2025-07-20'),
(3, 10, 4, '2025-07-03', '2025-07-18'),
(4, 12, 1, '2025-07-04', '2025-07-16'),
(5, 15, 4, '2025-07-05', '2025-07-19'),
(6, 20, 3, '2025-07-06', '2025-07-22'),
(7, 25, 1, '2025-07-07', '2025-07-17'),
(8, 30, 2, '2025-07-08', '2025-07-21'),
(9, 35, 3, '2025-07-09', '2025-07-23'),
(10, 40, 1, '2025-07-10', '2025-07-24');

ALTER TABLE vobjet ADD image_principale VARCHAR(255) DEFAULT '../Uploads/images/default.jpg';