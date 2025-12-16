<?php
include "connexion.php"; // connexion PDO à ta base

$message = "";

// ✅ Vérifier si le formulaire est soumis
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST["username"]);
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);

    // Vérification basique
    if (!empty($username) && !empty($password)) {
        // ✅ Hachage du mot de passe
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // ✅ Préparer l'insertion
        $stmt = $pdo->prepare("INSERT INTO admin (username, password, email) VALUES (?, ?, ?)");

        try {
            $stmt->execute([$username, $hashed_password, $email]);
            $message = "<p class='text-green-600 font-semibold'>✅ Administrateur ajouté avec succès !</p>";
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $message = "<p class='text-red-600 font-semibold'>⚠️ Ce nom d'utilisateur existe déjà.</p>";
            } else {
                $message = "<p class='text-red-600 font-semibold'>❌ Erreur : " . htmlspecialchars($e->getMessage()) . "</p>";
            }
        }
    } else {
        $message = "<p class='text-red-600 font-semibold'>⚠️ Tous les champs obligatoires doivent être remplis.</p>";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Ajouter un administrateur</title>
    <link rel="icon" href="telechargement/Caroucelle/icon.ico" type="image/x-icon">

  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">

  <div class="bg-white shadow-lg rounded-2xl p-8 w-full max-w-md">
    <h2 class="text-2xl font-bold text-center mb-6 text-gray-800">Ajouter un administrateur</h2>

    <?= $message ?>

    <form method="POST" class="space-y-4">
      <div>
        <label for="username" class="block text-gray-700 font-medium">Nom d'utilisateur *</label>
        <input type="text" name="username" id="username" required
               class="w-full mt-1 p-2 border rounded-lg focus:ring-2 focus:ring-blue-500 outline-none">
      </div>

      <div>
        <label for="email" class="block text-gray-700 font-medium">Email</label>
        <input type="email" name="email" id="email"
               class="w-full mt-1 p-2 border rounded-lg focus:ring-2 focus:ring-blue-500 outline-none">
      </div>

      <div>
        <label for="password" class="block text-gray-700 font-medium">Mot de passe *</label>
        <input type="password" name="password" id="password" required
               class="w-full mt-1 p-2 border rounded-lg focus:ring-2 focus:ring-blue-500 outline-none">
      </div>

      <button type="submit"
              class="w-full bg-blue-600 text-white py-2 rounded-lg font-semibold hover:bg-blue-700 transition">
        Ajouter
      </button>
    </form>
  </div>

</body>
</html>
