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

// ✅ Préparer message WhatsApp
$message = " Nouvelle commande Gallaxy Paint:\n";
$total = 0;
foreach ($_SESSION['panier'] as $id => $item) {
    $subtotal = $item['prix'] * $item['quantite'];
    $total += $subtotal;

    // lien complet de la photo (si stockée dans dossier 'telechargement/')
    $photo_url = "https://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/telechargement/" . $item['photo'];

    $message .= "- " . $item['nom'] . "\n";
    $message .= "    Prix: " . $item['prix'] . "$ | Qté: " . $item['quantite'] . "\n";
    $message .= "    Photo: " . $photo_url . "\n\n";
}
$message .= " Total: " . $total . " $";
$whatsapp_link = "https://wa.me/243992261070?text=" . urlencode($message);
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

<!-- ✅ Navbar -->
<nav class="bg-gray-900 text-white shadow-md sticky top-0 z-50">
  <div class="max-w-7xl mx-auto flex items-center justify-between px-4 py-3">
    <a href="index.php" class="text-2xl font-bold">Gallaxy Paint</a>
    <div class="space-x-6 hidden md:flex">
      <a href="index.php" class="hover:text-blue-400">Accueil</a>
      <a href="produit.php" class="hover:text-blue-400">Produits</a>
      <a href="panier.php" class="relative hover:text-blue-400 flex items-center gap-1">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
             viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round"
                d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13l-1.3 5.2a1 1 0 001 1.3h12.6M7 13l.4-2M10 21h4"/>
        </svg>
        Panier
        <span class="bg-blue-600 text-white text-xs font-bold px-2 py-0.5 rounded-full">
          <?= array_sum(array_column($_SESSION['panier'], 'quantite')) ?>
        </span>
      </a>
    </div>

    <!-- ✅ Menu mobile -->
    <button id="menu-toggle" class="md:hidden focus:outline-none">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
           viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round"
              d="M4 6h16M4 12h16M4 18h16"/>
      </svg>
    </button>
  </div>

  <div id="mobile-menu" class="hidden flex-col px-4 pb-3 md:hidden bg-gray-800">
    <a href="index.php" class="py-2 border-b border-gray-700 hover:text-blue-400">Accueil</a>
    <a href="produit.php" class="py-2 border-b border-gray-700 hover:text-blue-400">Produits</a>
    <a href="panier.php" class="py-2 hover:text-blue-400 flex items-center gap-2">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
           viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round"
              d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13l-1.3 5.2a1 1 0 001 1.3h12.6M7 13l.4-2M10 21h4"/>
      </svg>
      Panier
    </a>
  </div>
</nav>

<!-- ✅ Contenu principal -->
<main class="flex-grow max-w-7xl mx-auto p-6">
  <h2 class="text-2xl font-bold mb-6 text-gray-800"> Mon Panier</h2>

  <?php if (empty($_SESSION['panier'])): ?>
    <div class="bg-blue-50 border border-blue-300 text-blue-700 p-4 rounded-lg">
      Votre panier est vide.
    </div>
  <?php else: ?>
    <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
      <?php foreach ($_SESSION['panier'] as $id => $item): ?>
        <div class="bg-white shadow-md rounded-xl overflow-hidden flex flex-col">
          <img src="telechargement/<?= htmlspecialchars($item['photo']) ?>" alt="<?= htmlspecialchars($item['nom']) ?>" class="h-48 w-full object-cover">
          <div class="p-4 flex flex-col flex-grow">
            <h3 class="font-semibold text-gray-800 text-lg mb-2"><?= htmlspecialchars($item['nom']) ?></h3>
            <p class="text-gray-600 mb-1">Monnaie <span class="font-semibold"><?= $item['prix'] ?> $</span></p>
            <p class="text-gray-600 mb-1"> Quantité : <span class="font-semibold"><?= $item['quantite'] ?></span></p>
            <p class="text-gray-800 font-bold mt-2">Sous-total : <?= $item['prix'] * $item['quantite'] ?> $</p>
            <a href="panier.php?action=remove&id=<?= $id ?>" class="mt-auto bg-red-600 text-white py-2 rounded-lg hover:bg-red-700 transition text-center">
              🗑️ Retirer
            </a>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <div class="mt-10 flex flex-col sm:flex-row justify-end items-center gap-4">
      <div class="bg-white shadow-md rounded-xl p-5 w-full sm:w-96">
        <h4 class="text-xl font-semibold text-right mb-4">Total : 
          <span class="text-green-600"><?= $total ?> $</span>
        </h4>
        <div class="flex flex-col gap-3">
          <a href="panier.php?action=clear" class="bg-yellow-500 text-white py-2 rounded-lg hover:bg-yellow-600 text-center transition">
             Vider le panier
          </a>
          <a href="<?= $whatsapp_link ?>" target="_blank" class="bg-green-600 text-white py-2 rounded-lg hover:bg-green-700 text-center transition">
             Commander via WhatsApp
          </a>
          <a href="checkout.php" class="bg-blue-600 text-white py-2 rounded-lg hover:bg-green-700 text-center transition">Commander maintenant</a>
        </div>
      </div>
    </div>
  <?php endif; ?>
</main>

<!-- ✅ Script Menu mobile -->
<script>
  document.getElementById("menu-toggle").addEventListener("click", () => {
    document.getElementById("mobile-menu").classList.toggle("hidden");
  });
</script>

</body>
</html>
