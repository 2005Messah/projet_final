<?php
// Connexion à la base de données
try {
    $pdo = new PDO("mysql:host=localhost;dbname=suivi_stagiaires", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

// Récupérer l'ID du stagiaire depuis l'URL (par défaut = 1 si non fourni)
$stagiaire_id = isset($_GET['id']) ? (int)$_GET['id'] : 1;

// --- Suppression d'un stage si paramètre delete présent ---
if (isset($_GET['delete'])) {
    $id_delete = (int) $_GET['delete'];

    // Supprimer les fichiers liés si nécessaire
    $stmt = $pdo->prepare("SELECT rapport, convention FROM stages WHERE id_stages = ?");
    $stmt->execute([$id_delete]);
    $files = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($files) {
        if ($files['rapport'] && file_exists($files['rapport'])) unlink($files['rapport']);
        if ($files['convention'] && file_exists($files['convention'])) unlink($files['convention']);
    }

    // Supprimer en BDD
    $del = $pdo->prepare("DELETE FROM stages WHERE id_stages = ?");
    $del->execute([$id_delete]);

    echo "<script>alert('Stage supprimé avec succès !'); window.location='profil.php?id=$stagiaire_id';</script>";
    exit;
}

// Récupérer les informations du stagiaire
$stmt = $pdo->prepare("SELECT * FROM stagiaires WHERE id = ?");
$stmt->execute([$stagiaire_id]);
$stagiaire = $stmt->fetch(PDO::FETCH_ASSOC);

// Si le stagiaire n'existe pas
if (!$stagiaire) {
    die("Stagiaire introuvable !");
}

// Récupérer les stages associés à ce stagiaire
$stmt = $pdo->prepare("SELECT * FROM stages WHERE id_stagiaire = ?");
$stmt->execute([$stagiaire_id]);
$stages = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Stagiaire</title>
    <style>
        :root {
            --violet-clair: #d1c4e9;
            --violet-fonce: #673ab7;
            --blanc: #fff;
        }
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 1000px;
            margin: auto;
            background-color: var(--blanc);
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h2 {
            color: var(--violet-fonce);
            margin-bottom: 20px;
        }
        .profile {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }
        .profile img {
            width: 150px;
            height: 150px;
            object-fit: cover;
            border-radius: 8px;
            border: 3px solid var(--violet-fonce);
        }
        .info-table, .stage-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .info-table td {
            padding: 10px;
            border: 1px solid #ddd;
        }
        .stage-table th, .stage-table td {
            padding: 12px;
            border: 1px solid #ccc;
            text-align: left;
        }
        .stage-table th {
            background-color: var(--violet-clair);
            color: #333;
        }
        .actions a {
            margin-right: 10px;
            text-decoration: none;
            color: var(--violet-fonce);
        }
        .actions a.delete {
            color: #f44336;
        }
        .actions a.delete:hover {
            text-decoration: underline;
        }
        @media (max-width: 600px) {
            .profile {
                flex-direction: column;
                align-items: center;
            }
        }
        .btn-download {
            display:inline-block;
            background-color: var(--violet-fonce);
            color: white;
            padding: 10px 20px;
            margin-top: 20px;
            text-decoration: none;
            border-radius: 6px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Informations personnelles</h2>
        <div class="profile">
            <img src="<?php echo htmlspecialchars($stagiaire['photo']); ?>" alt="Photo">
            <table class="info-table">
                <tr><td><strong>ID :</strong></td><td><?php echo htmlspecialchars($stagiaire['id']); ?></td></tr>
                <tr><td><strong>Sexe :</strong></td><td><?php echo htmlspecialchars($stagiaire['sexe']); ?></td></tr>
                <tr><td><strong>Nom :</strong></td><td><?php echo htmlspecialchars($stagiaire['nom']); ?></td></tr>
                <tr><td><strong>Prénom :</strong></td><td><?php echo htmlspecialchars($stagiaire['prenom']); ?></td></tr>
                <tr><td><strong>Téléphone :</strong></td><td><?php echo htmlspecialchars($stagiaire['telephone']); ?></td></tr>
                <tr><td><strong>Email :</strong></td><td><?php echo htmlspecialchars($stagiaire['email']); ?></td></tr>
                <tr><td><strong>Filière :</strong></td><td><?php echo htmlspecialchars($stagiaire['filiere']); ?></td></tr>
            </table>
        </div>

        <a href="formulaire_carte_stagiaire.php?id=<?= $stagiaire_id ?>" class="btn-download">
            📄 Télécharger la carte du stagiaire
        </a>

        <h2>Stages</h2>
        <table class="stage-table">
            <thead>
                <tr>
                    <th>Action</th>
                    <th>Date début</th>
                    <th>Date fin</th>
                    <th>Etablissement</th>
                    <th>Thème</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($stages)): ?>
                    <?php foreach ($stages as $stage): ?>
                        <tr>
                            <td class="actions">
                                <a href="modifier_stage.php?id=<?php echo $stage['id_stages']; ?>">Modifier</a>
                                <a href="profil.php?id=<?= $stagiaire_id ?>&delete=<?= $stage['id_stages']; ?>" class="delete" onclick="return confirm('Voulez-vous vraiment supprimer ce stage ?');">Supprimer</a>
                            </td>
                            <td><?php echo htmlspecialchars($stage['date_debut']); ?></td>
                            <td><?php echo htmlspecialchars($stage['date_fin']); ?></td>
                            <td><?php echo htmlspecialchars($stage['etablissement']); ?></td>
                            <td><?php echo htmlspecialchars($stage['theme']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="5">Aucun stage trouvé pour ce stagiaire.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>