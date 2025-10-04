<?php
// Connexion à la base de données
try {
    $pdo = new PDO("mysql:host=localhost;dbname=suivi_stagiaires", "root", "");
} catch (PDOException $e) {
    die("Erreur : " . $e->getMessage());
}

// Récupération des stagiaires
$stagiaires = $pdo->query("SELECT id, nom, prenom FROM stagiaires");
$S = $stagiaires->fetchAll(PDO::FETCH_ASSOC);


?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Carte de Stagiaire</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        :root {
            --violet-fonce: #6a0dad;
            --violet-clair: #e1bee7;
            --blanc: #ffffff;
        }

        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(to bottom right, var(--violet-clair), var(--violet-fonce));
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 500px;
            margin: 80px auto;
            background-color: var(--blanc);
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
        }

        h2 {
            color: var(--violet-fonce);
            text-align: center;
        }

        form {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        select, button {
            padding: 12px;
            font-size: 16px;
            border-radius: 8px;
            border: 1px solid #ccc;
        }

        button {
            background-color: var(--violet-fonce);
            color: var(--blanc);
            border: none;
            cursor: pointer;
        }

        button:hover {
            background-color: #4b0d94;
        }

        @media (max-width: 600px) {
            .container {
                margin: 30px 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Télécharger la carte d’un stagiaire</h2>
        <form method="GET" action="carte_stagiaire.php" target="_blank">
            <label for="stagiaire">Sélectionnez un stagiaire :</label>
            <select name="id" id="stagiaire" required>
                <option value="">-- Choisir un stagiaire --</option>
                <?php foreach ($S as $s): ?>
                    <option value="<?= $s['id'] ?>">
                        <?= htmlspecialchars($s['nom'] . ' ' . $s['prenom']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit">Afficher la carte PDF</button>
        </form>
    </div>
</body>
</html>
