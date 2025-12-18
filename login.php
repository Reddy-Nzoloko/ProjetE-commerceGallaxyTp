<?php
session_start();
include "connexion.php";

if (isset($_POST['login'])) {
    $email = strtolower(trim($_POST['email']));
    $password = $_POST['password'];

    try {
        /** 1️⃣ Vérifier ADMIN */
        $reqAdmin = $pdo->prepare("SELECT * FROM admin WHERE email = ? LIMIT 1");
        $reqAdmin->execute([$email]);
        $admin = $reqAdmin->fetch();

        if ($admin && password_verify($password, $admin['password'])) {
            $_SESSION['role'] = 'admin';
            $_SESSION['admin_id'] = $admin['id_admin'];
            $_SESSION['admin_email'] = $admin['email'];
            $_SESSION['admin_username'] = $admin['username'];

            header("Location: dashbord.php");
            exit;
        }

        /** 2️⃣ Vérifier CLIENT */
        $reqClient = $pdo->prepare("SELECT * FROM client WHERE email = ? LIMIT 1");
        $reqClient->execute([$email]);
        $client = $reqClient->fetch();

        if ($client && password_verify($password, $client['password'])) {
            $_SESSION['role'] = 'client';
            $_SESSION['client_id'] = $client['id_client'];
            $_SESSION['client_email'] = $client['email'];
            $_SESSION['client_nom'] = $client['nom'];

            header("Location: client_dashboard.php");
            exit;
        }

        /** ❌ Aucun trouvé */
        $error = "Adresse email ou mot de passe incorrect";

    } catch (PDOException $e) {
        $error = "Erreur serveur : " . $e->getMessage();
    }
}
?>


<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gallaxy paint</title>
    <link rel="icon" href="telechargement/Caroucelle/icon.ico" type="image/x-icon">
    <script src="https://cdn.tailwindcss.com"></script>
    
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'primary-teal': '#00c896', // La couleur que vous utilisiez (vert/bleu)
                        'dark-bg': '#1a1a1a',
                        'card-bg': '#2c2c2c',
                        'input-bg': '#3a3a3a',
                    },
                    keyframes: {
                        fadeInUp: {
                            '0%': { opacity: '0', transform: 'translateY(20px)' },
                            '100%': { opacity: '1', transform: 'translateY(0)' },
                        },
                    },
                    animation: {
                        'fade-in-up': 'fadeInUp 0.8s ease-out',
                    },
                }
            }
        }
    </script>
    
    <link rel="icon" href="telechargement/Caroucelle/icon.ico" type="image/x-icon">
</head>

<body class="min-h-screen flex items-center justify-center bg-dark-bg text-white" 
      style="background-image: linear-gradient(135deg, var(--tw-colors-dark-bg), #2c2c2c);">
    
    <div class="w-full max-w-md bg-card-bg p-8 rounded-xl shadow-2xl relative animate-fade-in-up border border-primary-teal/30">
        
        <a href="index.php" class="absolute top-4 right-4 text-gray-400 hover:text-white transition-colors duration-300 group">
            <svg class="w-6 h-6 group-hover:rotate-90 transition-transform duration-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </a>

        <div class="text-center mb-6">
            <h1 class="text-3xl font-extrabold text-primary-teal tracking-tight">Gallaxy paint</h1>
            <p class="text-gray-400 mt-1 text-xl font-semibold">Connexion Administrateur</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="bg-red-900/40 text-red-300 p-3 rounded mb-4 flex items-center gap-2 border border-red-700">
                <svg class="w-5 h-5 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M8.48 2.072a2 2 0 013.04 0l5.48 9.13A2 2 0 0115 14H5a2 2 0 01-1.52-2.798l5.48-9.13zM10 8a1 1 0 00-1 1v2a1 1 0 102 0V9a1 1 0 00-1-1zm1 4a1 1 0 10-2 0h2z" clip-rule="evenodd" />
                </svg>
                <span><?php echo $error; ?></span>
            </div>
        <?php endif; ?>

        <form method="POST" class="space-y-4">
            
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="w-5 h-5 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a1.5 1.5 0 01-1.5 1.5h-15a1.5 1.5 0 01-1.5-1.5V6.75m18 0l-9 5.25L3.75 6.75" />
                    </svg>
                </div>
                <input type="email" name="email" id="email" 
                       class="w-full pl-10 pr-4 py-3 rounded-lg bg-input-bg text-white placeholder-gray-500 focus:ring-2 focus:ring-primary-teal focus:border-primary-teal border border-transparent transition duration-300 focus:outline-none" 
                       placeholder="Adresse Email" required>
            </div>
            
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="w-5 h-5 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V19.5M12 21H6.75A2.25 2.25 0 014.5 18.75V7.5M4.5 7.5a3 3 0 016 0h9a3 3 0 016 0v11.25m-18 0V19.5" />
                    </svg>
                </div>
                <input type="password" name="password" id="password" 
                       class="w-full pl-10 pr-4 py-3 rounded-lg bg-input-bg text-white placeholder-gray-500 focus:ring-2 focus:ring-primary-teal focus:border-primary-teal border border-transparent transition duration-300 focus:outline-none" 
                       placeholder="Mot de passe" required>
            </div>
            
            <button type="submit" name="login" 
                    class="w-full flex items-center justify-center py-3 bg-primary-teal text-white font-semibold rounded-lg shadow-lg hover:bg-primary-teal/90 transform hover:scale-[1.01] transition-all duration-300 focus:outline-none focus:ring-4 focus:ring-primary-teal/50 active:scale-95">
                <svg class="w-5 h-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l3-3m0 0l-3-3m3 3H9" />
                </svg>
                Se connecter
            </button>
            <a href="inscription.php">Ajout Client</a>
        </form>
    </div>
</body>
</html>