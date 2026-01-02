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
    <title>Gallaxy Paint - Panier</title>
    <link rel="icon" href="telechargement/Caroucelle/icon.ico" type="image/x-icon">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex flex-col">

<nav class="bg-gray-900 text-white shadow-md sticky top-0 z-50">
  <div class="max-w-7xl mx-auto flex items-center justify-between px-4 py-3">
    <a href="index.php" class="text-2xl font-bold">Gallaxy Paint</a>
    <div class="space-x-6 hidden md:flex">
      <a href="index.php" class="hover:text-blue-400">Accueil</a>
      <a href="produit.php" class="hover:text-blue-400">Produits</a>
      <a href="panier.php" class="relative hover:text-blue-400 flex items-center gap-1">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13l-1.3 5.2a1 1 0 001 1.3h12.6M7 13l.4-2M10 21h4"/>
        </svg>
        Panier
        <span class="bg-blue-600 text-white text-xs font-bold px-2 py-0.5 rounded-full">
          <?= array_sum(array_column($_SESSION['panier'], 'quantite')) ?>
        </span>
      </a>
    </div>
  </div>
</nav>

<main class="flex-grow max-w-7xl mx-auto p-6">
  <h2 class="text-2xl font-bold mb-6 text-gray-800"> Mon Panier</h2>

  <?php if (empty($_SESSION['panier'])): ?>
    <div class="bg-blue-50 border border-blue-300 text-blue-700 p-4 rounded-lg">Votre panier est vide.</div>
  <?php else: ?>
    <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
      <?php foreach ($_SESSION['panier'] as $id => $item): ?>
        <div class="bg-white shadow-md rounded-xl overflow-hidden flex flex-col">
          <img src="telechargement/<?= htmlspecialchars($item['photo']) ?>" class="h-48 w-full object-cover">
          <div class="p-4 flex flex-col flex-grow">
            <h3 class="font-semibold text-gray-800 text-lg mb-2"><?= htmlspecialchars($item['nom']) ?></h3>
            <p class="text-gray-600">Prix : <?= $item['prix'] ?> $ | Qté : <?= $item['quantite'] ?></p>
            <p class="text-gray-800 font-bold mt-2">Sous-total : <?= $item['prix'] * $item['quantite'] ?> $</p>
            <a href="panier.php?action=remove&id=<?= $id ?>" class="mt-4 bg-red-600 text-white py-2 rounded-lg hover:bg-red-700 text-center text-sm">🗑️ Retirer</a>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <div class="mt-10 flex justify-end">
      <div class="bg-white shadow-md rounded-xl p-6 w-full sm:w-96 border border-gray-200">
        <h4 class="text-xl font-bold text-right mb-6">Total : <span class="text-green-600"><?= $total ?> $</span></h4>
        <div class="flex flex-col gap-3">
          <a href="panier.php?action=clear" class="bg-gray-200 text-gray-700 py-3 rounded-xl hover:bg-gray-300 text-center font-semibold transition">Vider le panier</a>
          <!-- ✅ Modification ici : redirection vers le script de traitement -->
          <a href="valider_whatsapp.php" class="bg-green-600 text-white py-3 rounded-xl hover:bg-green-700 text-center font-bold shadow-lg transition flex items-center justify-center gap-2">
             <img src="https://upload.wikimedia.org/wikipedia/commons/6/6b/WhatsApp.svg" class="w-5 h-5" alt=""> Commander via WhatsApp
          </a>
          <a href="checkout.php" class="bg-blue-600 text-white py-3 rounded-xl hover:bg-blue-700 text-center font-bold shadow-lg transition">Commander via Site</a>
        </div>
      </div>
    </div>
  <?php endif; ?>
</main>
</body>
</html>