<?php
$pdo = new PDO("mysql:host=localhost;dbname=suivi_stagiaires", "root", "");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Recherche
$condition = "";
$params = [];

if (!empty($_GET['theme'])) {
    $condition .= " AND theme LIKE ?";
    $params[] = "%" . $_GET['theme'] . "%";
}

if (!empty($_GET['date'])) {
    $condition .= " AND date_debut <= ? AND date_fin >= ?";
    $params[] = $_GET['date'];
    $params[] = $_GET['date'];
}

if (!empty($_GET['id_stagiaire'])) {
    $condition .= " AND id_stagiaire = ?";
    $params[] = $_GET['id_stagiaire'];
}

$sql = "SELECT * FROM stages WHERE 1 $condition ORDER BY id_stages ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$stages = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Liste des Stages</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f5ebff;
            color: #333;
            padding: 20px;
        }
        h2 {
            color: #5e3a87;
        }
        form {
            background: #fff;
            padding: 15px;
            border-radius: 8px;
            max-width: 700px;
            margin-bottom: 20px;
        }
        input[type="text"], input[type="date"] {
            padding: 8px;
            margin: 5px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        button {
            background-color: #6a4ca4;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
        }
        button:hover {
            background-color: #563a92;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background: #fff;
        }
        th, td {
            padding: 12px;
            border-bottom: 1px solid #ccc;
            text-align: center;
        }
        th {
            background-color: #cbb5e9;
            color: #333;
        }
        a.action {
            padding: 6px 10px;
            margin: 2px;
            color: white;
            border-radius: 4px;
            text-decoration: none;
        }
        .edit {
            background-color:#5e3a87;
        }
        .delete {
            background-color: #cbb5e9;
        }
    </style>
</head>
<body>

<h2>Recherche de Stages</h2>

<form method="GET">
    <input type="text" name="theme" placeholder="Thème du stage" value="<?php echo htmlspecialchars(isset($_GET['theme']) ? $_GET['theme'] : ''); ?>">
    <input type="date" name="date" value="<?php echo htmlspecialchars(isset($_GET['date']) ? $_GET['date'] : ''); ?>">
    <input type="text" name="id_stagiaire" placeholder="ID stagiaire" value="<?php echo htmlspecialchars(isset($_GET['id_stagiaire']) ? $_GET['id_stagiaire'] : ''); ?>">
    <button type="submit">Rechercher</button>
</form>

<h2>Liste des Stages</h2>

<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>ID stagiaire</th>
            <th>Date Début</th>
            <th>Date Fin</th>
            <th>Établissement</th>
            <th>Thème</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php if (count($stages) > 0): ?>
            <?php foreach ($stages as $stage): ?>
                <tr>
                    <td><?php echo $stage['id_stages']; ?></td>
                    <td><?php echo htmlspecialchars($stage['id_stagiaire']); ?></td>
                    <td><?php echo $stage['date_debut']; ?></td>
                    <td><?php echo $stage['date_fin']; ?></td>
                    <td><?php echo htmlspecialchars($stage['etablissement']); ?></td>
                    <td><?php echo htmlspecialchars($stage['theme']); ?></td>
                    <td>
                        <a class="action edit" href="modifier_stage.php?id=<?php echo $stage['id_stages']; ?>">Modifier</a>
                        <a class="action delete" href="supprimer_stage.php?id=<?php echo $stage['id_stages']; ?>" onclick="return confirm('Confirmer la suppression ?')">Supprimer</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr><td colspan="7">Aucun stage trouvé.</td></tr>
        <?php endif; ?>
    </tbody>
</table>

</body>
</html>
