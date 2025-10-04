<?php
// Connexion à la base de données
try {
    $pdo = new PDO('mysql:host=localhost;dbname=suivi_stagiaires', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $sexe = $_POST['sexe'];
    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
    $date_naissance = $_POST['naissance'];
    $lieu_naissance = $_POST['lieu_naissance'];
    $telephone = $_POST['telephone'];
    $email =$_POST['email'];
    $mot_de_passe =$_POST['mot_de_passe'];
    $mot_de_passe_hash = password_hash($mot_de_passe, PASSWORD_DEFAULT);


    // Gestion de l'image
    $photo_path = "";
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
        $target_dir = "uploads/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $photo_path = $target_dir . basename($_FILES["photo"]["name"]);
        move_uploaded_file($_FILES["photo"]["tmp_name"], $photo_path);
    }


    // Requête d'insertion
    $sql = "INSERT INTO stagiaires (sexe, nom, prenom, naissance, lieu_naissance, telephone, email, mot_de_passe, photo)
            VALUES (:sexe, :nom, :prenom,  :naissance, :lieu_naissance, :telephone, :email, :mot_de_passe, :photo)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':sexe' => $sexe,
        ':nom' => $nom,
        ':prenom' => $prenom,
        ':naissance' => $date_naissance,
        ':lieu_naissance' => $lieu_naissance,
        ':telephone' => $telephone,
        ':email' => $email,
        ':mot_de_passe' => $mot_de_passe_hash,
        ':photo' => $photo_path,
        
    ]);

    $message = "Stagiaire ajouté avec succès.";
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter un stagiaire</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        :root {
            --violet-clair: #d1c4e9;
            --violet-fonce: #6a1b9a;
        }

        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
        }

        .container {
            max-width: 600px;
            margin: 30px auto;
            background: #fff;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }

        h2 {
            text-align: center;
            background-color: var(--violet-fonce);
            color: white;
            padding: 15px;
            border-radius: 10px 10px 0 0;
        }

        label {
            display: block;
            margin: 10px 0 5px;
            color: var(--violet-fonce);
            font-weight: bold;
        }

        input[type="text"],
        input[type="email"],
        input[type="date"],
        input[type="tel"],
        input[type="file"],
        select {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 6px;
            border: 1px solid #ccc;
        }

        .radio-group {
            display: flex;
            gap: 20px;
        }

        button {
            background-color: var(--violet-fonce);
            color: white;
            border: none;
            padding: 12px;
            width: 100%;
            font-size: 16px;
            border-radius: 6px;
            cursor: pointer;
        }

        .message {
            margin-top: 15px;
            color: green;
            text-align: center;
        }

        @media (max-width: 500px) {
            .radio-group {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
<div class="container">
    <h2>Ajouter un Stagiaire</h2>
    <?php if ($message): ?>
        <p class="message"><?= $message ?></p>
    <?php endif; ?>
    <form method="POST" enctype="multipart/form-data">
         <label>Sexe :</label>
        <div class="radio-group">
            <label><input type="radio" name="sexe" value="F" required> F</label>
            <label><input type="radio" name="sexe" value="M"> M</label>
        </div>
        <label for="nom">Nom :</label>
        <input type="text" name="nom" id="nom" required>

        <label for="prenom">Prénom :</label>
        <input type="text" name="prenom" id="prenom" required>

        <label for="date_naissance">Date de naissance :</label>
        <input type="date" name="naissance" id="naissance" required>

        <label for="lieu_naissance">Lieu de naissance :</label>
        <input type="text" name="lieu_naissance" id="lieu_naissance" required>

        <label for="telephone">Téléphone :</label>
        <input type="tel" name="telephone" id="telephone" required>

        <label for="email">Email :</label>
        <input type="email" name="email" id="email" required>
       
        <label for="mot_de_passe">mot_de_passe :</label>
        <input type="password" name="mot_de_passe" id="mot_de_passe" required>
        

        <label for="photo">Photo :</label>
        <input type="file" name="photo" id="photo" accept="image/*">

        <button type="submit">Ajouter le stagiaire</button>
    </form>
</div>
</body>
</html>
