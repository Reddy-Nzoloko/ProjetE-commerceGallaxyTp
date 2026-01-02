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

    <div class="hidden md:flex space-x-6 text-sm">
      <a href="index.php" class="hover:text-blue-400 transition">Accueil</a>
      <a href="produit.php" class="text-blue-400 font-semibold">Produits</a>
      <a href="panier.php" class="relative hover:text-blue-400 flex items-center gap-1">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
          <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13l-1.3 5.2a1 1 0 001 1.3h12.6M10 21h4"/>
        </svg>
        Panier
        <?php if ($nb_articles > 0): ?>
          <span class="bg-blue-600 text-[10px] text-white px-1.5 py-0.5 rounded-full"><?= $nb_articles ?></span>
        <?php endif; ?>
      </a>
    </div>

    <button id="menuBtn" class="md:hidden text-white">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
      </svg>
    </button>
  </div>
</nav>

<!-- ✅ Contenu principal -->
<div class="max-w-7xl mx-auto px-4 py-8 grid grid-cols-1 md:grid-cols-4 gap-6">

  <!-- Sidebar catégories -->
  <aside class="md:col-span-1">
    <div class="bg-white shadow-sm rounded-2xl p-5 sticky top-24">
        <h3 class="text-lg font-bold mb-4 text-gray-900">Catégories</h3>
        <ul class="space-y-1">
          <li>
            <a href="produit.php" class="block px-4 py-2.5 rounded-xl transition <?= $id_categorie == 0 ? 'bg-blue-600 text-white shadow-md' : 'hover:bg-gray-50 text-gray-600' ?>">
              Tous les produits
            </a>
          </li>
          <?php foreach ($categories as $c): ?>
            <li>
              <a href="produit.php?categorie=<?= $c['id_categorie'] ?>"
                 class="block px-4 py-2.5 rounded-xl transition <?= $id_categorie == $c['id_categorie'] ? 'bg-blue-600 text-white shadow-md' : 'hover:bg-gray-50 text-gray-600' ?>">
                <?= htmlspecialchars($c['nom_categorie']) ?>
              </a>
            </li>
          <?php endforeach; ?>
        </ul>
    </div>
  </aside>

  <!-- Liste des produits -->
  <main class="md:col-span-3">
    <div class="flex items-center justify-between mb-8">
        <h2 class="text-2xl font-black text-gray-900 uppercase tracking-tight">
          <?= $id_categorie > 0 ? "Rayon : " . htmlspecialchars($produits[0]['nom_categorie'] ?? 'Catégorie') : "Nos Produits" ?>
        </h2>
        <span class="text-sm text-gray-500 font-medium"><?= count($produits) ?> articles disponibles</span>
    </div>

    <?php if (count($produits) > 0): ?>
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
        <?php foreach ($produits as $p): ?>
          <div class="group bg-white rounded-3xl overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 flex flex-col border border-gray-100">
            <div class="relative overflow-hidden aspect-square">
                <img src="telechargement/<?= htmlspecialchars($p['photo']) ?>" class="w-full h-full object-cover transition duration-500 group-hover:scale-110" alt="<?= htmlspecialchars($p['nom_produit']) ?>">
                <div class="absolute top-4 right-4 bg-white/90 backdrop-blur px-3 py-1 rounded-full text-sm font-bold text-gray-900 shadow-sm">
                    <?= $p['prix'] ?> $
                </div>
            </div>
            
            <div class="p-5 flex flex-col flex-grow">
              <h5 class="font-bold text-gray-900 text-lg mb-1 leading-tight"><?= htmlspecialchars($p['nom_produit']) ?></h5>
              <div class="flex items-center gap-2 mb-4">
                  <span class="text-[10px] uppercase tracking-widest font-bold bg-gray-100 text-gray-500 px-2 py-0.5 rounded"><?= htmlspecialchars($p['couleur']) ?></span>
                  <span class="text-[10px] uppercase tracking-widest font-bold bg-blue-50 text-blue-600 px-2 py-0.5 rounded">REF: <?= htmlspecialchars($p['code_produit']) ?></span>
              </div>

              <div class="mt-auto grid grid-cols-2 gap-2">
                <a href="panier.php?action=add&id=<?= $p['id_produit'] ?>"
                   class="flex items-center justify-center gap-2 bg-gray-900 hover:bg-black text-white py-3 rounded-xl text-xs font-bold transition">
                  Panier
                </a>

                <!-- ✅ Modification ici : Envoi vers le script de validation directe -->
                <a href="valider_whatsapp_direct.php?id=<?= $p['id_produit'] ?>"
                   class="flex items-center justify-center gap-2 bg-green-500 hover:bg-green-600 text-white py-3 rounded-xl text-xs font-bold transition">
                  WhatsApp
                </a>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <div class="bg-white p-20 rounded-3xl text-center shadow-sm">
          <p class="text-gray-400 font-medium text-lg">Aucun produit dans cette vitrine pour le moment.</p>
      </div>
    <?php endif; ?>
  </main>
</div>

<footer class="bg-gray-900 text-white py-12 text-center mt-20 border-t border-white/5">
  <p class="text-gray-500 text-sm">© 2025 Gallaxy Paint — par RedDev</p>
</footer>

<script>
  document.getElementById("menuBtn").addEventListener("click", () => {
    document.getElementById("mobileMenu").classList.toggle("hidden");
  });
</script>

</body>
</html>