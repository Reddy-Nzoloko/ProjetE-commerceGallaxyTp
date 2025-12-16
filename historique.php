<?php
require 'connexion.php';
session_start();

$telephone = trim($_GET['telephone'] ?? '');
$id_commande_view = intval($_GET['id'] ?? 0);
$commandes = [];
$details = [];

if($telephone !== ''){
    $stmt = $pdo->prepare('SELECT c.*, p.statut as paiement_statut FROM commande c LEFT JOIN paiement p ON p.id_commande = c.id_commande LEFT JOIN client cl ON cl.id_client = c.id_client WHERE cl.telephone = ? ORDER BY c.date_commande DESC');
    $stmt->execute([$telephone]);
    $commandes = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

if($id_commande_view > 0){
    $stmt = $pdo->prepare('SELECT cd.*, pr.nom_produit FROM commande_details cd LEFT JOIN produit pr ON pr.id_produit = cd.id_produit WHERE cd.id_commande = ?');
    $stmt->execute([$id_commande_view]);
    $details = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Gallaxy Paint</title>
<link rel="icon" href="telechargement/Caroucelle/icon.ico" type="image/x-icon">

  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6">
  <div class="max-w-5xl mx-auto bg-white p-6 rounded shadow">
    <h1 class="text-2xl font-semibold mb-4">Historique des commandes</h1>

    <form method="get" class="mb-6">
      <div class="flex gap-2">
        <input type="text" name="telephone" placeholder="Entrez votre numéro de téléphone" value="<?=htmlspecialchars($telephone)?>" class="flex-1 border rounded p-2">
        <button class="bg-blue-600 text-white px-4 py-2 rounded">Voir</button>
      </div>
    </form>

    <?php if(!empty($commandes)): ?>
      <div class="space-y-4">
        <?php foreach($commandes as $cmd): ?>
          <div class="border rounded p-3">
            <div class="flex justify-between items-center">
              <div>
                <div class="font-semibold">Commande #<?=$cmd['id_commande']?></div>
                <div class="text-sm text-gray-600">Le <?=htmlspecialchars($cmd['date_commande'])?></div>
              </div>
              <div class="text-right">
                <div>Montant: <strong><?=number_format($cmd['montant_total'],2)?> $</strong></div>
                <div>Statut: <strong><?=htmlspecialchars($cmd['statut'])?></strong></div>
                <div>Paiement: <strong><?=htmlspecialchars($cmd['paiement_statut'] ?? 'Non payé')?></strong></div>
                <div class="mt-2">
                  <a href="?telephone=<?=urlencode($telephone)?>&id=<?=$cmd['id_commande']?>" class="text-blue-600">Voir détails</a>
                </div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

      <?php if(!empty($details)): ?>
        <h2 class="text-lg font-medium mt-6 mb-2">Détails commande #<?=$id_commande_view?></h2>
        <div class="border rounded p-3">
          <?php foreach($details as $d): ?>
            <div class="flex justify-between py-2 border-b">
              <div>
                <div class="font-semibold"><?=htmlspecialchars($d['nom_produit'] ?? 'Produit supprimé')?></div>
                <div class="text-sm text-gray-600">Qté: <?=intval($d['quantite'])?></div>
              </div>
              <div class="font-medium"><?=number_format($d['prix_unitaire'] * $d['quantite'],2)?> $</div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

    <?php elseif($telephone !== ''): ?>
      <div class="text-gray-600">Aucune commande trouvée pour ce numéro.</div>
    <?php endif; ?>

  </div>
</body>
</html><?php
// Ceci est un fichier de base pour integrer :
// 1. Creation de commande
// 2. Paiement a la livraison
// 3. Historique d'achat client
// Ce fichier sert comme modele. A adapter avec index.php, panier.php, dashboard.php

// ------------------------
// CONNEXION A LA BASE
// ------------------------
$pdo = new PDO("mysql:host=localhost;dbname=gallaxyAvecPaiement;charset=utf8", "root", "");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// ------------------------
// FONCTION : CREER CLIENT
// ------------------------
function creerClient($nom, $telephone, $adresse, $email) {
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO client (nom, telephone, adresse, email) VALUES (?, ?, ?, ?)");
    $stmt->execute([$nom, $telephone, $adresse, $email]);
    return $pdo->lastInsertId();
}

// ------------------------
// FONCTION : CREER COMMANDE
// ------------------------
function creerCommande($id_client, $montant_total) {
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO commande (id_client, montant_total) VALUES (?, ?)");
    $stmt->execute([$id_client, $montant_total]);
    return $pdo->lastInsertId();
}

// ------------------------
// AJOUT PRODUITS DANS LA COMMANDE
// ------------------------
function ajouterProduitCommande($id_commande, $id_produit, $quantite, $prix) {
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO commande_details (id_commande, id_produit, quantite, prix_unitaire) VALUES (?, ?, ?, ?)");
    $stmt->execute([$id_commande, $id_produit, $quantite, $prix]);
}

// ------------------------
// ENREGISTRER UN PAIEMENT COD
// ------------------------
function creerPaiement($id_commande, $montant) {
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO paiement (id_commande, montant) VALUES (?, ?)");
    $stmt->execute([$id_commande, $montant]);
}

// ------------------------
// HISTORIQUE DES COMMANDES D'UN CLIENT
// ------------------------
function historiqueCommandes($telephone) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT c.*, cl.nom, cl.telephone
                           FROM commande c
                           LEFT JOIN client cl ON c.id_client = cl.id_client
                           WHERE cl.telephone = ?
                           ORDER BY c.date_commande DESC");
    $stmt->execute([$telephone]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>