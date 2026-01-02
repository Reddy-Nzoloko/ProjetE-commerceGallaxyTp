<?php
session_start();
require 'connexion.php';

// On récupère l'ID du produit depuis l'URL
$id_produit = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id_produit <= 0) {
    header("Location: produit.php");
    exit;
}

try {
    $pdo->beginTransaction();

    // 1. Récupérer les infos du produit
    $stmtP = $pdo->prepare("SELECT * FROM produit WHERE id_produit = ?");
    $stmtP->execute([$id_produit]);
    $produit = $stmtP->fetch();

    if (!$produit) {
        die("Produit introuvable.");
    }

    // 2. Gérer le client "WhatsApp" par défaut
    $stmtC = $pdo->prepare("SELECT id_client FROM client WHERE nom = 'Client WhatsApp' LIMIT 1");
    $stmtC->execute();
    $client = $stmtC->fetch();

    if ($client) {
        $id_client = $client['id_client'];
    } else {
        $insClient = $pdo->prepare("INSERT INTO client (nom, telephone, adresse) VALUES ('Client WhatsApp', '0000000000', 'WhatsApp Direct')");
        $insClient->execute();
        $id_client = $pdo->lastInsertId();
    }

    // 3. Créer la commande (pour 1 seul article ici)
    $prix = floatval($produit['prix']);
    $stmtCmd = $pdo->prepare("INSERT INTO commande (id_client, mode_paiement, montant_total, statut) VALUES (?, 'WhatsApp Direct', ?, 'En attente')");
    $stmtCmd->execute([$id_client, $prix]);
    $id_commande = $pdo->lastInsertId();

    // 4. Ajouter le détail
    $stmtDet = $pdo->prepare("INSERT INTO commande_details (id_commande, id_produit, quantite, prix_unitaire) VALUES (?, ?, 1, ?)");
    $stmtDet->execute([$id_commande, $id_produit, $prix]);

    // 5. Construire le message WhatsApp
    $photo_url = "https://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/telechargement/" . $produit['photo'];
    
    $message = "COMMANDE DIRECTE Gallaxy Paint (#$id_commande)\n\n";
    $message .= "Je souhaite commander ce produit :\n";
    $message .= "{$produit['nom_produit']}\n";
    $message .= "Couleur: {$produit['couleur']}\n";
    $message .= "Prix: {$prix} $\n\n";
    $message .= "Photo: $photo_url";

    $whatsapp_link = "https://wa.me/243992261070?text=" . urlencode($message);

    $pdo->commit();

    // Redirection vers WhatsApp
    header("Location: " . $whatsapp_link);
    exit;

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    die("Erreur : " . $e->getMessage());
}