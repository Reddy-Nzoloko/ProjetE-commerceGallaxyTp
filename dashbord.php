<?php
session_start();
if(!isset($_SESSION['admin'])){
    header("Location: login.php");
    exit;
}
include 'connexion.php'; // Assurez-vous que ce fichier est correctement inclus.

/* -------------------------------
    TRAITEMENT CRUD
--------------------------------*/
// Votre logique CRUD PHP reste inchangée pour garantir la fonctionnalité.
// ... (Logique CRUD complète de l'original) ...

// Ajouter produit
if(isset($_POST['ajouterProduit'])){
    $nom = $_POST['nom'];
    $categorie = $_POST['categorie'];
    $code = $_POST['code'];
    $couleur = $_POST['couleur'];
    $taille = $_POST['taille'];
    $prix = $_POST['prix'];
    $description = $_POST['description'];

    $photo = null;
    if(!empty($_FILES['photo']['name'])){
        $photo = time()."_".$_FILES['photo']['name'];
        move_uploaded_file($_FILES['photo']['tmp_name'], "telechargement/".$photo);
    }
    $sql = "INSERT INTO produit(nom_produit,id_categorie,code_produit,couleur,taille,prix,description,photo,date_ajout)
             VALUES(?,?,?,?,?,?,?,?,NOW())";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$nom,$categorie,$code,$couleur,$taille,$prix,$description,$photo]);
    header("Location: dashbord.php"); exit;
}

// Modifier produit
if(isset($_POST['modifierProduit'])){
    $id = $_POST['id_produit'];
    $nom = $_POST['nom'];
    $categorie = $_POST['categorie'];
    $code = $_POST['code'];
    $couleur = $_POST['couleur'];
    $taille = $_POST['taille'];
    $prix = $_POST['prix'];
    $description = $_POST['description'];

    $params = [$nom,$categorie,$code,$couleur,$taille,$prix,$description];
    $photoSql = "";
    if(!empty($_FILES['photo']['name'])){
        $photo = time()."_".$_FILES['photo']['name'];
        move_uploaded_file($_FILES['photo']['tmp_name'], "telechargement/".$photo);
        $photoSql = ", photo=?";
        $params[] = $photo;
    }
    $params[] = $id;
    $sql = "UPDATE produit SET nom_produit=?, id_categorie=?, code_produit=?, couleur=?, taille=?, prix=?, description=? $photoSql WHERE id_produit=?";
    $pdo->prepare($sql)->execute($params);
    header("Location: dashbord.php"); exit;
}

// Supprimer produit
if(isset($_GET['supprimer_produit'])){
    $pdo->prepare("DELETE FROM produit WHERE id_produit=?")->execute([$_GET['supprimer_produit']]);
    header("Location: dashbord.php"); exit;
}

// Ajouter admin
if(isset($_POST['ajouterAdmin'])){
    $username=$_POST['username'];
    $email=$_POST['email'];
    $password=password_hash($_POST['password'],PASSWORD_DEFAULT);
    $pdo->prepare("INSERT INTO admin(username,email,password) VALUES(?,?,?)")->execute([$username,$email,$password]);
    header("Location: dashbord.php"); exit;
}

// Modifier admin
if(isset($_POST['modifierAdmin'])){
    $id=$_POST['id_admin'];
    $username=$_POST['username'];
    $email=$_POST['email'];
    if(!empty($_POST['password'])){
        $pass=password_hash($_POST['password'],PASSWORD_DEFAULT);
        $pdo->prepare("UPDATE admin SET username=?,email=?,password=? WHERE id_admin=?")->execute([$username,$email,$pass,$id]);
    }else{
        $pdo->prepare("UPDATE admin SET username=?,email=? WHERE id_admin=?")->execute([$username,$email,$id]);
    }
    header("Location: dashbord.php"); exit;
}

// Supprimer admin
if(isset($_GET['supprimer_admin'])){
    $pdo->prepare("DELETE FROM admin WHERE id_admin=?")->execute([$_GET['supprimer_admin']]);
    header("Location: dashbord.php"); exit;
}

// Ajouter catégorie
if(isset($_POST['ajouterCategorie'])){
    $pdo->prepare("INSERT INTO categorie(nom_categorie) VALUES(?)")->execute([$_POST['nom_categorie']]);
    header("Location: dashbord.php"); exit;
}

// Supprimer catégorie
if(isset($_GET['supprimer_categorie'])){
    $pdo->prepare("DELETE FROM categorie WHERE id_categorie=?")->execute([$_GET['supprimer_categorie']]);
    header("Location: dashbord.php"); exit;
}

/* -------------------------------
    DONNEES
--------------------------------*/
$produits=$pdo->query("SELECT p.*,c.nom_categorie FROM produit p LEFT JOIN categorie c ON p.id_categorie=c.id_categorie ORDER BY p.date_ajout DESC")->fetchAll();
$admins=$pdo->query("SELECT * FROM admin")->fetchAll();
$categories=$pdo->query("SELECT * FROM categorie")->fetchAll();
$totalProduits=$pdo->query("SELECT COUNT(*) FROM produit")->fetchColumn();
$totalAdmins=$pdo->query("SELECT COUNT(*) FROM admin")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gallaxy paint</title>
    <link rel="icon" href="telechargement/Caroucelle/icon.ico" type="image/x-icon">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Styles pour l'impression ciblée */
        @media print {
            body * {
                visibility: hidden; /* Cache tout par défaut */
            }
            #produitsTable, #produitsTable * {
                visibility: visible; /* Rend visible uniquement la section du tableau des produits */
            }
            #produitsTable {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
                margin: 0;
                padding: 10px;
            }
            /* Masquer la colonne "Actions" pour l'impression */
            #produitsTable thead tr th:last-child,
            #produitsTable tbody tr td:last-child {
                display: none !important;
            }
            /* Styles pour que le tableau soit propre à l'impression */
            #produitsTable table {
                width: 100%;
                border-collapse: collapse;
            }
            #produitsTable th, #produitsTable td {
                border: 1px solid #ccc;
                padding: 8px;
            }
        }

        /* Classes pour la gestion des modals (remplacement de Bootstrap) */
        .modal-tail-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            display: none; /* Caché par défaut */
            z-index: 50;
            opacity: 0;
            transition: opacity 0.3s ease-in-out;
        }
        .modal-tail-overlay.open {
            display: flex;
            opacity: 1;
        }
        .modal-tail-content {
            transform: translateY(-50px);
            transition: transform 0.3s ease-in-out;
        }
        .modal-tail-overlay.open .modal-tail-content {
            transform: translateY(0);
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">

<div class="container mx-auto p-6 lg:p-10">
    
    <header class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-extrabold text-indigo-700">Gallaxy paint </h1>
        <a href="adminCommande.php"
   class="bg-gray-800 text-white px-4 py-2 rounded hover:bg-gray-700">
   📦 Gérer les commandes
</a>

        <a href="logout.php" class="py-2 px-4 bg-red-600 text-white font-semibold rounded-lg shadow-md hover:bg-red-700 transition duration-300 ease-in-out transform hover:scale-105">
            Déconnexion
        </a>
    </header>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-10">
        <div class="bg-gradient-to-r from-indigo-500 to-blue-500 rounded-xl p-6 text-white shadow-xl hover:shadow-2xl transition duration-300 ease-in-out transform hover:scale-[1.02]">
            <h2 class="text-lg font-semibold mb-2 opacity-80">Total Produits</h2>
            <p class="text-5xl font-bold"><?= $totalProduits ?></p>
        </div>
        <div class="bg-gradient-to-r from-green-500 to-teal-500 rounded-xl p-6 text-white shadow-xl hover:shadow-2xl transition duration-300 ease-in-out transform hover:scale-[1.02]">
            <h2 class="text-lg font-semibold mb-2 opacity-80">Total Admins</h2>
            <p class="text-5xl font-bold"><?= $totalAdmins ?></p>
        </div>
    </div>
    
    <hr class="my-8 border-t border-gray-200" />

    <section>
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-2xl font-bold text-gray-800">Gestion des Produits </h2>
            <div class="space-x-3">
                <button class="py-2 px-4 bg-teal-500 text-white font-semibold rounded-lg shadow-md hover:bg-teal-600 transition duration-300 ease-in-out" onclick="printTable('produitsTable')">
                     Imprimer Tableau
                </button>
                <button class="py-2 px-4 bg-indigo-600 text-white font-semibold rounded-lg shadow-md hover:bg-indigo-700 transition duration-300 ease-in-out transform hover:scale-105" data-modal-target="ajoutProduit">
                    + Ajouter Produit
                </button>
            </div>
        </div>

        <div id="produitsTable" class="bg-white shadow-lg rounded-xl overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Photo</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nom</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Catégorie</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Prix</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach($produits as $p): ?>
                    <tr class="hover:bg-indigo-50 transition duration-150">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?= $p['id_produit'] ?></td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?php if($p['photo']): ?>
                            <img src="telechargement/<?= $p['photo'] ?>" class="w-16 h-16 object-cover rounded-md shadow-sm" alt="Photo <?= htmlspecialchars($p['nom_produit']) ?>">
                            <?php else: ?>
                            <span class="text-xs text-gray-400">Pas d'image</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700"><?= htmlspecialchars($p['nom_produit']) ?></td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                <?= htmlspecialchars($p['nom_categorie']) ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 font-bold"><?= $p['prix'] ?> $</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                            <button class="text-yellow-600 hover:text-yellow-800 transition duration-150" data-modal-target="editProduit<?= $p['id_produit'] ?>">
                                Modifier
                            </button>
                            <a href="?supprimer_produit=<?= $p['id_produit'] ?>" class="text-red-600 hover:text-red-800 transition duration-150" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce produit ?')">
                                Supprimer
                            </a>
                        </td>
                    </tr>

                    <?= modalProduit($p, $categories, 'modifierProduit') ?>

                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>

    <hr class="my-8 border-t border-gray-200" />

    <section>
        <div class="flex justify-between items-center mt-8 mb-4">
            <h2 class="text-2xl font-bold text-gray-800">Gestion des Administrateurs </h2>
            <button class="py-2 px-4 bg-indigo-600 text-white font-semibold rounded-lg shadow-md hover:bg-indigo-700 transition duration-300 ease-in-out transform hover:scale-105" data-modal-target="ajoutAdmin">
                + Ajouter Admin
            </button>
        </div>
        
        <div class="bg-white shadow-lg rounded-xl overflow-x-auto mb-10">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nom</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach($admins as $a): ?>
                    <tr class="hover:bg-indigo-50 transition duration-150">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?= $a['id_admin'] ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700"><?= htmlspecialchars($a['username']) ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700"><?= htmlspecialchars($a['email']) ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                            <button class="text-yellow-600 hover:text-yellow-800 transition duration-150" data-modal-target="editAdmin<?= $a['id_admin'] ?>">
                                Modifier
                            </button>
                            <a href="?supprimer_admin=<?= $a['id_admin'] ?>" class="text-red-600 hover:text-red-800 transition duration-150" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet administrateur ?')">
                                Supprimer
                            </a>
                        </td>
                    </tr>

                    <?= modalAdmin($a, 'modifierAdmin') ?>

                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>

    <hr class="my-8 border-t border-gray-200" />

    <section>
        <div class="flex justify-between items-center mt-8 mb-4">
            <h2 class="text-2xl font-bold text-gray-800">Gestion des Catégories </h2>
            <button class="py-2 px-4 bg-indigo-600 text-white font-semibold rounded-lg shadow-md hover:bg-indigo-700 transition duration-300 ease-in-out transform hover:scale-105" data-modal-target="ajoutCategorie">
                + Ajouter Catégorie
            </button>
        </div>
        
        <div class="bg-white shadow-lg rounded-xl overflow-x-auto mb-10">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nom</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach($categories as $c): ?>
                    <tr class="hover:bg-indigo-50 transition duration-150">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?= $c['id_categorie'] ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700"><?= htmlspecialchars($c['nom_categorie']) ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                            <a href="?supprimer_categorie=<?= $c['id_categorie'] ?>" class="text-red-600 hover:text-red-800 transition duration-150" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette catégorie ?')">
                                Supprimer
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
</div>

<?= modalProduit([], $categories, 'ajouterProduit') ?>

<?= modalAdmin([], 'ajouterAdmin') ?>

<?= modalCategorie() ?>

<?php
/**
 * Génère le code HTML pour un Modal Produit.
 * @param array $p Données du produit (vide pour l'ajout).
 * @param array $categories Liste des catégories.
 * @param string $action Nom de l'action ('ajouterProduit' ou 'modifierProduit').
 * @return string Code HTML du modal.
 */
function modalProduit($p, $categories, $action) {
    $isEdit = $action === 'modifierProduit';
    $id = $isEdit ? $p['id_produit'] : '';
    $modalId = $isEdit ? "editProduit{$id}" : "ajoutProduit";
    $title = $isEdit ? 'Modifier Produit' : 'Ajouter Produit';
    $buttonText = $isEdit ? 'Enregistrer' : 'Ajouter';
    $currentPhoto = $isEdit && $p['photo'] ? $p['photo'] : '';

    ob_start();
    ?>
    <div class="modal-tail-overlay items-center justify-center p-4" id="<?= $modalId ?>">
        <div class="modal-tail-content bg-white rounded-xl shadow-2xl w-full max-w-lg mx-auto">
            <form method="post" enctype="multipart/form-data">
                <div class="p-5 border-b border-gray-100 flex justify-between items-center">
                    <h5 class="text-xl font-bold text-gray-800"><?= $title ?></h5>
                    <button type="button" class="text-gray-400 hover:text-gray-600 transition duration-150" data-modal-close="<?= $modalId ?>">&times;</button>
                </div>
                <div class="p-6 space-y-4">
                    <?php if ($isEdit): ?>
                    <input type="hidden" name="id_produit" value="<?= $id ?>">
                    <?php endif; ?>

                    <input class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition duration-150" name="nom" value="<?= $isEdit ? htmlspecialchars($p['nom_produit']) : '' ?>" placeholder="Nom" required>

                    <select class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition duration-150" name="categorie" required>
                        <option value="">Choisir catégorie</option>
                        <?php foreach($categories as $c): ?>
                        <option value="<?= $c['id_categorie'] ?>" <?= $isEdit && $c['id_categorie']==$p['id_categorie'] ? 'selected' : '' ?>><?= htmlspecialchars($c['nom_categorie']) ?></option>
                        <?php endforeach; ?>
                    </select>

                    <input class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition duration-150" name="code" value="<?= $isEdit ? htmlspecialchars($p['code_produit']) : '' ?>" placeholder="Code" required>

                    <input class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition duration-150" name="couleur" value="<?= $isEdit ? htmlspecialchars($p['couleur']) : '' ?>" placeholder="Couleur">

                    <input class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition duration-150" name="taille" value="<?= $isEdit ? htmlspecialchars($p['taille']) : '' ?>" placeholder="Taille">

                    <input type="number" step="0.01" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition duration-150" name="prix" value="<?= $isEdit ? $p['prix'] : '' ?>" placeholder="Prix" required>

                    <textarea class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition duration-150 h-24" name="description" placeholder="Description"><?= $isEdit ? htmlspecialchars($p['description']) : '' ?></textarea>

                    <input type="file" name="photo" class="w-full p-3 border border-gray-300 rounded-lg file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer">
                    <?php if($currentPhoto): ?>
                    <small class="text-sm text-gray-500 block mt-1">Image actuelle : <strong><?= htmlspecialchars($currentPhoto) ?></strong></small>
                    <?php endif; ?>
                </div>
                <div class="p-5 border-t border-gray-100 flex justify-end space-x-3">
                    <button type="button" class="py-2 px-4 bg-gray-200 text-gray-800 font-semibold rounded-lg hover:bg-gray-300 transition duration-150" data-modal-close="<?= $modalId ?>">Fermer</button>
                    <button type="submit" name="<?= $action ?>" class="py-2 px-4 bg-indigo-600 text-white font-semibold rounded-lg shadow-md hover:bg-indigo-700 transition duration-150"><?= $buttonText ?></button>
                </div>
            </form>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

/**
 * Génère le code HTML pour un Modal Admin.
 * @param array $a Données de l'admin (vide pour l'ajout).
 * @param string $action Nom de l'action ('ajouterAdmin' ou 'modifierAdmin').
 * @return string Code HTML du modal.
 */
function modalAdmin($a, $action) {
    $isEdit = $action === 'modifierAdmin';
    $id = $isEdit ? $a['id_admin'] : '';
    $modalId = $isEdit ? "editAdmin{$id}" : "ajoutAdmin";
    $title = $isEdit ? 'Modifier Admin' : 'Ajouter Admin';
    $buttonText = $isEdit ? 'Enregistrer' : 'Ajouter';

    ob_start();
    ?>
    <div class="modal-tail-overlay items-center justify-center p-4" id="<?= $modalId ?>">
        <div class="modal-tail-content bg-white rounded-xl shadow-2xl w-full max-w-md mx-auto">
            <form method="post">
                <div class="p-5 border-b border-gray-100 flex justify-between items-center">
                    <h5 class="text-xl font-bold text-gray-800"><?= $title ?></h5>
                    <button type="button" class="text-gray-400 hover:text-gray-600 transition duration-150" data-modal-close="<?= $modalId ?>">&times;</button>
                </div>
                <div class="p-6 space-y-4">
                    <?php if ($isEdit): ?>
                    <input type="hidden" name="id_admin" value="<?= $id ?>">
                    <?php endif; ?>

                    <input class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition duration-150" name="username" value="<?= $isEdit ? htmlspecialchars($a['username']) : '' ?>" placeholder="Nom utilisateur" required>

                    <input type="email" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition duration-150" name="email" value="<?= $isEdit ? htmlspecialchars($a['email']) : '' ?>" placeholder="Email" required>

                    <input type="password" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition duration-150" name="password" placeholder="<?= $isEdit ? 'Nouveau mot de passe (laisser vide)' : 'Mot de passe' ?>" <?= $isEdit ? '' : 'required' ?>>
                </div>
                <div class="p-5 border-t border-gray-100 flex justify-end space-x-3">
                    <button type="button" class="py-2 px-4 bg-gray-200 text-gray-800 font-semibold rounded-lg hover:bg-gray-300 transition duration-150" data-modal-close="<?= $modalId ?>">Fermer</button>
                    <button type="submit" name="<?= $action ?>" class="py-2 px-4 bg-indigo-600 text-white font-semibold rounded-lg shadow-md hover:bg-indigo-700 transition duration-150"><?= $buttonText ?></button>
                </div>
            </form>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

/**
 * Génère le code HTML pour un Modal Catégorie.
 * @return string Code HTML du modal.
 */
function modalCategorie() {
    ob_start();
    ?>
    <div class="modal-tail-overlay items-center justify-center p-4" id="ajoutCategorie">
        <div class="modal-tail-content bg-white rounded-xl shadow-2xl w-full max-w-md mx-auto">
            <form method="post">
                <div class="p-5 border-b border-gray-100 flex justify-between items-center">
                    <h5 class="text-xl font-bold text-gray-800">Ajouter Catégorie</h5>
                    <button type="button" class="text-gray-400 hover:text-gray-600 transition duration-150" data-modal-close="ajoutCategorie">&times;</button>
                </div>
                <div class="p-6 space-y-4">
                    <input class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition duration-150" name="nom_categorie" placeholder="Nom catégorie" required>
                </div>
                <div class="p-5 border-t border-gray-100 flex justify-end space-x-3">
                    <button type="button" class="py-2 px-4 bg-gray-200 text-gray-800 font-semibold rounded-lg hover:bg-gray-300 transition duration-150" data-modal-close="ajoutCategorie">Fermer</button>
                    <button type="submit" name="ajouterCategorie" class="py-2 px-4 bg-indigo-600 text-white font-semibold rounded-lg shadow-md hover:bg-indigo-700 transition duration-150">Ajouter</button>
                </div>
            </form>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
?>

<script>
    /**
     * Ouvre le modal spécifié.
     * @param {string} modalId L'ID du modal à ouvrir.
     */
    function openModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.add('open');
            // Empêche le défilement du corps de la page lorsque le modal est ouvert
            document.body.style.overflow = 'hidden';
        }
    }

    /**
     * Ferme le modal spécifié.
     * @param {string} modalId L'ID du modal à fermer.
     */
    function closeModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.remove('open');
            // Rétablit le défilement du corps de la page
            document.body.style.overflow = '';
        }
    }

    /**
     * Gestionnaire d'événements pour les boutons d'ouverture et de fermeture.
     */
    document.addEventListener('DOMContentLoaded', () => {
        // Écouteurs pour les boutons d'ouverture (data-modal-target)
        document.querySelectorAll('[data-modal-target]').forEach(button => {
            button.addEventListener('click', () => {
                const targetId = button.getAttribute('data-modal-target');
                openModal(targetId);
            });
        });

        // Écouteurs pour les boutons de fermeture (data-modal-close)
        document.querySelectorAll('[data-modal-close]').forEach(button => {
            button.addEventListener('click', () => {
                const targetId = button.getAttribute('data-modal-close');
                closeModal(targetId);
            });
        });

        // Écouteurs pour fermer le modal en cliquant en dehors du contenu
        document.querySelectorAll('.modal-tail-overlay').forEach(overlay => {
            overlay.addEventListener('click', (e) => {
                // S'assurer que le clic n'est pas sur le contenu du modal
                if (e.target.classList.contains('modal-tail-overlay')) {
                    closeModal(overlay.id);
                }
            });
        });

        // Fermer les modals avec la touche Echap
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                document.querySelectorAll('.modal-tail-overlay.open').forEach(modal => {
                    closeModal(modal.id);
                });
            }
        });
    });

    /**
     * Imprime uniquement le contenu d'un élément spécifique.
     * @param {string} elementId L'ID de l'élément à imprimer (ici 'produitsTable').
     */
    function printTable(elementId) {
        // La magie se passe dans le CSS @media print.
        // On appelle window.print() et le CSS se charge de n'afficher que l'élément #produitsTable.
        window.print();
    }
</script>

</body>
</html>