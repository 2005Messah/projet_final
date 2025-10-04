<?php
// Connexion à la base
try {
    $pdo = new PDO("mysql:host=localhost;dbname=suivi_stagiaires;charset=utf8", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

// Récupérer la liste des stagiaires
$stagiaires = $pdo->query("SELECT id, nom, prenom FROM stagiaires");
$S = $stagiaires->fetchAll(PDO::FETCH_ASSOC);

// Traitement du formulaire
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $stagiaire_id = $_POST['stagiaire_id'];  
    $date_debut   = $_POST['date_debut'];
    $date_fin     = $_POST['date_fin'];
    $etablissement = $_POST['etablissement'];
    $theme         = $_POST['theme'];

    // ✅ Insertion simplifiée (sans rapport ni convention)
    $stmt = $pdo->prepare("INSERT INTO stages (id_stagiaire, date_debut, date_fin, etablissement, theme) 
                           VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$stagiaire_id, $date_debut, $date_fin, $etablissement, $theme]);

    echo "<script>alert('Stage ajouté avec succès !'); window.location.href='profil.php?id=$stagiaire_id';</script>";
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter un Stage</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        :root {
            --violet-fonce: #6a0dad;
            --violet-clair: #d1c4e9;
            --blanc: #fff;
            --gris: #f9f9f9;
        }

        body {
            font-family: Arial, sans-serif;
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
            margin-bottom: 5px;
        }

        input[type="text"],
        input[type="date"],
        select {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 6px;
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
        <h2>Ajouter un nouveau stage</h2>
        <form method="POST">
            <div class="form-group full">
                <label for="stagiaire_id">Stagiaire :</label>
                <select name="stagiaire_id" required>
                    <option value="">-- Choisir un stagiaire --</option>
                    <?php foreach ($S as $s): ?>
                        <option value="<?= $s['id'] ?>">
                            <?= htmlspecialchars($s['prenom'] . " " . $s['nom']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="date_debut">Date début :</label>
                <input type="date" name="date_debut" required>
            </div>

            <div class="form-group">
                <label for="date_fin">Date fin :</label>
                <input type="date" name="date_fin" required>
            </div>

            <div class="form-group">
                <label for="etablissement">Établissement :</label>
                <input type="text" name="etablissement" required>
            </div>

            <div class="form-group full">
                <label for="theme">Thème :</label>
                <input type="text" name="theme" required>
            </div>

            <div class="form-group full">
                <button class="btn" type="submit">Ajouter le stage</button>
            </div>
        </form>
    </div>
</body>
</html>
