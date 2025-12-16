<?php
require 'connexion.php';
session_start();

// On suppose que le panier est stocké dans $_SESSION['panier']
// Structure attendue pour chaque item : ['id_produit'=>int, 'nom'=>string, 'prix'=>float, 'quantite'=>int]
$panier = $_SESSION['panier'] ?? [];

if(empty($panier)){
    // Si le panier est vide, on redirige vers index
    header('Location: index.php');
    exit;
}

// Calcul du total
$total = 0;
foreach($panier as $item){
    $total += $item['prix'] * $item['quantite'];
}
?>

<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link rel="icon" href="telechargement/Caroucelle/icon.ico" type="image/x-icon">
  <title>GallaxyPaint</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6">
  <div class="max-w-4xl mx-auto bg-white p-6 rounded-lg shadow">
    <h1 class="text-2xl font-semibold mb-4">Valider la commande (Paiement à la livraison)</h1>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <div>
        <form action="valider_commande.php" method="post" class="space-y-4">
          <div>
            <label class="block text-sm font-medium">Nom</label>
            <input type="text" name="nom" class="w-full border rounded p-2" required>
          </div>

          <div>
            <label class="block text-sm font-medium">Téléphone</label>
            <input type="tel" name="telephone" class="w-full border rounded p-2" required>
          </div>

          <div>
            <label class="block text-sm font-medium">Adresse</label>
            <textarea name="adresse" class="w-full border rounded p-2" required></textarea>
          </div>

          <div>
            <label class="block text-sm font-medium">Email (optionnel)</label>
            <input type="email" name="email" class="w-full border rounded p-2">
          </div>

          <input type="hidden" name="montant_total" value="<?=htmlspecialchars($total)?>">

          <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Commander maintenant (COD)</button>
        </form>
      </div>

      <div>
        <h2 class="text-lg font-medium mb-2">Récapitulatif du panier</h2>
        <div class="space-y-3">
          <?php foreach($panier as $p): ?>
            <div class="flex justify-between items-center border-b py-2">
              <div>
                <div class="font-semibold"><?=htmlspecialchars($p['nom'])?></div>
                <div class="text-sm text-gray-600">Qté: <?=intval($p['quantite'])?></div>
              </div>
              <div class="font-medium"><?=number_format($p['prix'] * $p['quantite'],2)?> $</div>
            </div>
          <?php endforeach; ?>

          <div class="flex justify-between font-bold text-lg pt-2">
            <div>Total</div>
            <div><?=number_format($total,2)?> $</div>
          </div>
        </div>
      </div>
    </div>

  </div>
</body>
</html>
