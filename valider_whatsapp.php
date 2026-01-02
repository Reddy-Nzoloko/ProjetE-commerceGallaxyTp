<?php
session_start();
require 'connexion.php';

if (empty($_SESSION['panier'])) {
    header("Location: panier.php");
    exit;
}

$panier = $_SESSION['panier'];
$total = 0;
foreach ($panier as $item) { $total += $item['prix'] * $item['quantite']; }

try {
    $pdo->beginTransaction();

    // 1. Gérer le client "WhatsApp" par défaut
    // On vérifie si un client nommé 'Client WhatsApp' existe, sinon on le crée
    $stmt = $pdo->prepare("SELECT id_client FROM client WHERE nom = 'Client WhatsApp' LIMIT 1");
    $stmt->execute();
    $client = $stmt->fetch();

    if ($client) {
        $id_client = $client['id_client'];
    } else {
        $insClient = $pdo->prepare("INSERT INTO client (nom, telephone, adresse) VALUES ('Client WhatsApp', '0000000000', 'WhatsApp Order')");
        $insClient->execute();
        $id_client = $pdo->lastInsertId();
    }

    // 2. Insérer la commande
    $stmtCmd = $pdo->prepare("INSERT INTO commande (id_client, mode_paiement, montant_total, statut) VALUES (?, 'WhatsApp', ?, 'En attente')");
    $stmtCmd->execute([$id_client, $total]);
    $id_commande = $pdo->lastInsertId();

    // 3. Insérer les détails
    $stmtDet = $pdo->prepare("INSERT INTO commande_details (id_commande, id_produit, quantite, prix_unitaire) VALUES (?, ?, ?, ?)");
    
    // Message WhatsApp construction
    $message = "Nouvelle Commande Gallaxy Paint (#$id_commande)\n\n";
    
    foreach ($panier as $id_produit => $item) {
        // Enregistrement DB
        $stmtDet->execute([$id_commande, $id_produit, $item['quantite'], $item['prix']]);
        
        // Construction message
        $message .= "{$item['nom']}\n";
        $message .= "Qté: {$item['quantite']} | Prix: {$item['prix']}$\n\n";
    }

    $message .= "TOTAL: $total $";
    $whatsapp_link = "https://wa.me/243992261070?text=" . urlencode($message);

    $pdo->commit();

    // On vide le panier après l'enregistrement
    unset($_SESSION['panier']);

    // Redirection vers WhatsApp
    header("Location: " . $whatsapp_link);
    exit;

} catch (Exception $e) {
    $pdo->rollBack();
    die("Erreur lors de l'enregistrement de la commande WhatsApp : " . $e->getMessage());
}