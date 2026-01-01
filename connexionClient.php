<?php
session_start();
require 'connexion.php';

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($action === 'login') {
        $stmt = $pdo->prepare("SELECT * FROM client WHERE email = ?");
        $stmt->execute([$email]);
        $client = $stmt->fetch();

        if ($client && password_verify($password, $client['password'])) {
            $_SESSION['client_id'] = $client['id_client'];
            $_SESSION['client_nom'] = $client['nom'];
            header("Location: checkout.php");
            exit;
        } else {
            $error = "Identifiants incorrects.";
        }
    } elseif ($action === 'register') {
        $nom = trim($_POST['nom'] ?? '');
        $tel = trim($_POST['telephone'] ?? '');
        $adr = trim($_POST['adresse'] ?? '');
        
        // Vérifier si l'email existe déjà
        $check = $pdo->prepare("SELECT id_client FROM client WHERE email = ?");
        $check->execute([$email]);
        
        if ($check->fetch()) {
            $error = "Cet email est déjà utilisé.";
        } else {
            $hashed_pass = password_hash($password, PASSWORD_DEFAULT);
            $ins = $pdo->prepare("INSERT INTO client (nom, telephone, adresse, email, password) VALUES (?, ?, ?, ?, ?)");
            if ($ins->execute([$nom, $tel, $adr, $email, $hashed_pass])) {
                $_SESSION['client_id'] = $pdo->lastInsertId();
                $_SESSION['client_nom'] = $nom;
                header("Location: checkout.php");
                exit;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion - Gallaxy</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="icon" href="telechargement/Caroucelle/icon.ico" type="image/x-icon">

</head>
<body class="bg-gray-900 text-gray-100 min-vh-100 flex items-center justify-center p-6">
    <div class="max-w-4xl w-full grid grid-cols-1 md:grid-cols-2 gap-8 bg-gray-800 p-8 rounded-2xl shadow-2xl">
        
        <!-- Section Connexion -->
        <div>
            <h2 class="text-2xl font-bold mb-6 text-blue-400">Déjà client ?</h2>
            <?php if($error && $_POST['action']=='login'): ?>
                <p class="text-red-400 mb-4"><?= $error ?></p>
            <?php endif; ?>
            <form method="POST" class="space-y-4">
                <input type="hidden" name="action" value="login">
                <input type="email" name="email" placeholder="Email" class="w-full bg-gray-700 border-none rounded-lg p-3 focus:ring-2 focus:ring-blue-500" required>
                <input type="password" name="password" placeholder="Mot de passe" class="w-full bg-gray-700 border-none rounded-lg p-3 focus:ring-2 focus:ring-blue-500" required>
                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-lg transition">Se connecter</button>
            </form>
        </div>

        <!-- Section Inscription -->
        <div class="border-t md:border-t-0 md:border-l border-gray-700 pt-8 md:pt-0 md:pl-8">
            <h2 class="text-2xl font-bold mb-6 text-green-400">Nouveau ici ?</h2>
            <?php if($error && $_POST['action']=='register'): ?>
                <p class="text-red-400 mb-4"><?= $error ?></p>
            <?php endif; ?>
            <form method="POST" class="space-y-4">
                <input type="hidden" name="action" value="register">
                <input type="text" name="nom" placeholder="Nom complet" class="w-full bg-gray-700 border-none rounded-lg p-3 focus:ring-2 focus:ring-green-500" required>
                <input type="email" name="email" placeholder="Email" class="w-full bg-gray-700 border-none rounded-lg p-3 focus:ring-2 focus:ring-green-500" required>
                <input type="text" name="telephone" placeholder="Téléphone" class="w-full bg-gray-700 border-none rounded-lg p-3 focus:ring-2 focus:ring-green-500" required>
                <textarea name="adresse" placeholder="Adresse de livraison" class="w-full bg-gray-700 border-none rounded-lg p-3 focus:ring-2 focus:ring-green-500" required></textarea>
                <input type="password" name="password" placeholder="Créer un mot de passe" class="w-full bg-gray-700 border-none rounded-lg p-3 focus:ring-2 focus:ring-green-500" required>
                <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3 rounded-lg transition">Créer mon compte</button>
            </form>
        </div>
    </div>
</body>
</html>