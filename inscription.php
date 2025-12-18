<?php
session_start();
include "connexion.php";

if (isset($_POST['register'])) {
    $nom = trim($_POST['nom']);
    $telephone = trim($_POST['telephone']);
    $adresse = trim($_POST['adresse']);
    $email = strtolower(trim($_POST['email']));
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $check = $pdo->prepare("SELECT id_client FROM client WHERE email = ?");
    $check->execute([$email]);

    if ($check->rowCount() > 0) {
        $error = "Cet email est déjà utilisé";
    } else {
        $req = $pdo->prepare("
            INSERT INTO client (nom, telephone, adresse, email, password)
            VALUES (?, ?, ?, ?, ?)
        ");
        $req->execute([$nom, $telephone, $adresse, $email, $password]);

        header("Location: login.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Inscription Client | Gallaxy Paint</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'primary-teal': '#00c896',
                        'dark-bg': '#1a1a1a',
                        'card-bg': '#2c2c2c',
                        'input-bg': '#3a3a3a',
                    },
                }
            }
        }
    </script>
</head>

<body class="min-h-screen flex items-center justify-center bg-dark-bg text-white">

    <div class="w-full max-w-lg bg-card-bg p-8 rounded-2xl shadow-2xl border border-primary-teal/30">

        <!-- Titre -->
        <div class="text-center mb-6">
            <h1 class="text-3xl font-extrabold text-primary-teal">Gallaxy Paint</h1>
            <p class="text-gray-400 mt-1">Créer un compte client</p>
        </div>

        <!-- Erreur -->
        <?php if (!empty($error)): ?>
            <div class="bg-red-900/40 text-red-300 p-3 rounded mb-4 border border-red-700">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <!-- Formulaire -->
        <form method="POST" class="space-y-4">

            <input type="text" name="nom" placeholder="Nom complet"
                class="w-full px-4 py-3 rounded-lg bg-input-bg text-white
                placeholder-gray-500 focus:ring-2 focus:ring-primary-teal focus:outline-none"
                required>

            <input type="tel" name="telephone" placeholder="Téléphone"
                class="w-full px-4 py-3 rounded-lg bg-input-bg text-white
                placeholder-gray-500 focus:ring-2 focus:ring-primary-teal focus:outline-none"
                required>

            <input type="text" name="adresse" placeholder="Adresse"
                class="w-full px-4 py-3 rounded-lg bg-input-bg text-white
                placeholder-gray-500 focus:ring-2 focus:ring-primary-teal focus:outline-none">

            <input type="email" name="email" placeholder="Adresse email"
                class="w-full px-4 py-3 rounded-lg bg-input-bg text-white
                placeholder-gray-500 focus:ring-2 focus:ring-primary-teal focus:outline-none"
                required>

            <input type="password" name="password" placeholder="Mot de passe"
                class="w-full px-4 py-3 rounded-lg bg-input-bg text-white
                placeholder-gray-500 focus:ring-2 focus:ring-primary-teal focus:outline-none"
                required>

            <button type="submit" name="register"
                class="w-full py-3 bg-primary-teal text-black font-semibold rounded-lg
                hover:bg-primary-teal/90 transition duration-300 shadow-lg">
                Créer mon compte
            </button>

        </form>

        <!-- Lien login -->
        <p class="text-center text-gray-400 mt-6">
            Déjà un compte ?
            <a href="login.php" class="text-primary-teal hover:underline">
                Se connecter
            </a>
        </p>

    </div>

</body>
</html>
