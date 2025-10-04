<?php
// Connexion à la base de données
try {
    $pdo = new PDO("mysql:host=localhost;dbname=suivi_stagiaires", "root", "");
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

// Récupération des données du stagiaire à modifier
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $pdo->prepare("SELECT * FROM stagiaires WHERE id = ?");
    $stmt->execute([$id]);
    $stagiaire = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$stagiaire) {
        die("Stagiaire non trouvé.");
    }
} else {
    die("ID manquant.");
}

// Mise à jour des données
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $sexe = $_POST['sexe'];
    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
    $naissance = $_POST['naissance'];
    $lieu = $_POST['lieu_naissance'];
    $telephone = $_POST['telephone'];
    $email = $_POST['email'];
    $mot_de_passe = $_POST['mot_de_passe'];

    // Si une nouvelle photo est envoyée
    if (!empty($_FILES['photo']['name'])) {
        $target_dir = "uploads/";
        if (!is_dir($target_dir)) mkdir($target_dir);
        $photo = $target_dir . basename($_FILES["photo"]["name"]);
        move_uploaded_file($_FILES["photo"]["tmp_name"], $photo);
    } else {
        $photo = $stagiaire['photo'];
    }

    $stmt = $pdo->prepare("UPDATE stagiaire SET sexe=?, nom=?, prenom=?, naissance=?, lieu_naissance=?, telephone=?, email=?, mot_de_passe=?, photo=? WHERE id=?");
    $stmt->execute([$sexe, $nom, $prenom, $sexe, $naissance, $lieu, $telephone, $email, $photo, $mot_de_passe, $id]);

    echo "<script>alert('Stagiaire modifié avec succès !'); window.location.href='test2.php';</script>";
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier Stagiaire</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        :root {
            --violet-fonce: #6a0dad;
            --violet-clair: #e1bee7;
            --blanc: #ffffff;
            --gris: #f5f5f5;
        }

        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: var(--gris);
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 700px;
            margin: 40px auto;
            background: var(--blanc);
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        }

        h2 {
            background-color: var(--violet-fonce);
            color: var(--blanc);
            text-align: center;
            padding: 15px;
            border-radius: 10px 10px 0 0;
        }

        form {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }

        .form-group {
            flex: 1 1 45%;
            display: flex;
            flex-direction: column;
        }

        .form-group.full {
            flex: 1 1 100%;
        }

        label {
            font-weight: bold;
            color: var(--violet-fonce);
        }

        input[type="text"],
        input[type="email"],
        input[type="date"],
        input[type="tel"],
        input[type="file"] {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 6px;
        }

        .radio-group {
            display: flex;
            gap: 15px;
        }

        .btn {
            margin-top: 20px;
            padding: 12px;
            background-color: var(--violet-fonce);
            color: var(--blanc);
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
        }

        .btn:hover {
            background-color: #512da8;
        }

        @media (max-width: 600px) {
            .form-group {
                flex: 1 1 100%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Modifier le stagiaire</h2>
        <form method="POST" enctype="multipart/form-data">
            
            <div class="form-group">
                <label>Sexe :</label>
                <div class="radio-group">
                    <label><input type="radio" name="sexe" value="M" <?= $stagiaire['sexe'] === 'M' ? 'checked' : '' ?>> M</label>
                    <label><input type="radio" name="sexe" value="F" <?= $stagiaire['sexe'] === 'F' ? 'checked' : '' ?>> F</label>
                </div>
            </div>

            <div class="form-group">
                <label>Nom :</label>
                <input type="text" name="nom" value="<?= htmlspecialchars($stagiaire['nom']) ?>" required>
            </div>

            <div class="form-group">
                <label>Prénom :</label>
                <input type="text" name="prenom" value="<?= htmlspecialchars($stagiaire['prenom']) ?>" required>
            </div>

            <div class="form-group">
                <label>Date de naissance :</label>
                <input type="date" name="naissance" value="<?= $stagiaire['naissance'] ?>" required>
            </div>

            <div class="form-group">
                <label>Lieu de naissance :</label>
                <input type="text" name="lieu_naissance" value="<?= htmlspecialchars($stagiaire['lieu_naissance']) ?>" required>
            </div>

            <div class="form-group">
                <label>Téléphone :</label>
                <input type="tel" name="telephone" value="<?= $stagiaire['telephone'] ?>" required>
            </div>

            <div class="form-group">
                <label>Email :</label>
                <input type="email" name="email" value="<?= htmlspecialchars($stagiaire['email']) ?>" required>
            </div>

            <div class="form-group">
                <label>Mot de passe :</label>
                <input type="password" name="mot_de_passe" value="<?= htmlspecialchars($stagiaire['mot_de_passe']) ?>" required>
            </div>

            <div class="form-group full">
                <label>Photo :</label>
                <input type="file" name="photo">
                <?php if (!empty($stagiaire['photo'])): ?>
                    <p>Photo actuelle : <a href="<?= $stagiaire['photo'] ?>" target="_blank">Voir</a></p>
                <?php endif; ?>
            </div>

            <div class="form-group full">
                <button class="btn" type="submit">Enregistrer les modifications</button>
            </div>
        </form>
    </div>
</body>
</html>
