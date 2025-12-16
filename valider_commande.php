<?php
require 'connexion.php';
session_start();

if($_SERVER['REQUEST_METHOD'] !== 'POST'){
    header('Location: index.php');
    exit;
}

$nom = trim($_POST['nom'] ?? '');
$telephone = trim($_POST['telephone'] ?? '');
$adresse = trim($_POST['adresse'] ?? '');
$email = trim($_POST['email'] ?? '');
$montant_total = floatval($_POST['montant_total'] ?? 0);
$panier = $_SESSION['panier'] ?? [];

if(empty($panier) || empty($nom) || empty($telephone) || empty($adresse)){
    die('Données manquantes.');
}

try{
    $pdo->beginTransaction();

    // 1) Vérifier si le client existe déjà (par téléphone)
    $stmt = $pdo->prepare('SELECT id_client FROM client WHERE telephone = ? LIMIT 1');
    $stmt->execute([$telephone]);
    $client = $stmt->fetch(PDO::FETCH_ASSOC);

    if($client){
        $id_client = $client['id_client'];
        // Optionnel : mettre à jour nom/adresse/email
        $up = $pdo->prepare('UPDATE client SET nom = ?, adresse = ?, email = ? WHERE id_client = ?');
        $up->execute([$nom, $adresse, $email, $id_client]);
    } else {
        $ins = $pdo->prepare('INSERT INTO client (nom, telephone, adresse, email) VALUES (?, ?, ?, ?)');
        $ins->execute([$nom, $telephone, $adresse, $email]);
        $id_client = $pdo->lastInsertId();
    }

    // 2) Créer la commande
    $insCmd = $pdo->prepare('INSERT INTO commande (id_client, montant_total, mode_paiement, statut) VALUES (?, ?, ?, ?)');
    $insCmd->execute([$id_client, $montant_total, 'Paiement à la livraison', 'En attente']);
    $id_commande = $pdo->lastInsertId();

    // 3) Ajouter les détails de la commande
    $insDet = $pdo->prepare('INSERT INTO commande_details (id_commande, quantite, prix_unitaire) VALUES ( ?, ?, ?)');
    foreach($panier as $item){
        $insDet->execute([$id_commande,  $item['quantite'], $item['prix']]);
    }

    // 4) Créer l'enregistrement de paiement (Non payé pour COD)
    $insPay = $pdo->prepare('INSERT INTO paiement (id_commande, montant, statut) VALUES (?, ?, ?)');
    $insPay->execute([$id_commande, $montant_total, 'Non payé']);

    $pdo->commit();

    // Optionnel : envoyer message WhatsApp (lien)
    $message = "Nouvelle commande%0A#ID: $id_commande%0AMontant: " . number_format($montant_total,2) . "%0AClient: " . rawurlencode($nom) . "%0ATel: " . rawurlencode($telephone);
    // Remplace avec ton numéro de réception, ex: 243XXXXXXXXX
    $whatsappNumber = '243971920530';
    $waLink = "https://wa.me/" . $whatsappNumber . "?text= Votre commande est bel est bien arrivée" . $message;

    // Vider le panier
    unset($_SESSION['panier']);

} catch(Exception $e){
    $pdo->rollBack();
    die('Erreur lors de la création de la commande : ' . $e->getMessage());
}
?>

<!doctype html>
<html lang="fr">
<link rel="icon" href="telechargement/Caroucelle/icon.ico" type="image/x-icon">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link rel="icon" href="telechargement/Caroucelle/icon.ico" type="image/x-icon">
  <title>Commande confirmée</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6">
  <div class="max-w-xl mx-auto bg-white p-6 rounded shadow">
    <h1 class="text-2xl font-semibold mb-4">Commande enregistrée</h1>
    <p class="mb-3">Merci <?=htmlspecialchars($nom)?>, ta commande a été enregistrée avec l'ID <strong>#<?=$id_commande?></strong>.</p>
    <p class="mb-3">Mode de paiement : <strong>Paiement à la livraison</strong></p>
    <p class="mb-3">Montant total : <strong><?=number_format($montant_total,2)?> $</strong></p>

    <div class="space-y-2">
      <a href="<?=htmlspecialchars($waLink)?>" target="_blank" class="inline-block bg-green-500 text-white px-4 py-2 rounded">Envoyer la commande par WhatsApp</a>
      <a href="index.php" class="inline-block bg-gray-200 px-4 py-2 rounded">Retour à l'accueil</a>
      <a href="historique.php" class="inline-block bg-blue-600 text-white px-4 py-2 rounded">Voir mon historique</a>
    </div>
  </div>
</body>
</html>