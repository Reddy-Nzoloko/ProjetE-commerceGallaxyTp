<?php
session_start();
require 'connexion.php';

// Rediriger vers login si le client n'est pas connecté
if (!isset($_SESSION['client_id'])) {
    header("Location: login_client.php");
    exit;
}

$panier = $_SESSION['panier'] ?? [];
if (empty($panier)) {
    header("Location: panier.php");
    exit;
}

// Récupérer les informations fraîches du client connecté
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Finaliser la commande - Gallaxy Paint</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="icon" href="telechargement/Caroucelle/icon.ico" type="image/x-icon">
</head>
<body class="bg-gray-950 text-gray-100 min-h-screen font-sans p-4 md:p-8">

    <div class="max-w-6xl mx-auto">
        <!-- En-tête -->
        <div class="flex flex-col md:flex-row justify-between items-center mb-10 gap-4">
            <div>
                <h1 class="text-3xl font-black tracking-tighter">FINALISER LA <span class="text-teal-500">COMMANDE</span></h1>
                <p class="text-gray-500">Vérifiez vos informations avant de confirmer.</p>
            </div>
            <div class="bg-gray-900 border border-gray-800 px-6 py-3 rounded-2xl flex items-center gap-3">
                <div class="w-10 h-10 bg-teal-500/20 rounded-full flex items-center justify-center text-teal-500 font-bold">
                    <?= strtoupper(substr($client['nom'], 0, 1)) ?>
                </div>
                <div>
                    <p class="text-xs text-gray-500 leading-none">Client connecté</p>
                    <p class="font-bold"><?= htmlspecialchars($client['nom'] ?? 'Utilisateur') ?></p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Formulaire de Livraison -->
            <div class="lg:col-span-2">
                <form action="valider_commande.php" method="POST" class="space-y-6">
                    <div class="bg-gray-900 border border-gray-800 rounded-3xl p-6 md:p-8 shadow-xl">
                        <h2 class="text-xl font-bold mb-6 flex items-center gap-3">
                            <span class="w-8 h-8 bg-teal-600 rounded-lg flex items-center justify-center text-sm">01</span>
                            Adresse de Livraison
                        </h2>
                        
                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-2 ml-1">Adresse complète</label>
                                <textarea name="adresse" required rows="3"
                                    class="w-full bg-gray-800 border border-transparent focus:border-teal-500 focus:ring-4 focus:ring-teal-500/10 text-white p-4 rounded-2xl transition-all outline-none"
                                    placeholder="Rue, Quartier, Ville, Points de repère..."><?= htmlspecialchars($client['adresse'] ?? '') ?></textarea>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-2 ml-1">Téléphone de contact</label>
                                    <input type="text" value="<?= htmlspecialchars($client['telephone'] ?? '') ?>" disabled
                                        class="w-full bg-gray-800/50 border border-gray-700 text-gray-500 p-4 rounded-2xl cursor-not-allowed">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-2 ml-1">Email</label>
                                    <input type="text" value="<?= htmlspecialchars($client['email'] ?? '') ?>" disabled
                                        class="w-full bg-gray-800/50 border border-gray-700 text-gray-500 p-4 rounded-2xl cursor-not-allowed">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-900 border border-gray-800 rounded-3xl p-6 md:p-8 shadow-xl">
                        <h2 class="text-xl font-bold mb-6 flex items-center gap-3">
                            <span class="w-8 h-8 bg-teal-600 rounded-lg flex items-center justify-center text-sm">02</span>
                            Méthode de Paiement
                        </h2>
                        <div class="bg-teal-500/5 border border-teal-500/20 p-5 rounded-2xl flex items-center gap-4">
                            <input type="radio" name="mode_paiement" value="Paiement à la livraison" checked 
                                class="w-6 h-6 accent-teal-500">
                            <div>
                                <p class="font-bold text-teal-500">Paiement à la livraison</p>
                                <p class="text-sm text-gray-500">Payez en espèces lors de la réception de votre colis.</p>
                            </div>
                        </div>
                    </div>

                    <input type="hidden" name="montant_total" value="<?= $total ?>">
                    
                    <button type="submit" 
                        class="w-full bg-teal-600 hover:bg-teal-500 text-white font-black py-5 rounded-2xl shadow-2xl shadow-teal-900/40 transition-all transform hover:scale-[1.01] active:scale-95 text-lg">
                        CONFIRMER LA COMMANDE • <?= number_format($total, 2) ?> $
                    </button>
                </form>
            </div>

            <!-- Résumé Panier -->
            <div class="lg:col-span-1">
                <div class="bg-gray-900 border border-gray-800 rounded-3xl p-6 shadow-xl sticky top-8">
                    <h2 class="text-lg font-bold mb-6 border-b border-gray-800 pb-4">Résumé des articles</h2>
                    <div class="space-y-4 max-h-96 overflow-y-auto pr-2 custom-scrollbar">
                        <?php foreach($panier as $id => $p): ?>
                        <div class="flex gap-4 items-center bg-gray-800/50 p-3 rounded-2xl border border-gray-800">
                            <div class="w-16 h-16 bg-gray-700 rounded-xl flex-shrink-0 overflow-hidden">
                                <img src="<?= htmlspecialchars($p['photo'] ?? 'telechargement/default.png') ?>" alt="Produit" class="w-full h-full object-cover">
                            </div>
                            <div class="flex-grow min-w-0">
                                <p class="font-bold text-sm truncate"><?= htmlspecialchars($p['nom']) ?></p>
                                <p class="text-xs text-gray-500">Quantité: <?= $p['quantite'] ?></p>
                            </div>
                            <p class="font-bold text-teal-500 text-sm whitespace-nowrap"><?= number_format($p['prix'] * $p['quantite'], 2) ?>$</p>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="mt-8 space-y-3 pt-6 border-t border-gray-800">
                        <div class="flex justify-between text-gray-500 text-sm font-medium">
                            <span>Sous-total</span>
                            <span><?= number_format($total, 2) ?> $</span>
                        </div>
                        <div class="flex justify-between text-gray-500 text-sm font-medium">
                            <span>Frais de livraison</span>
                            <span class="text-green-500 font-bold uppercase text-xs">Gratuit</span>
                        </div>
                        <div class="flex justify-between items-center pt-4">
                            <span class="text-xl font-bold">Total TTC</span>
                            <span class="text-3xl font-black text-teal-500"><?= number_format($total, 2) ?> $</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>
</html>