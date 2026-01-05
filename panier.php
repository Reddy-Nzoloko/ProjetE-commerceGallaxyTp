<?php
session_start();
include "connexion.php";

// ✅ Initialiser le panier
if (!isset($_SESSION['panier'])) {
    $_SESSION['panier'] = [];
}

// ✅ Ajouter un produit
if (isset($_GET['action']) && $_GET['action'] == "add" && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $pdo->prepare("SELECT * FROM produit WHERE id_produit=?");
    $stmt->execute([$id]);
    $prod = $stmt->fetch();
    if ($prod) {
        if (isset($_SESSION['panier'][$id])) {
            $_SESSION['panier'][$id]['quantite']++;
        } else {
            $_SESSION['panier'][$id] = [
                "nom" => $prod['nom_produit'],
                "prix" => $prod['prix'],
                "quantite" => 1,
                "photo" => $prod['photo']
            ];
        }
    }
    header("Location: panier.php");
    exit;
}

// ✅ Supprimer un produit
if (isset($_GET['action']) && $_GET['action'] == "remove" && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    unset($_SESSION['panier'][$id]);
    header("Location: panier.php");
    exit;
}

// ✅ Vider le panier
if (isset($_GET['action']) && $_GET['action'] == "clear") {
    $_SESSION['panier'] = [];
    header("Location: panier.php");
    exit;
}

$total = 0;
foreach ($_SESSION['panier'] as $item) {
    $total += $item['prix'] * $item['quantite'];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gallaxy Paint - Mon Panier</title>
    <link rel="icon" href="telechargement/Caroucelle/icon.ico" type="image/x-icon">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .btn-hover { transition: all 0.3s ease; }
        .btn-hover:hover { transform: translateY(-2px); }
    </style>
</head>
<body class="bg-gray-100 min-h-screen flex flex-col font-sans">

<nav class="bg-gray-900 text-white shadow-md sticky top-0 z-50">
  <div class="max-w-7xl mx-auto flex items-center justify-between px-6 py-4">
    <a href="index.php" class="text-2xl font-black tracking-tighter text-blue-500">GALLAXY<span class="text-white">PAINT</span></a>
    <div class="space-x-6 hidden md:flex items-center">
      <a href="index.php" class="hover:text-blue-400 transition">Accueil</a>
      <a href="produit.php" class="hover:text-blue-400 transition">Produits</a>
      <a href="panier.php" class="relative bg-blue-600 px-4 py-2 rounded-lg flex items-center gap-2 shadow-lg">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13l-1.3 5.2a1 1 0 001 1.3h12.6M7 13l.4-2M10 21h4"/>
        </svg>
        <span class="font-bold"><?= array_sum(array_column($_SESSION['panier'], 'quantite')) ?></span>
      </a>
    </div>
  </div>
</nav>

<main class="flex-grow max-w-7xl mx-auto w-full p-6">
  <div class="flex items-center justify-between mb-8">
    <h2 class="text-3xl font-extrabold text-gray-900">Mon Panier</h2>
    <a href="produit.php" class="text-blue-600 hover:underline flex items-center gap-1">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path d="M15 19l-7-7 7-7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
        Continuer mes achats
    </a>
  </div>

  <?php if (empty($_SESSION['panier'])): ?>
    <div class="bg-white border border-dashed border-gray-300 p-12 rounded-3xl text-center shadow-sm">
      <div class="text-6xl mb-4 text-gray-300">🛒</div>
      <p class="text-xl text-gray-500 mb-6">Votre panier est encore vide.</p>
      <a href="produit.php" class="bg-blue-600 text-white px-8 py-3 rounded-xl font-bold hover:bg-blue-700 transition">Découvrir nos peintures</a>
    </div>
  <?php else: ?>
    <div class="grid gap-8 lg:grid-cols-3">
      <!-- Liste des produits -->
      <div class="lg:col-span-2 space-y-4">
        <?php foreach ($_SESSION['panier'] as $id => $item): ?>
          <div class="bg-white shadow-sm rounded-2xl overflow-hidden flex items-center p-4 border border-gray-100 hover:shadow-md transition">
            <img src="telechargement/<?= htmlspecialchars($item['photo']) ?>" class="h-24 w-24 object-cover rounded-xl" onerror="this.src='telechargement/default.png'">
            <div class="ml-6 flex-grow">
              <h3 class="font-bold text-gray-800 text-lg"><?= htmlspecialchars($item['nom']) ?></h3>
              <p class="text-gray-500 text-sm italic"><?= $item['prix'] ?> $ / unité</p>
              <div class="flex items-center mt-2">
                 <span class="bg-gray-100 px-3 py-1 rounded-full text-sm font-medium">Quantité : <?= $item['quantite'] ?></span>
              </div>
            </div>
            <div class="text-right">
              <p class="text-xl font-black text-gray-900"><?= $item['prix'] * $item['quantite'] ?> $</p>
              <a href="panier.php?action=remove&id=<?= $id ?>" class="text-red-500 text-xs font-bold hover:text-red-700 mt-2 block uppercase tracking-wider">Supprimer</a>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

      <!-- Résumé et Actions -->
      <div class="lg:col-span-1">
        <div class="bg-white shadow-xl rounded-3xl p-8 sticky top-24 border border-gray-100">
          <h4 class="text-lg font-bold text-gray-400 uppercase tracking-widest mb-4 text-center">Récapitulatif</h4>
          <div class="flex justify-between items-center mb-6">
              <span class="text-gray-600">Total à payer</span>
              <span class="text-4xl font-black text-green-600"><?= $total ?> $</span>
          </div>
          
          <div class="flex flex-col gap-4">
            <!-- ✅ OPTION 1 : WhatsApp (Libre) -->
            <a href="valider_whatsapp.php" class="bg-[#25D366] text-white py-4 rounded-2xl hover:bg-[#20ba59] text-center font-bold shadow-lg btn-hover flex items-center justify-center gap-3">
               <img src="https://upload.wikimedia.org/wikipedia/commons/6/6b/WhatsApp.svg" class="w-6 h-6" alt=""> 
               Commander sur WhatsApp
            </a>

            <!-- ✅ OPTION 2 : Via Site (Connexion requise) -->
            <a href="checkout.php" class="bg-gray-900 text-white py-4 rounded-2xl hover:bg-black text-center font-bold shadow-lg btn-hover flex items-center justify-center gap-2">
               🛒 Commander via Site
            </a>

            <div class="relative py-4">
                <div class="absolute inset-0 flex items-center"><span class="w-full border-t border-gray-200"></span></div>
                <div class="relative flex justify-center text-xs uppercase"><span class="bg-white px-2 text-gray-400">Ou bien</span></div>
            </div>

            <a href="panier.php?action=clear" class="text-gray-400 hover:text-red-500 text-center text-sm font-medium transition">
                Vider le panier
            </a>
          </div>
        </div>
      </div>
    </div>
  <?php endif; ?>
</main>

<footer class="p-6 text-center text-gray-400 text-sm">
&copy; <?= date('Y') ?> Gallaxy Paint - Tous droits réservés.
</footer>

</body>
</html>