CREATE DATABASE GallaxyAvecPaiement;

USE GallaxyAvecPaiement;

-- Table des administrateurs
CREATE TABLE admin (
    id_admin INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100)
);

-- Table des catégories de produits
CREATE TABLE categorie (
    id_categorie INT AUTO_INCREMENT PRIMARY KEY,
    nom_categorie VARCHAR(100) NOT NULL -- Exemple: Souliers, Sandales, Polos, Pantalons, Chemises
);

-- Table des produits
CREATE TABLE produit (
    id_produit INT AUTO_INCREMENT PRIMARY KEY,
    id_categorie INT,
    nom_produit VARCHAR(100) NOT NULL,
    code_produit VARCHAR(50) NOT NULL, -- numéro du produit ou référence
    couleur VARCHAR(50),
    taille VARCHAR(20),
    prix DECIMAL(10,2),
    description TEXT,
    photo VARCHAR(255), -- chemin ou URL de l'image
    date_ajout TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_categorie) REFERENCES categorie(id_categorie) ON DELETE SET NULL
);

--Tales client
CREATE TABLE client (
    id_client INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100),
    telephone VARCHAR(20) NOT NULL,
    adresse TEXT,
    email VARCHAR(100)
    password VARCHAR(255) NOT NULL,
);



-- table commande 
CREATE TABLE commande (
    id_commande INT AUTO_INCREMENT PRIMARY KEY,
    id_client INT,
    date_commande TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    statut VARCHAR(50) DEFAULT 'En attente',   -- En attente, En préparation, Livré, Annulé
    mode_paiement VARCHAR(50) DEFAULT 'Paiement à la livraison', 
    montant_total DECIMAL(10,2),
    FOREIGN KEY (id_client) REFERENCES client(id_client) ON DELETE SET NULL
);

-- Table details commande 
CREATE TABLE commande_details (
    id_commande_details INT AUTO_INCREMENT PRIMARY KEY,
    id_commande INT,
    id_produit INT,
    quantite INT DEFAULT 1,
    prix_unitaire DECIMAL(10,2),
    FOREIGN KEY (id_commande) REFERENCES commande(id_commande) ON DELETE CASCADE,
    FOREIGN KEY (id_produit) REFERENCES produit(id_produit) ON DELETE CASCADE
);

--Table paiement
CREATE TABLE paiement (
    id_paiement INT AUTO_INCREMENT PRIMARY KEY,
    id_commande INT,
    montant DECIMAL(10,2),
    statut VARCHAR(50) DEFAULT 'Non payé',   -- Non payé, Payé
    date_paiement TIMESTAMP NULL,
    FOREIGN KEY (id_commande) REFERENCES commande(id_commande) ON DELETE CASCADE
);


---TRIGGERR POUR LE PROJET 
DELIMITER $$

CREATE TRIGGER trg_calcul_montant_commande
AFTER INSERT ON commande_details
FOR EACH ROW
BEGIN
    UPDATE commande
    SET montant_total = (
        SELECT SUM(quantite * prix_unitaire)
        FROM commande_details
        WHERE id_commande = NEW.id_commande
    )
    WHERE id_commande = NEW.id_commande;
END$$

DELIMITER ;


DELIMITER $$

CREATE TRIGGER trg_update_montant_commande
AFTER UPDATE ON commande_details
FOR EACH ROW
BEGIN
    UPDATE commande
    SET montant_total = (
        SELECT SUM(quantite * prix_unitaire)
        FROM commande_details
        WHERE id_commande = NEW.id_commande
    )
    WHERE id_commande = NEW.id_commande;
END$$

DELIMITER ;


DELIMITER $$

CREATE TRIGGER trg_delete_montant_commande
AFTER DELETE ON commande_details
FOR EACH ROW
BEGIN
    UPDATE commande
    SET montant_total = (
        SELECT IFNULL(SUM(quantite * prix_unitaire), 0)
        FROM commande_details
        WHERE id_commande = OLD.id_commande
    )
    WHERE id_commande = OLD.id_commande;
END$$

DELIMITER ;


DELIMITER $$

CREATE TRIGGER trg_create_paiement
AFTER INSERT ON commande
FOR EACH ROW
BEGIN
    INSERT INTO paiement (id_commande, montant, statut)
    VALUES (NEW.id_commande, NEW.montant_total, 'Non payé');
END$$

DELIMITER ;



DELIMITER $$

CREATE TRIGGER trg_check_montant_paiement
BEFORE INSERT ON paiement
FOR EACH ROW
BEGIN
    IF NEW.montant <= 0 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Montant de paiement invalide';
    END IF;
END$$

DELIMITER ;


