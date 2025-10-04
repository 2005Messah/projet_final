<?php
try {
    $pdo = new PDO("mysql:host=localhost;dbname=suivi_stagiaires", "root", "");
} catch (PDOException $e) {
    die("Erreur : " . $e->getMessage());
}

$search = "";
if (!empty($_GET['search'])) {
    $search = $_GET['search'];
    $stmt = $pdo->prepare("SELECT * FROM stagiaires WHERE nom LIKE ? OR prenom LIKE ?");
    $stmt->execute(["%$search%", "%$search%"]);
} else {
    $stmt = $pdo->query("SELECT * FROM stagiaires");
}
$stagiaires = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Liste des stagiaires</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
:root {
    --violet-fonce: #6a0dad;
    --violet-clair: #e1bee7;
    --blanc: #fff;
    --gris: #f9f9f9;
}
body { font-family: Arial, sans-serif; background: var(--gris); margin: 0; padding: 20px; }
h2 { color: var(--violet-fonce); text-align: center; }
.search-box { text-align: center; margin-bottom: 20px; }
input[type="text"] { padding: 10px; width: 60%; border: 1px solid #ccc; border-radius: 6px; }
button { padding: 10px 15px; background: var(--violet-fonce); color: white; border: none; border-radius: 6px; cursor: pointer; }
table { width: 100%; border-collapse: collapse; background: var(--blanc); box-shadow: 0 0 10px #ccc; }
th, td { padding: 12px; border-bottom: 1px solid #ddd; text-align: center; }
th { background: var(--violet-fonce); color: white; }
tr:nth-child(even) { background: var(--violet-clair); }
img { width: 60px; height: 60px; border-radius: 50%; }
.actions a { padding: 6px 10px; background: var(--violet-fonce); color: white; border-radius: 5px; margin: 0 5px; text-decoration: none; }
@media (max-width: 768px) {
    table, thead, tbody, th, td, tr { display: block; }
    th { display: none; }
    td { margin-bottom: 10px; border: none; padding: 10px; }
}
</style>
</head>
<body>

<h2>Liste des stagiaires</h2>

<div class="search-box">
    <form method="GET">
        <input type="text" name="search" placeholder="Rechercher par nom ou prénom..." value="<?= htmlspecialchars($search) ?>">
        <button type="submit">Rechercher</button>
    </form>
</div>

<?php if (count($stagiaires) > 0): ?>
<table>
    <thead>
        <tr>
            <th>Photo</th>
            <th>ID</th>
            <th>Sexe</th>
            <th>Nom</th>
            <th>Prénom</th>
            <th>Téléphone</th>
            <th>Email</th>
            <th>Lieu</th>
            <th>Filière</th>
            <th>Actions</th>
            <th>Profil</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($stagiaires as $stagiaire): ?>
        <tr>
            <td>
                <?php if (!empty($stagiaire['photo'])): ?>
                    <img src="<?= htmlspecialchars($stagiaire['photo']) ?>" alt="Photo">
                <?php else: ?>
                    <img src="default-avatar.png" alt="Photo par défaut" style="width:60px;height:60px;border-radius:50%;">
                <?php endif; ?>
            </td>
            <td><?= htmlspecialchars($stagiaire['id']) ?></td>
            <td><?= htmlspecialchars($stagiaire['sexe']) ?></td>
            <td><?= htmlspecialchars($stagiaire['nom']) ?></td>
            <td><?= htmlspecialchars($stagiaire['prenom']) ?></td>
            <td><?= htmlspecialchars($stagiaire['telephone']) ?></td>
            <td><?= htmlspecialchars($stagiaire['email']) ?></td>
            <td><?= htmlspecialchars($stagiaire['lieu']) ?></td>
            <td><?= htmlspecialchars($stagiaire['filiere']) ?></td>
            <td class="actions">
                <a href="modifier_stagiaire.php?id=<?= $stagiaire['id'] ?>">Modifier</a>
                <a href="supprimer_stagiaire.php?id=<?= $stagiaire['id'] ?>" onclick="return confirm('Confirmer la suppression ?')">Supprimer</a>
                <a href="ajouter_stage.php?id=<?= $stagiaire['id'] ?>">Attribuer un stage</a>
            </td>
            <td class="profil">
                <a href="profil.php?id=<?= $stagiaire['id'] ?>">Voir le profil</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>


<?php else: ?>
<p style="text-align:center; color:#6a0dad; font-weight:bold;">Aucun stagiaire validé pour l'instant</p>
<?php endif; ?>

</body>
</html>