<?php
session_start();
include "connexion.php";

$error = "";

if (isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    try {
        // Recherche du client uniquement
        $stmt = $pdo->prepare("SELECT * FROM client WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $client = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($client) {
            // Vérification du mot de passe
            if (password_verify($password, $client['password'])) {
                $_SESSION['role'] = 'client';
                $_SESSION['client_id'] = $client['id_client'];
                $_SESSION['client_nom'] = $client['nom'];
                $_SESSION['client_email'] = $client['email'];

                // Redirection vers le checkout après connexion réussie
                header("Location: checkout.php");
                exit;
            } else {
                $error = "Mot de passe incorrect.";
            }
        } else {
            $error = "Aucun compte trouvé avec cet email.";
        }
    } catch (Exception $e) {
        $error = "Une erreur est survenue lors de la connexion.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion Client - Gallaxy Paint</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="icon" href="telechargement/Caroucelle/icon.ico" type="image/x-icon">
    <style>
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-login { animation: slideUp 0.5s ease-out; }
    </style>
</head>
<body class="bg-gray-950 flex items-center justify-center min-h-screen p-4 font-sans text-gray-200">

    <div class="w-full max-w-md bg-gray-900 border border-gray-800 rounded-3xl shadow-2xl p-8 animate-login">
        <!-- Header -->
        <div class="text-center mb-10">
            <div class="inline-block p-4 bg-teal-500/10 rounded-2xl mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-teal-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
            </div>
            <h1 class="text-3xl font-black text-white tracking-tight">Espace <span class="text-teal-500">Client</span></h1>
            <p class="text-gray-500 mt-2">Connectez-vous pour finaliser votre achat</p>
        </div>

        <?php if ($error): ?>
            <div class="bg-red-500/10 border border-red-500/50 text-red-500 p-4 rounded-2xl mb-6 text-sm flex items-center gap-3">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                </svg>
                <?= $error ?>
            </div>
        <?php endif; ?>

        <form action="" method="POST" class="space-y-6">
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-2 ml-1">Adresse Email</label>
                <input type="email" name="email" required
                       class="w-full bg-gray-800 border border-transparent focus:border-teal-500 focus:ring-4 focus:ring-teal-500/10 text-white p-4 rounded-2xl transition-all duration-300 outline-none placeholder-gray-600"
                       placeholder="exemple@mail.com">
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-2 ml-1">Mot de passe</label>
                <input type="password" name="password" required
                       class="w-full bg-gray-800 border border-transparent focus:border-teal-500 focus:ring-4 focus:ring-teal-500/10 text-white p-4 rounded-2xl transition-all duration-300 outline-none placeholder-gray-600"
                       placeholder="••••••••">
            </div>

            <button type="submit" name="login"
                    class="w-full bg-teal-600 hover:bg-teal-500 text-white font-bold py-4 rounded-2xl shadow-xl shadow-teal-900/20 transition-all duration-300 transform active:scale-95 flex items-center justify-center gap-2">
                <span>Connexion Express</span>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
            </button>
        </form>

        <div class="mt-8 text-center">
            <p class="text-gray-500 text-sm">Pas encore de compte ? <a href="inscription.php" class="text-teal-500 hover:underline font-semibold">Inscrivez-vous</a></p>
            <a href="panier.php" class="inline-block mt-6 text-gray-600 hover:text-gray-400 text-xs uppercase tracking-tighter transition">
                ← Retour au panier
            </a>
        </div>
    </div>

</body>
</html>