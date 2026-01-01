<?php
// FILE: admin_commandes.php
require 'connexion.php';

// 1. GESTION DE LA RECHERCHE
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// 2. MISE À JOUR STATUT (Action POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_commande'], $_POST['action'])) {
    $id = intval($_POST['id_commande']);
    if ($_POST['action'] === 'livre') {
        $stmt = $pdo->prepare("UPDATE commande SET statut = 'Livré' WHERE id_commande = ?");
        $stmt->execute([$id]);
    }
    if ($_POST['action'] === 'paye') {
        $stmt = $pdo->prepare("UPDATE paiement SET statut = 'Payé', date_paiement = NOW() WHERE id_commande = ?");
        $stmt->execute([$id]);
    }
}

// 3. RÉCUPÉRATION DES COMMANDES (avec ou sans filtre)
$sql = "SELECT c.id_commande, c.date_commande, c.montant_total, c.statut,
               cl.nom, cl.telephone, cl.adresse,
               p.statut AS paiement_statut
        FROM commande c
        LEFT JOIN client cl ON cl.id_client = c.id_client
        LEFT JOIN paiement p ON p.id_commande = c.id_commande";

if ($search !== '') {
    $sql .= " WHERE cl.nom LIKE :search";
}
$sql .= " ORDER BY c.date_commande DESC";

$stmt = $pdo->prepare($sql);
if ($search !== '') {
    $stmt->execute(['search' => "%$search%"]);
} else {
    $stmt->execute();
}
$commandes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link rel="icon" href="telechargement/Caroucelle/icon.ico" type="image/x-icon">
  <title>Gestion des commandes | Admin</title>
  <script src="https://cdn.tailwindcss.com"></script>
  
  <style>
    @media print {
        /* Cacher tout ce qui ne doit pas être imprimé */
        .no-print, form, .search-box, .nav-links {
            display: none !important;
        }
        body {
            background-color: white !important;
            color: black !important;
            padding: 0;
        }
        .container-main {
            width: 100% !important;
            max-width: 100% !important;
            margin: 0 !important;
        }
        table {
            color: black !important;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #ddd !important;
        }
        .print-title {
            display: block !important;
            color: black;
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 20px;
        }
    }
    .print-title { display: none; }
  </style>
</head>
<body class="bg-gray-900 text-gray-100 p-4 md:p-6">

  <div class="max-w-7xl mx-auto container-main">
    
    <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4 no-print">
        <h1 class="text-2xl font-semibold text-teal-400">📦 Gestion des commandes</h1>
        
        <form method="GET" class="flex w-full md:w-auto gap-2">
            <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" 
                   placeholder="Rechercher un client..." 
                   class="bg-gray-800 border border-gray-700 rounded px-4 py-2 focus:outline-none focus:border-teal-500 w-full">
            <button type="submit" class="bg-teal-600 px-4 py-2 rounded hover:bg-teal-500">Filtrer</button>
            <?php if($search): ?>
                <a href="admin_commandes.php" class="bg-gray-700 px-4 py-2 rounded hover:bg-gray-600">X</a>
            <?php endif; ?>
        </form>

        <button onclick="window.print()" class="bg-gray-100 text-gray-900 font-bold px-4 py-2 rounded hover:bg-white flex items-center gap-2">
            🖨️ Imprimer la liste
        </button>
    </div>

    <div class="print-title">
        Rapport des commandes - Gallaxy Paint <br>
        <small>Date : <?= date('d/m/Y H:i') ?> | Filtre : <?= $search ?: 'Toutes' ?></small>
    </div>

    <div class="overflow-x-auto bg-gray-800 rounded-lg shadow border border-gray-700">
      <table class="min-w-full text-sm">
        <thead class="bg-gray-700 text-gray-200 uppercase text-xs">
          <tr>
            <th class="p-3 text-left">ID</th>
            <th class="p-3 text-left">Client</th>
            <th class="p-3 text-left">Détails</th>
            <th class="p-3 text-left">Montant</th>
            <th class="p-3 text-left">Statuts</th>
            <th class="p-3 text-left no-print">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($commandes)): ?>
            <tr><td colspan="6" class="p-6 text-center text-gray-400">Aucune commande trouvée.</td></tr>
          <?php endif; ?>

          <?php foreach ($commandes as $c): ?>
            <tr class="border-t border-gray-700 hover:bg-gray-700/50">
              <td class="p-3 font-mono text-teal-400">#<?= $c['id_commande'] ?></td>
              <td class="p-3">
                <div class="font-bold text-gray-100"><?= htmlspecialchars($c['nom']) ?></div>
                <div class="text-xs text-gray-400"><?= htmlspecialchars($c['telephone']) ?></div>
              </td>
              <td class="p-3 text-xs">
                Le : <?= date('d/m/Y', strtotime($c['date_commande'])) ?>
              </td>
              <td class="p-3 font-semibold text-white">
                <?= number_format($c['montant_total'], 2, ',', ' ') ?> $
              </td>
              <td class="p-3 space-y-1">
                <span class="block w-fit px-2 py-0.5 rounded text-[10px] font-bold uppercase <?= $c['statut'] === 'Livré' ? 'bg-green-900 text-green-200' : 'bg-yellow-900 text-yellow-200' ?>">
                   📦 <?= $c['statut'] ?>
                </span>
                <span class="block w-fit px-2 py-0.5 rounded text-[10px] font-bold uppercase <?= $c['paiement_statut'] === 'Payé' ? 'bg-blue-900 text-blue-200' : 'bg-red-900 text-red-200' ?>">
                   💳 <?= $c['paiement_statut'] ?? 'Non payé' ?>
                </span>
              </td>
              <td class="p-3 no-print">
                <div class="flex flex-col gap-1">
                    <form method="post" class="flex gap-1">
                        <input type="hidden" name="id_commande" value="<?= $c['id_commande'] ?>">
                        <?php if ($c['statut'] !== 'Livré'): ?>
                            <button name="action" value="livre" class="bg-blue-600 hover:bg-blue-500 p-1 rounded text-[10px]" title="Marquer livré">Livrer</button>
                        <?php endif; ?>
                        <?php if ($c['paiement_statut'] !== 'Payé'): ?>
                            <button name="action" value="paye" class="bg-green-600 hover:bg-green-500 p-1 rounded text-[10px]" title="Marquer payé">Payer</button>
                        <?php endif; ?>
                    </form>
                    <button onclick="window.print()" class="border border-gray-500 text-gray-300 hover:bg-gray-600 p-1 rounded text-[10px]">
                        Imprimer Reçu
                    </button>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <div class="mt-6 no-print flex justify-between items-center">
      <a href="dashbord.php" class="text-teal-400 hover:text-teal-300">← Retour au dashboard</a>
      <p class="text-gray-500 text-xs">Gallaxy Paint System v2.0</p>
    </div>
  </div>

</body>
</html>