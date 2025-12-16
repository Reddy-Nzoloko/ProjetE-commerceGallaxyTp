<?php
// FILE: admin_commandes.php
// Page admin simple pour gérer les commandes (sans authentification)

require 'connexion.php';

// Mise à jour du statut commande ou paiement
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['id_commande'], $_POST['action'])) {
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
}

// Récupérer toutes les commandes
$sql = "SELECT c.id_commande, c.date_commande, c.montant_total, c.statut,
               cl.nom, cl.telephone,
               p.statut AS paiement_statut
        FROM commande c
        LEFT JOIN client cl ON cl.id_client = c.id_client
        LEFT JOIN paiement p ON p.id_commande = c.id_commande
        ORDER BY c.date_commande DESC";

$commandes = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
?>

<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Gestion des commandes</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-900 text-gray-100 p-6">

  <div class="max-w-7xl mx-auto">
    <h1 class="text-2xl font-semibold mb-6">📦 Gestion des commandes</h1>

    <div class="overflow-x-auto bg-gray-800 rounded-lg shadow">
      <table class="min-w-full text-sm">
        <thead class="bg-gray-700 text-gray-200">
          <tr>
            <th class="p-3 text-left">ID</th>
            <th class="p-3 text-left">Client</th>
            <th class="p-3 text-left">Téléphone</th>
            <th class="p-3 text-left">Date</th>
            <th class="p-3 text-left">Montant</th>
            <th class="p-3 text-left">Commande</th>
            <th class="p-3 text-left">Paiement</th>
            <th class="p-3 text-left">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($commandes as $c): ?>
            <tr class="border-t border-gray-700 hover:bg-gray-700/50">
              <td class="p-3">#<?= $c['id_commande'] ?></td>
              <td class="p-3"><?= htmlspecialchars($c['nom']) ?></td>
              <td class="p-3"><?= htmlspecialchars($c['telephone']) ?></td>
              <td class="p-3"><?= htmlspecialchars($c['date_commande']) ?></td>
              <td class="p-3 font-semibold"><?= number_format($c['montant_total'], 2) ?> FC</td>
              <td class="p-3">
                <span class="px-2 py-1 rounded text-xs
                <?= $c['statut'] === 'Livré' ? 'bg-green-600' : 'bg-yellow-600' ?>">
                  <?= $c['statut'] ?>
                </span>
              </td>
              <td class="p-3">
                <span class="px-2 py-1 rounded text-xs
                <?= $c['paiement_statut'] === 'Payé' ? 'bg-green-600' : 'bg-red-600' ?>">
                  <?= $c['paiement_statut'] ?? 'Non payé' ?>
                </span>
              </td>
              <td class="p-3">
                <form method="post" class="flex gap-2">
                  <input type="hidden" name="id_commande" value="<?= $c['id_commande'] ?>">
                  <?php if ($c['statut'] !== 'Livré'): ?>
                    <button name="action" value="livre" class="bg-blue-600 px-2 py-1 rounded text-xs">Marquer livré</button>
                  <?php endif; ?>
                  <?php if ($c['paiement_statut'] !== 'Payé'): ?>
                    <button name="action" value="paye" class="bg-green-600 px-2 py-1 rounded text-xs">Marquer payé</button>
                  <?php endif; ?>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <div class="mt-6">
      <a href="dashbord.php" class="text-blue-400">← Retour au dashboard</a>
    </div>
  </div>

</body>
</html>
