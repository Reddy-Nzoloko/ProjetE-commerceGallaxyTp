<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'client') {
    header("Location: login.php");
    exit;
}

include "connexion.php";

$id_client = $_SESSION['client_id'];

/* Infos client */
$reqClient = $pdo->prepare("SELECT * FROM client WHERE id_client = ?");
$reqClient->execute([$id_client]);
$client = $reqClient->fetch();

/* Commandes + paiements */
$reqCmd = $pdo->prepare("
    SELECT 
        c.id_commande,
        c.date_commande,
        c.statut AS statut_commande,
        c.montant_total,
        p.statut AS statut_paiement,
        p.date_paiement
    FROM commande c
    LEFT JOIN paiement p ON c.id_commande = p.id_commande
    WHERE c.id_client = ?
    ORDER BY c.date_commande DESC
");
$reqCmd->execute([$id_client]);
$commandes = $reqCmd->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Espace Client | Gallaxy Paint</title>
<script src="https://cdn.tailwindcss.com"></script>

<script>
tailwind.config = {
    theme: {
        extend: {
            colors: {
                'primary-teal': '#00c896',
                'dark-bg': '#1a1a1a',
                'card-bg': '#2c2c2c',
            }
        }
    }
}
</script>
</head>

<body class="bg-dark-bg text-white min-h-screen">

<!-- HEADER -->
<header class="bg-card-bg p-6 flex justify-between items-center border-b border-primary-teal/30">
    <div>
        <h1 class="text-2xl font-bold text-primary-teal">Gallaxy Paint</h1>
        <p class="text-gray-400">Bienvenue, <?= htmlspecialchars($client['nom']) ?></p>
    </div>
    <a href="logout.php" class="text-red-400 hover:underline">Déconnexion</a>
</header>

<!-- CONTENU -->
<main class="p-6 grid grid-cols-1 lg:grid-cols-3 gap-6">

    <!-- INFOS CLIENT -->
    <div class="bg-card-bg rounded-xl p-6 shadow border border-primary-teal/20">
        <h2 class="text-xl font-semibold mb-4 text-primary-teal">Mes informations</h2>
        <p><span class="text-gray-400">Nom :</span> <?= $client['nom'] ?></p>
        <p><span class="text-gray-400">Email :</span> <?= $client['email'] ?></p>
        <p><span class="text-gray-400">Téléphone :</span> <?= $client['telephone'] ?></p>
        <p><span class="text-gray-400">Adresse :</span> <?= $client['adresse'] ?></p>
    </div>

    <!-- HISTORIQUE COMMANDES -->
    <div class="lg:col-span-2 bg-card-bg rounded-xl p-6 shadow border border-primary-teal/20">
        <h2 class="text-xl font-semibold mb-4 text-primary-teal">Mes commandes</h2>

        <?php if (count($commandes) === 0): ?>
            <p class="text-gray-400">Aucune commande trouvée.</p>
        <?php else: ?>
            <div class="space-y-4">
                <?php foreach ($commandes as $cmd): ?>
                    <div class="p-4 rounded-lg bg-dark-bg border border-gray-700">
                        <div class="flex justify-between">
                            <span class="font-semibold">Commande #<?= $cmd['id_commande'] ?></span>
                            <span class="text-sm text-gray-400">
                                <?= date('d/m/Y', strtotime($cmd['date_commande'])) ?>
                            </span>
                        </div>

                        <div class="mt-2 text-sm">
                            <p>Montant : <span class="text-primary-teal font-semibold"><?= $cmd['montant_total'] ?> $</span></p>
                            <p>Statut commande : <span class="text-yellow-400"><?= $cmd['statut_commande'] ?></span></p>
                            <p>Paiement : 
                                <?php if ($cmd['statut_paiement'] === 'Payé'): ?>
                                    <span class="text-green-400">Payé</span>
                                <?php else: ?>
                                    <span class="text-red-400">Non payé</span>
                                <?php endif; ?>
                            </p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

</main>

</body>
</html>
