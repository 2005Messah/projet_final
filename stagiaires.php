<?php
// Connexion à la base de données
$host = "localhost";
$dbname = "suivi_stagiaires";
$username = "root";
$password = "";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

// Requête pour récupérer les stagiaires
$query = $pdo->query("SELECT * FROM stagiaires");
$stagiaires = $query->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des stagiaires</title>
    <style>
        :root {
            --violet-fonce: #6a1b9a;
            --violet-clair: #ba68c8;
            --text-color: #333;
            --bg-color: #f9f9f9;
        }

        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: var(--bg-color);
            margin: 0;
            padding: 0;
        }

        header {
            background-color: var(--violet-fonce);
            color: white;
            padding: 1rem;
            text-align: center;
        }

        .container {
            max-width: 2000px;
            margin: 1rem auto;
            padding: 2rem;
            background: white;
            border-radius: 20px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        h2 {
            color: var(--violet-fonce);
            text-align: center;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }

        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ccc;
        }

        th {
            background-color: var(--violet-clair);
            color: white;
        }

        tr:hover {
            background-color: #f1e4f3;
        }

        @media screen and (max-width: 768px) {
            table, thead, tbody, th, td, tr {
                display: block;
            }

            thead tr {
                display: none;
            }

            td {
                position: relative;
                padding-left: 100%;
            }

            td::before {
                position: absolute;
                top: 12px;
                left: 12px;
                width: 45%;
                font-weight: bold;
                white-space: nowrap;
            }
            td:nth-of-type(1)::before { content: "Id"; }
            td:nth-of-type(2)::before { content: "sexe"; }
            td:nth-of-type(3)::before { content: "Nom"; }
            td:nth-of-type(4)::before { content: "Prénom"; }
            td:nth-of-type(5)::before { content: " Naissance"; }
            td:nth-of-type(6)::before { content: "Lieu Naissance"; }
            td:nth-of-type(7)::before { content: "Téléphone"; }
            td:nth-of-type(8)::before { content: "Email"; }
            td:nth-of-type(9)::before { content: "Mot de passe"; }
            td:nth-of-type(10)::before { content: "Photo"; }
            td:nth-of-type(11)::before { content: "date_inscription"; }
        }
    </style>
</head>
<body>

<header>
LISTE DES STAGIAIRES
</header>

<div class="container">
    <h2>Liste des stagiaires</h2>
    <table>
        <thead>
            <tr>
                <th>Id</th>
                <th>sexe</th>
                <th>nom</th>
                <th>Prénom</th>
                <th> Naissance</th>
                <th>Lieu Naissance</th>
                <th>Téléphone</th>
                <th>Email</th>
                <th>Mot de passe</th>
                <th>Photo</th>
                <th>Date d'inscription</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($stagiaires as $stagiaire): ?>
                <tr>
                    <td><?= htmlspecialchars($stagiaire['id']) ?></td>
                    <td><?= htmlspecialchars($stagiaire['sexe']) ?></td>
                    <td><?= htmlspecialchars($stagiaire['nom']) ?></td>
                    <td><?= htmlspecialchars($stagiaire['prenom']) ?></td>
                    <td><?= htmlspecialchars($stagiaire['naissance']) ?></td>
                    <td><?= htmlspecialchars($stagiaire['lieu_naissance']) ?></td>
                    <td><?= htmlspecialchars($stagiaire['telephone']) ?></td>
                    <td><?= htmlspecialchars($stagiaire['email']) ?></td>
                    <td><?= htmlspecialchars($stagiaire['mot_de_passe']) ?></td>
                    <td><?= htmlspecialchars($stagiaire['photo']) ?></td>
                    <td><?= htmlspecialchars($stagiaire['date_inscription']) ?></td>
                    
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

</body>
</html>