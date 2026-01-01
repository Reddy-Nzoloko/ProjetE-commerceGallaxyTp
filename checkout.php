<?php
session_start();
require 'connexion.php';

// Rediriger vers login si non connecté
if (!isset($_SESSION['client_id'])) {
    header("Location: login.php");
    exit;
}

$panier = $_SESSION['panier'] ?? [];
if (empty($panier)) {
    header("Location: panier.php");
    exit;
}

// Récupérer les infos du client connecté
$stmt = $pdo->prepare("SELECT * FROM client WHERE id_client = ?");
$stmt->execute([$_SESSION['client_id']]);
$client = $stmt->fetch();

$total = 0;
foreach($panier as $item) {
    $total += $item['prix'] * $item['quantite'];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Finaliser la commande - Gallaxy</title>
    <script src="https://cdn.tailwindcss.com"></script>
      <link rel="icon" href="telechargement/Caroucelle/icon.ico" type="image/x-icon">

</head>
<body class="bg-gray-900 text-gray-100 p-6">
    <div class="max-w-5xl mx-auto grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Infos Client -->
        <div class="lg:col-span-2 bg-gray-800 p-8 rounded-2xl shadow-xl">
            <h1 class="text-3xl font-bold mb-6 text-blue-400">Confirmation de livraison</h1>
            <div class="space-y-4 mb-8">
                <p><span class="text-gray-400">Destinataire:</span> <br><strong class="text-xl"><?= htmlspecialchars($client['nom']) ?></strong></p>
                <p><span class="text-gray-400">Téléphone:</span> <br><strong><?= htmlspecialchars($client['telephone']) ?></strong></p>
                <p><span class="text-gray-400">Adresse de livraison:</span> <br><strong><?= nl2br(htmlspecialchars($client['adresse'])) ?></strong></p>
            </div>

            <form action="valider_commande.php" method="POST">
                <input type="hidden" name="id_client" value="<?= $client['id_client'] ?>">
                <input type="hidden" name="montant_total" value="<?= $total ?>">
                
                <h3 class="text-lg font-semibold mb-3">Mode de paiement</h3>
                <div class="p-4 bg-gray-700 rounded-lg mb-6 border border-blue-500">
                    <label class="flex items-center space-x-3 cursor-pointer">
                        <input type="radio" name="mode_paiement" value="Paiement à la livraison" checked class="form-radio h-5 w-5 text-blue-500">
                        <span>Paiement à la livraison (Cash on Delivery)</span>
                    </label>
                </div>

                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-4 rounded-xl shadow-lg transition transform hover:scale-[1.02]">
                    Confirmer et Commander (<?= number_format($total, 2) ?> $)
                </button>
            </form>
        </div>

        <!-- Résumé Panier -->
        <div class="bg-gray-800 p-6 rounded-2xl shadow-xl h-fit">
            <h2 class="text-xl font-bold mb-4 border-b border-gray-700 pb-2">Votre Panier</h2>
            <div class="space-y-4 max-h-96 overflow-y-auto pr-2">
                <?php foreach($panier as $id => $p): ?>
                <div class="flex justify-between items-center bg-gray-700 p-3 rounded-lg">
                    <div>
                        <p class="font-medium"><?= htmlspecialchars($p['nom']) ?></p>
                        <p class="text-sm text-gray-400">Quantité: <?= $p['quantite'] ?></p>
                    </div>
                    <p class="font-bold text-blue-400"><?= number_format($p['prix'] * $p['quantite'], 2) ?> $</p>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="mt-6 pt-4 border-t border-gray-700">
                <div class="flex justify-between text-xl font-bold">
                    <span>Total</span>
                    <span class="text-green-400"><?= number_format($total, 2) ?> $</span>
                </div>
            </div>
        </div>
    </div>
</body>
</html>