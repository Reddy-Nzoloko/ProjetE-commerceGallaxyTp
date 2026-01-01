<?php
session_start();
require 'connexion.php';

/**
 * Ce script traite la validation finale de la commande.
 * 1. Il vérifie la session client et le panier.
 * 2. Il insère la commande (avec le montant total pour satisfaire les triggers).
 * 3. Il insère chaque article dans les détails de la commande.
 * 4. Il vide le panier et affiche un message de succès.
 */

// Sécurité : Vérifier si le client est connecté et si le panier n'est pas vide
if (!isset($_SESSION['client_id']) || empty($_SESSION['panier'])) {
    header("Location: index.php");
    exit;
}

$id_client = $_SESSION['client_id'];
$mode_p = $_POST['mode_paiement'] ?? 'Paiement à la livraison';
$montant_total = floatval($_POST['montant_total'] ?? 0);
$panier = $_SESSION['panier'];

try {
    // Début de la transaction pour garantir l'intégrité des données
    $pdo->beginTransaction();

    // 1. Insertion dans la table 'commande'
    // IMPORTANT : On insère le montant_total immédiatement. 
    // Votre trigger 'trg_check_montant_paiement' bloque les paiements <= 0.
    $stmt = $pdo->prepare("INSERT INTO commande (id_client, mode_paiement, montant_total, statut) VALUES (?, ?, ?, 'En attente')");
    $stmt->execute([$id_client, $mode_p, $montant_total]);
    
    // Récupération de l'ID de la commande générée
    $id_commande = $pdo->lastInsertId();

    // 2. Insertion des détails dans 'commande_details'
    // Chaque insertion ici déclenchera vos triggers de mise à jour du montant
    $stmtDetail = $pdo->prepare("INSERT INTO commande_details (id_commande, id_produit, quantite, prix_unitaire) VALUES (?, ?, ?, ?)");
    
    foreach ($panier as $id_produit => $item) {
        $stmtDetail->execute([
            $id_commande,
            $id_produit,
            $item['quantite'],
            $item['prix']
        ]);
    }

    // Validation de toutes les étapes
    $pdo->commit();
    
    // Vider le panier après le succès de l'enregistrement
    unset($_SESSION['panier']);

    // Affichage de la page de succès avec Tailwind CSS (Thème sombre)
    ?>
    <!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <script src="https://cdn.tailwindcss.com"></script>
        <link rel="icon" href="telechargement/Caroucelle/icon.ico" type="image/x-icon">
        <title>Commande Confirmée - Gallaxy</title>
    </head>
    <body class="bg-gray-900 text-white flex items-center justify-center min-h-screen p-4">
        <div class="bg-gray-800 p-8 md:p-12 rounded-3xl shadow-2xl text-center max-w-lg border border-green-500/20">
            <!-- Icône de succès animée -->
            <div class="w-24 h-24 bg-green-500/10 text-green-500 rounded-full flex items-center justify-center mx-auto mb-8 text-5xl shadow-inner">
                ✓
            </div>
            
            <h1 class="text-3xl font-extrabold mb-4 text-white">Commande Enregistrée !</h1>
            <p class="text-gray-400 mb-8 leading-relaxed">
                Merci pour votre confiance. Votre commande <span class="text-blue-400 font-mono font-bold text-lg">#<?= $id_commande ?></span> a bien été transmise à notre équipe logistique.
            </p>
            
            <!-- Récapitulatif rapide -->
            <div class="bg-gray-900/50 p-6 rounded-2xl mb-8 text-left border border-gray-700">
                <div class="flex justify-between mb-2">
                    <span class="text-gray-500">Total payé à la livraison :</span>
                    <span class="text-green-400 font-bold"><?= number_format($montant_total, 2, ',', ' ') ?> $</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Mode de paiement :</span>
                    <span class="text-gray-200"><?= htmlspecialchars($mode_p) ?></span>
                </div>
            </div>

            <div class="flex flex-col gap-4">
                <a href="index.php" class="w-full bg-blue-600 px-8 py-4 rounded-xl font-bold hover:bg-blue-700 transition transform hover:scale-[1.02] active:scale-95 shadow-lg">
                    Retour à la boutique
                </a>
                <p class="text-xs text-gray-500 italic">Un email de confirmation vous a été envoyé.</p>
            </div>
        </div>
    </body>
    </html>
    <?php

} catch (Exception $e) {
    // En cas d'erreur, on annule tout ce qui a été fait
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    // Affichage d'une page d'erreur stylisée
    die("
    <!DOCTYPE html>
    <html lang='fr'>
    <head>
        <meta charset='UTF-8'>
        <script src='https://cdn.tailwindcss.com'></script>
    </head>
    <body class='bg-gray-900 text-white p-10 font-sans flex items-center justify-center min-h-screen'>
        <div class='bg-red-900/10 border border-red-500/50 p-8 rounded-2xl max-w-md text-center'>
            <div class='text-red-500 text-5xl mb-4 font-bold'>!</div>
            <h2 class='text-red-500 font-bold text-2xl mb-4'>Échec de l'enregistrement</h2>
            <p class='text-gray-300 mb-6'>Nous n'avons pas pu sauvegarder votre commande pour la raison suivante : <br><br>
               <span class='bg-black/30 p-2 rounded block text-sm font-mono'>" . htmlspecialchars($e->getMessage()) . "</span>
            </p>
            <a href='checkout.php' class='inline-block bg-gray-700 hover:bg-gray-600 px-6 py-2 rounded-lg transition font-bold'>
                Retourner au panier
            </a>
        </div>
    </body>
    </html>");
}
?>