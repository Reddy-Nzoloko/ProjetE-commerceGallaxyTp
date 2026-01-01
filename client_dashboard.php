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
<link rel="icon" href="telechargement/Caroucelle/icon.ico" type="image/x-icon">
<script src="https://cdn.tailwindcss.com"></script>

<style>
    /* STYLES POUR L'IMPRESSION */
    @media print {
        /* On cache tout ce qui ne doit pas être imprimé */
        header, .no-print, .btn-print, a {
            display: none !important;
        }
        
        /* On adapte les couleurs pour économiser l'encre et être lisible */
        body {
            background-color: white !important;
            color: black !important;
        }
        
        .bg-card-bg, .bg-dark-bg {
            background-color: white !important;
            border: 1px solid #ccc !important;
            color: black !important;
            box-shadow: none !important;
        }

        .text-primary-teal, .text-gray-400, .text-yellow-400, .text-green-400 {
            color: black !important;
        }

        .print-only {
            display: block !important;
        }

        /* Forcer chaque commande à ne pas être coupée entre deux pages si possible */
        .order-card {
            page-break-inside: avoid;
            margin-bottom: 20px;
        }
    }
</style>

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

// Fonction pour imprimer une commande spécifique
function imprimerCommande(id) {
    window.print();
}
</script>
</head>

<body class="bg-dark-bg text-white min-h-screen">

<header class="bg-card-bg p-6 flex justify-between items-center border-b border-primary-teal/30">
    <div>
        <h1 class="text-2xl font-bold text-primary-teal">Gallaxy Paint</h1>
        <p class="text-gray-400">Bienvenue, <?= htmlspecialchars($client['nom']) ?></p>
    </div>
    <a href="logout.php" class="text-red-400 hover:underline">Déconnexion</a>
</header>

<main class="p-6 grid grid-cols-1 lg:grid-cols-3 gap-6">

    <div class="bg-card-bg rounded-xl p-6 shadow border border-primary-teal/20 h-fit">
        <h2 class="text-xl font-semibold mb-4 text-primary-teal">Mes informations</h2>
        <p><span class="text-gray-400">Nom :</span> <?= htmlspecialchars($client['nom']) ?></p>
        <p><span class="text-gray-400">Email :</span> <?= htmlspecialchars($client['email']) ?></p>
        <p><span class="text-gray-400">Téléphone :</span> <?= htmlspecialchars($client['telephone']) ?></p>
        <p><span class="text-gray-400">Adresse :</span> <?= htmlspecialchars($client['adresse']) ?></p>
        
        <button onclick="window.print()" class="mt-6 w-full bg-primary-teal/20 border border-primary-teal text-primary-teal py-2 rounded-lg hover:bg-primary-teal hover:text-white transition no-print">
            🖨️ Imprimer l'historique
        </button>
    </div>

    <div class="lg:col-span-2 bg-card-bg rounded-xl p-6 shadow border border-primary-teal/20">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-semibold text-primary-teal">Mes commandes</h2>
            <span class="hidden print-only text-black text-sm">Extrait le : <?= date('d/m/Y H:i') ?></span>
        </div>

        <?php if (count($commandes) === 0): ?>
            <p class="text-gray-400">Aucune commande trouvée.</p>
        <?php else: ?>
            <div class="space-y-4">
                <?php foreach ($commandes as $cmd): ?>
                    <div class="order-card p-4 rounded-lg bg-dark-bg border border-gray-700">
                        <div class="flex justify-between items-start">
                            <div>
                                <span class="font-bold text-lg">Commande #<?= $cmd['id_commande'] ?></span>
                                <p class="text-sm text-gray-400">
                                    Date : <?= date('d/m/Y', strtotime($cmd['date_commande'])) ?>
                                </p>
                            </div>
                            <button onclick="window.print()" class="no-print bg-gray-700 hover:bg-gray-600 text-white text-xs py-1 px-3 rounded">
                                Imprimer reçu
                            </button>
                        </div>

                        <div class="mt-3 grid grid-cols-2 gap-2 text-sm">
                            <p>Montant : <span class="text-primary-teal font-semibold"><?= number_format($cmd['montant_total'], 2) ?> $</span></p>
                            <p>Statut : <span class="text-yellow-400"><?= htmlspecialchars($cmd['statut_commande']) ?></span></p>
                            <p>Paiement : 
                                <?php if ($cmd['statut_paiement'] === 'Payé'): ?>
                                    <span class="text-green-400 font-bold">Payé</span>
                                <?php else: ?>
                                    <span class="text-red-400">En attente</span>
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