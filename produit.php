<?php
session_start();
include "connexion.php";

// ✅ Récupérer les catégories
$categories = $pdo->query("SELECT * FROM categorie")->fetchAll(PDO::FETCH_ASSOC);

// ✅ Catégorie sélectionnée
$id_categorie = isset($_GET['categorie']) ? intval($_GET['categorie']) : 0;

if ($id_categorie > 0) {
    $stmt = $pdo->prepare("SELECT * FROM produit WHERE id_categorie = ? ORDER BY date_ajout DESC");
    $stmt->execute([$id_categorie]);
} else {
    $stmt = $pdo->query("SELECT * FROM produit ORDER BY date_ajout DESC");
}
$produits = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ✅ Compteur d’articles
$nb_articles = isset($_SESSION['panier']) ? array_sum(array_column($_SESSION['panier'], 'quantite')) : 0;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Gallaxy Paint - Produits</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="icon" href="telechargement/Caroucelle/icon.ico" type="image/x-icon">
</head>
<body class="bg-gray-100 text-gray-800">

<!-- ✅ Navbar -->
<nav class="bg-gray-900 text-white shadow sticky top-0 z-50">
  <div class="max-w-7xl mx-auto px-4 flex justify-between items-center h-16">
    <a href="index.php" class="text-xl font-bold">Gallaxy Paint</a>

    <div class="hidden md:flex space-x-6">
      <a href="index.php" class="hover:text-blue-400">Accueil</a>
      <a href="produit.php" class="text-blue-400 font-semibold">Produits</a>
      <a href="login.php" class="hover:text-blue-400">Connexion</a>
      <a href="panier.php" class="relative hover:text-blue-400 flex items-center">
        <!-- 🛒 Icon -->
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
             stroke-width="2" stroke="currentColor" class="w-5 h-5">
          <path stroke-linecap="round" stroke-linejoin="round"
                d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13l-1.3 5.2a1 1 0 001 1.3h12.6M10 21h4"/>
        </svg>
        <?php if ($nb_articles > 0): ?>
          <span class="absolute -top-2 -right-3 bg-red-600 text-xs text-white px-1.5 py-0.5 rounded-full"><?= $nb_articles ?></span>
        <?php endif; ?>
      </a>
    </div>

    <!-- Menu mobile -->
    <button id="menuBtn" class="md:hidden text-white">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
           viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round"
              d="M4 6h16M4 12h16M4 18h16"/>
      </svg>
    </button>
  </div>

  <!-- Menu mobile -->
  <div id="mobileMenu" class="hidden bg-gray-800 px-4 pb-3 space-y-2">
    <a href="index.php" class="block hover:text-blue-400">Accueil</a>
    <a href="produit.php" class="block text-blue-400 font-semibold">Produits</a>
    <a href="login.php" class="block hover:text-blue-400">Connexion</a>
    <a href="panier.php" class="flex items-center gap-2 hover:text-blue-400">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
           stroke-width="2" stroke="currentColor" class="w-5 h-5">
        <path stroke-linecap="round" stroke-linejoin="round"
              d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13l-1.3 5.2a1 1 0 001 1.3h12.6M10 21h4"/>
      </svg>
      Panier
    </a>
  </div>
</nav>

<!-- ✅ Contenu principal -->
<div class="max-w-7xl mx-auto px-4 py-8 grid grid-cols-1 md:grid-cols-4 gap-6">

  <!-- Sidebar catégories -->
  <aside class="bg-white shadow rounded-lg p-4">
    <h3 class="text-lg font-bold mb-3 border-b pb-2">Catégories</h3>
    <ul class="space-y-2">
      <li>
        <a href="produit.php"
           class="block px-3 py-2 rounded-md <?= $id_categorie == 0 ? 'bg-blue-100 text-blue-700 font-semibold' : 'hover:bg-gray-100' ?>">
          Tous les produits
        </a>
      </li>
      <?php foreach ($categories as $c): ?>
        <li>
          <a href="produit.php?categorie=<?= $c['id_categorie'] ?>"
             class="block px-3 py-2 rounded-md <?= $id_categorie == $c['id_categorie'] ? 'bg-blue-100 text-blue-700 font-semibold' : 'hover:bg-gray-100' ?>">
            <?= htmlspecialchars($c['nom_categorie']) ?>
          </a>
        </li>
      <?php endforeach; ?>
    </ul>
  </aside>

  <!-- Liste des produits -->
  <main class="md:col-span-3">
    <h2 class="text-2xl font-bold mb-6 text-center">
      <?= $id_categorie > 0
        ? "Produits de la catégorie : " . htmlspecialchars($categories[array_search($id_categorie, array_column($categories, 'id_categorie'))]['nom_categorie'])
        : "Tous les produits" ?>
    </h2>

    <?php if (count($produits) > 0): ?>
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php foreach ($produits as $p): 
          $photo_url = "https://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/telechargement/" . $p['photo'];
          $whatsapp_message = "Produit : " . $p['nom_produit'] . 
                              " | Prix : " . $p['prix'] . "$ | Photo : " . $photo_url;
          $whatsapp_link = "https://wa.me/243992261070?text=" . urlencode($whatsapp_message);
        ?>
          <div class="bg-white shadow-lg rounded-xl overflow-hidden hover:shadow-2xl transition flex flex-col">
            <img src="telechargement/<?= htmlspecialchars($p['photo']) ?>" class="w-full h-56 object-cover" alt="<?= htmlspecialchars($p['nom_produit']) ?>">
            <div class="p-4 flex flex-col flex-grow">
              <h5 class="font-bold text-lg mb-2"><?= htmlspecialchars($p['nom_produit']) ?></h5>
              <p class="text-gray-600 text-sm mb-1">Code : <?= htmlspecialchars($p['code_produit']) ?></p>
              <p class="text-gray-600 text-sm mb-2">Couleur : <?= htmlspecialchars($p['couleur']) ?></p>
              <p class="text-lg font-semibold text-gray-800 mb-3"><?= $p['prix'] ?> $</p>

              <div class="mt-auto flex justify-between">
                <a href="panier.php?action=add&id=<?= $p['id_produit'] ?>"
                   class="flex items-center gap-1 bg-blue-600 hover:bg-blue-700 text-white px-3 py-1.5 rounded-md text-sm transition">
                  <!-- Icon Panier -->
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                       viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                       class="w-4 h-4">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13l-1.3 5.2a1 1 0 001 1.3h12.6M10 21h4"/>
                  </svg>
                  Ajouter
                </a>

                <a href="<?= $whatsapp_link ?>" target="_blank"
                   class="flex items-center gap-1 bg-green-600 hover:bg-green-700 text-white px-3 py-1.5 rounded-md text-sm transition">
                  <!-- Icon WhatsApp -->
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                       viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                       class="w-4 h-4">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M21.75 12A9.75 9.75 0 1112 2.25a9.75 9.75 0 019.75 9.75zM12 17.25h.008v.008H12v-.008zM12 13.5v-4.5"/>
                  </svg>
                  Commander
                </a>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <p class="text-center text-gray-600">Aucun produit trouvé dans cette catégorie.</p>
    <?php endif; ?>
  </main>
</div>

<!-- ✅ Footer -->
<footer class="bg-gray-900 text-white py-6 text-center mt-10">
  <p>© RedDev 2025 Gallaxy Paint</p>
</footer>

<!-- ✅ Script burger -->
<script>
  document.getElementById("menuBtn").addEventListener("click", () => {
    document.getElementById("mobileMenu").classList.toggle("hidden");
  });
</script>

</body>
</html>
