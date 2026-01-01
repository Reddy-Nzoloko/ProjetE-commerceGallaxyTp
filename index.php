<?php
session_start();
include "connexion.php";

// ✅ Initialiser panier si vide
if (!isset($_SESSION['panier'])) {
    $_SESSION['panier'] = [];
}

// Compteur d'articles dans le panier
$nb_panier = array_sum($_SESSION['panier']);

// Récupérer les produits récents
$stmt = $pdo->query("SELECT * FROM produit ORDER BY date_ajout DESC LIMIT 12");
$produits_recents = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les catégories
$categories = $pdo->query("SELECT * FROM categorie")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Gallaxy Paint</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- ✅ Tailwind CSS -->
  <script src="https://cdn.tailwindcss.com"></script>
  <!-- ✅ Heroicons -->
  <script src="https://unpkg.com/feather-icons"></script>
  <link rel="icon" href="telechargement/Caroucelle/icon.ico" type="image/x-icon">
</head>
<body class="bg-gray-100 text-gray-800">

<!-- ✅ Navbar -->
<nav class="bg-gray-900 text-[ivory] sticky top-0 z-50 shadow-md">
  <div class="container mx-auto px-4 py-3 flex items-center justify-between">
    <a href="index.php" class="text-2xl font-bold tracking-wide">Gallaxy Paint</a>
    <button id="menu-toggle" class="md:hidden focus:outline-none">
      <i data-feather="menu"></i>
    </button>
    <ul id="menu" class="hidden md:flex space-x-6 items-center">
      <li><a href="index.php" class="hover:text-orange-400 transition">Accueil</a></li>
      <li><a href="produit.php" class="hover:text-orange-400 transition">Produits</a></li>
      <li><a href="login.php" class="hover:text-orange-400 transition">Connexion</a></li>
      <li><a href="connexionClient.php" class="hover:text-orange-400 transition">Client</a></li>
      <!-- <li><a href="AjoutAdmin.php" class="hover:text-orange-400 transition">Ajout admin</a></li> -->

      <!-- <li><a href="AjoutAdmin.php">Ajout Admin</a></li> -->
      <li class="relative">
        <a href="panier.php" class="flex items-center hover:text-orange-400 transition">
          <i data-feather="shopping-cart"></i>
          <span class="ml-1">Panier</span>
          <?php if ($nb_panier > 0): ?>
            <span class="absolute -top-2 -right-3 bg-red-600 text-white text-xs rounded-full px-2"><?= $nb_panier ?></span>
          <?php endif; ?>
        </a>
      </li>
    </ul>
  </div>
  <ul id="mobile-menu" class="md:hidden hidden flex-col bg-gray-800 text-[ivory] space-y-2 py-3 text-center">
    <li><a href="index.php" class="block py-2 hover:bg-gray-700">Accueil</a></li>
    <li><a href="produit.php" class="block py-2 hover:bg-gray-700">Produits</a></li>
    <li><a href="login.php" class="block py-2 hover:bg-gray-700">Connexion</a></li>
    <li><a href="panier.php" class="block py-2 hover:bg-gray-700 flex justify-center items-center gap-1"><i data-feather="shopping-cart"></i> Panier</a></li>
  </ul>
</nav>

<!-- ✅ Carousel -->
<!-- ✅ Carousel (remplacer l'ancienne section) -->
<section aria-label="Carrousel" class="relative w-full h-[400px] overflow-hidden">
  <div class="relative w-full h-full">
    <!-- Slide 1 -->
    <div class="absolute inset-0 transition-opacity duration-700 ease-in-out slide opacity-100 z-10">
      <img src="telechargement/Caroucelle/h1_hero3.PNG" class="w-full h-full object-cover" alt="Image 1">
    </div>

    <!-- Slide 2 -->
    <div class="absolute inset-0 transition-opacity duration-700 ease-in-out slide opacity-0 z-0">
      <img src="telechargement/Caroucelle/Peinture.jpg" class="w-full h-full object-cover" alt="Image 2">
    </div>

    <!-- Slide 3 -->
    <div class="absolute inset-0 transition-opacity duration-700 ease-in-out slide opacity-0 z-0">
      <img src="telechargement/Caroucelle/maison.jpg" class="w-full h-full object-cover" alt="Image 3">
    </div>
  </div>

  <!-- Indicators -->
  <div class="absolute inset-x-0 bottom-4 flex justify-center gap-2 z-20">
    <button class="indicator w-3 h-3 rounded-full bg-white/70" data-slide="0" aria-label="Slide 1"></button>
    <button class="indicator w-3 h-3 rounded-full bg-white/40" data-slide="1" aria-label="Slide 2"></button>
    <button class="indicator w-3 h-3 rounded-full bg-white/40" data-slide="2" aria-label="Slide 3"></button>
  </div>

  <!-- Prev / Next -->
  <button id="carousel-prev" class="absolute left-4 top-1/2 -translate-y-1/2 z-20 p-2 bg-black/40 rounded-full hover:bg-black/50">
    <!-- petite flèche SVG -->
    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
    </svg>
  </button>
  <button id="carousel-next" class="absolute right-4 top-1/2 -translate-y-1/2 z-20 p-2 bg-black/40 rounded-full hover:bg-black/50">
    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
    </svg>
  </button>
</section>


<!-- ✅ Produits récents -->
<section class="container mx-auto py-10 px-4">
  <h2 class="text-center text-3xl font-bold mb-8">Produits récents</h2>
  <div class="grid sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
    <?php foreach($produits_recents as $p): ?>
      <div class="bg-white shadow-md rounded-2xl overflow-hidden hover:shadow-lg transition">
        <img src="telechargement/<?= htmlspecialchars($p['photo']) ?>" alt="<?= htmlspecialchars($p['nom_produit']) ?>"
             onerror="this.onerror=null;this.src='telechargement/default.png';"
             class="w-full h-56 object-cover">
        <div class="p-4">
          <h3 class="font-semibold text-lg"><?= htmlspecialchars($p['nom_produit']) ?></h3>
          <p class="text-sm text-gray-600">Code: <?= htmlspecialchars($p['code_produit']) ?> | Couleur: <?= htmlspecialchars($p['couleur']) ?></p>
          <p class="mt-2 font-bold text-orange-600"><?= htmlspecialchars($p['prix']) ?> $</p>
          <div class="flex justify-between items-center mt-4">
            <a href="panier.php?action=add&id=<?= $p['id_produit'] ?>" class="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded-lg text-sm">
              <i data-feather="shopping-cart"></i> Ajouter
            </a>
            <a href="https://wa.me/243992261070?text=Je veux commander : <?= urlencode($p['nom_produit']." - ".$p['prix']."$") ?>%0A<?= urlencode('Lien photo : http://'.$_SERVER['HTTP_HOST'].'/telechargement/'.$p['photo']) ?>"
               target="_blank"
               class="flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white px-3 py-2 rounded-lg text-sm">
              <i data-feather="message-circle"></i> WhatsApp
            </a>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</section>

<!-- ✅ Catégories -->
<section class="container mx-auto py-10 px-4">
  <h2 class="text-center text-3xl font-bold mb-8">Nos catégories</h2>
  <?php foreach($categories as $c): 
    $stmt = $pdo->prepare("SELECT * FROM produit WHERE id_categorie=? LIMIT 2");
    $stmt->execute([$c['id_categorie']]);
    $prods = $stmt->fetchAll();
    if(count($prods) > 0):
  ?>
  <h3 class="text-xl font-semibold mt-6 mb-3"><?= htmlspecialchars($c['nom_categorie']) ?></h3>
  <div class="grid md:grid-cols-2 gap-6">
    <?php foreach($prods as $p): ?>
      <div class="bg-white rounded-xl shadow-md hover:shadow-lg transition">
        <img src="telechargement/<?= htmlspecialchars($p['photo']) ?>" 
             alt="<?= htmlspecialchars($p['nom_produit']) ?>" 
             onerror="this.onerror=null;this.src='telechargement/default.png';"
             class="w-full h-48 object-cover">
        <div class="p-4">
          <h4 class="font-bold"><?= htmlspecialchars($p['nom_produit']) ?></h4>
          <p class="font-semibold text-orange-600"><?= htmlspecialchars($p['prix']) ?> $</p>
          <div class="flex justify-between items-center mt-3">
            <a href="panier.php?action=add&id=<?= $p['id_produit'] ?>" class="bg-blue-600 hover:bg-blue-700 text-white p-2 rounded-lg">
              <i data-feather="shopping-cart"></i>
            </a>
            <a href="https://wa.me/243992261070?text=Produit : <?= urlencode($p['nom_produit']." - ".$p['prix']."$") ?>%0A<?= urlencode('Lien photo : http://'.$_SERVER['HTTP_HOST'].'/telechargement/'.$p['photo']) ?>"
               target="_blank"
               class="bg-green-600 hover:bg-green-700 text-white p-2 rounded-lg">
              <i data-feather="message-circle"></i>
            </a>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
  <?php endif; endforeach; ?>
</section>

<!-- ✅ Section info -->
<section class="bg-gray-900 text-[ivory] py-10 text-center">
  <h2 class="text-2xl font-bold mb-3">Bienvenue chez Gallaxy Paint</h2>
  <p>Des couleurs qui transforment l’ordinaire en extraordinaire.</p>
  <p class="mt-3"> Butembo Grand Route, Bâtiment KABOSYO chez RIGO NZANZU</p>
  <p> +243 992 261 070 | +243 858 769 963 | +243 816 419 583</p>
</section>

<!-- ✅ Footer -->
<footer class="bg-black text-white py-5 text-center">
  <div class="flex justify-center gap-6 text-xl mb-2">
    <a href="https://wa.me/243992261070" class="hover:text-green-400"><i data-feather="message-circle"></i></a>
    <a href="https://www.instagram.com/reddynzoloko3" class="hover:text-pink-400"><i data-feather="instagram"></i></a>
    <a href="http://www.facebook.com/share/1AKPxS8gTP/?mibextid=wwXlfr" class="hover:text-blue-500"><i data-feather="facebook"></i></a>
    <a href="https://github.com/Reddy-Nzoloko" class="hover:text-gray-300"><i data-feather="github"></i></a>
  </div>
  <p class="text-sm">© RedDev 2025 Gallaxy Paint</p>
</footer>

<script>
  // ✅ Navbar toggle
  document.getElementById('menu-toggle').addEventListener('click', () => {
    document.getElementById('mobile-menu').classList.toggle('hidden');
  });

  // ✅ Activer les icônes Feather
  feather.replace();
</script>
<script>
document.addEventListener('DOMContentLoaded', function () {
  const slides = document.querySelectorAll('.slide');
  const indicators = document.querySelectorAll('.indicator');
  const nextBtn = document.getElementById('carousel-next');
  const prevBtn = document.getElementById('carousel-prev');
  const carouselSection = document.querySelector('section[aria-label="Carrousel"]');

  if (!slides.length) return;

  let current = 0;
  let interval = null;
  const delay = 4000; // ms

  function show(n) {
    slides.forEach((s, i) => {
      if (i === n) {
        s.classList.remove('opacity-0');
        s.classList.add('opacity-100');
        s.style.zIndex = 20;
      } else {
        s.classList.remove('opacity-100');
        s.classList.add('opacity-0');
        s.style.zIndex = 10;
      }
    });
    indicators.forEach((ind, i) => {
      if (i === n) {
        ind.classList.remove('bg-white/40'); ind.classList.add('bg-white');
      } else {
        ind.classList.remove('bg-white'); ind.classList.add('bg-white/40');
      }
    });
    current = n;
  }

  function next() { show((current + 1) % slides.length); }
  function prev() { show((current - 1 + slides.length) % slides.length); }

  function start() {
    stop();
    interval = setInterval(next, delay);
  }
  function stop() {
    if (interval) { clearInterval(interval); interval = null; }
  }

  // events
  nextBtn.addEventListener('click', () => { next(); start(); });
  prevBtn.addEventListener('click', () => { prev(); start(); });
  indicators.forEach(ind => {
    ind.addEventListener('click', (e) => {
      const idx = parseInt(ind.dataset.slide, 10);
      if (!isNaN(idx)) { show(idx); start(); }
    });
  });

  // pause on hover
  carouselSection.addEventListener('mouseenter', stop);
  carouselSection.addEventListener('mouseleave', start);

  // initial
  show(0);
  start();
});
</script>


</body>
</html>
