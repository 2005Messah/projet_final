<?php
// --- Connexion à la base ---
try {
    $pdo = new PDO("mysql:host=localhost;dbname=suivi_stagiaires", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

// --- Récupérer la liste des stagiaires pour le select ---
$stagiaires = $pdo->query("SELECT id, nom, prenom FROM stagiaires");
$S = $stagiaires->fetchAll(PDO::FETCH_ASSOC);

// --- Vérifier l'ID du stage à modifier ---
if (!isset($_GET['id'])) {
    die("Aucun stage sélectionné.");
}

$id_stage = (int) $_GET['id'];

// --- Récupérer les infos du stage ---
$stmt = $pdo->prepare("SELECT * FROM stages WHERE id_stages = ?");
$stmt->execute([$id_stage]);
$stage = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$stage) {
    die("Stage introuvable !");
}

// --- Traitement du formulaire ---
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $stagiaire_id = $_POST['stagiaire_id'];
    $date_debut   = $_POST['date_debut'];
    $date_fin     = $_POST['date_fin'];
    $etablissement = $_POST['etablissement'];
    $theme        = $_POST['theme'];

  

    // --- Mise à jour en BDD ---
    $update = $pdo->prepare("UPDATE stages 
        SET id_stagiaire=?, date_debut=?, date_fin=?, rapport=?, etablissement=?, theme=?, convention=? 
        WHERE id_stages=?");

    $update->execute([
        $stagiaire_id, $date_debut, $date_fin, $rapport, $etablissement, $theme, $convention, $id_stage
    ]);

    echo "<script>alert('Stage modifié avec succès !'); window.location='profil.php';</script>";
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier Stage</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        :root {
            --violet-fonce: #6a0dad;
            --violet-clair: #d1c4e9;
            --blanc: #fff;
            --gris: #f9f9f9;
        }
        body { font-family: Arial, sans-serif; background: var(--gris); margin: 0; padding: 0; }
        .container { max-width: 700px; margin: 40px auto; background: var(--blanc);
            border-radius: 10px; padding: 25px; box-shadow: 0 0 15px rgba(0,0,0,0.1); }
        h2 { background: var(--violet-fonce); color: var(--blanc); text-align: center;
            padding: 15px; border-radius: 10px 10px 0 0; }
        form { display: flex; flex-wrap: wrap; gap: 20px; }
        .form-group { flex: 1 1 45%; display: flex; flex-direction: column; }
        .form-group.full { flex: 1 1 100%; }
        label { font-weight: bold; color: var(--violet-fonce); }
        input, select { padding: 10px; border: 1px solid #ccc; border-radius: 6px; }
        .btn { margin-top: 20px; padding: 12px; background: var(--violet-fonce); color: var(--blanc);
            border: none; border-radius: 6px; cursor: pointer; font-size: 16px; }
        .btn:hover { background: #512da8; }
        .current-file { font-size: 13px; color: #444; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Modifier le Stage</h2>
        <form method="POST" enctype="multipart/form-data">
            <div class="form-group full">
                <label for="stagiaire_id">Stagiaire :</label>
                <select name="stagiaire_id" required>
                    <option value="">-- Choisir un stagiaire --</option>
                    <?php foreach ($S as $s): ?>
                        <option value="<?= $s['id'] ?>" <?= ($s['id']==$stage['id_stagiaire']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($s['prenom'] . " " . $s['nom']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Date début :</label>
                <input type="date" name="date_debut" value="<?= $stage['date_debut'] ?>" required>
            </div>

            <div class="form-group">
                <label>Date fin :</label>
                <input type="date" name="date_fin" value="<?= $stage['date_fin'] ?>" required>
            </div>

            <div class="form-group">
                <label>Établissement :</label>
                <input type="text" name="etablissement" value="<?= htmlspecialchars($stage['etablissement']) ?>" required>
            </div>

            <div class="form-group full">
                <label>Thème :</label>
                <input type="text" name="theme" value="<?= htmlspecialchars($stage['theme']) ?>" required>
            </div>
            <div class="form-group full">
                <button class="btn" type="submit">Enregistrer les modifications</button>
            </div>
        </form>
    </div>
</body>
</html>
