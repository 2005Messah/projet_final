<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'encadrant') {
    header("Location: login.php");
    exit;
}

try {
    $pdo = new PDO("mysql:host=localhost;dbname=suivi_stagiaires;charset=utf8", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Récupérer tous les stagiaires pour la liste déroulante
    $stmt = $pdo->query("SELECT id, nom, prenom FROM stagiaires ORDER BY nom");
    $stagiaires = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

// Si le formulaire est soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stagiaire_id = isset($_POST['stagiaire_id']) ? intval($_POST['stagiaire_id']) : 0;
    $date = isset($_POST['date']) ? $_POST['date'] : date('Y-m-d');
    $present = isset($_POST['present']) ? intval($_POST['present']) : 0;
    $statut = $present ? 'Présent' : 'Absent';

    if ($stagiaire_id > 0) {
        try {
            // Vérifier si une présence existe déjà pour ce stagiaire et cette date
            $sqlCheck = "SELECT COUNT(*) FROM presences WHERE stagiaire_id = :stagiaire_id AND date = :date";
            $stmtCheck = $pdo->prepare($sqlCheck);
            $stmtCheck->execute([
                ':stagiaire_id' => $stagiaire_id,
                ':date' => $date
            ]);
            $exists = $stmtCheck->fetchColumn();

            if ($exists) {
                // Mettre à jour la présence et le statut
                $sqlUpdate = "UPDATE presences SET present = :present, statut = :statut WHERE stagiaire_id = :stagiaire_id AND date = :date";
                $stmtUpdate = $pdo->prepare($sqlUpdate);
                $stmtUpdate->execute([
                    ':present' => $present,
                    ':statut' => $statut,
                    ':stagiaire_id' => $stagiaire_id,
                    ':date' => $date
                ]);
                $message = "Présence mise à jour avec succès !";
            } else {
                // Insérer nouvelle présence avec statut
                $sqlInsert = "INSERT INTO presences (stagiaire_id, date, present, statut) VALUES (:stagiaire_id, :date, :present, :statut)";
                $stmtInsert = $pdo->prepare($sqlInsert);
                $stmtInsert->execute([
                    ':stagiaire_id' => $stagiaire_id,
                    ':date' => $date,
                    ':present' => $present,
                    ':statut' => $statut
                ]);
                $message = "Présence enregistrée avec succès !";
            }
        } catch (PDOException $e) {
            $message = "Erreur lors de l'enregistrement : " . $e->getMessage();
        }
    } else {
        $message = "Veuillez sélectionner un stagiaire valide.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Pointer Présence</title>
<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    form { max-width: 400px; margin: auto; padding: 20px; border: 1px solid #ccc; border-radius: 8px; }
    label { display: block; margin-top: 10px; }
    select, input[type="date"], input[type="radio"], button { margin-top: 5px; width: 100%; padding: 8px; }
    .message { text-align: center; margin-bottom: 20px; color: green; font-weight: bold; }
</style>
</head>
<body>

<h2 style="text-align:center;">Pointer la présence d'un stagiaire</h2>

<?php if(isset($message)) echo '<div class="message">'.$message.'</div>'; ?>

<form method="POST" action="">
    <label>Stagiaire :</label>
    <select name="stagiaire_id" required>
        <option value="">-- Sélectionnez un stagiaire --</option>
        <?php foreach ($stagiaires as $s): ?>
            <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['nom'].' '.$s['prenom'], ENT_QUOTES, 'UTF-8') ?></option>
        <?php endforeach; ?>
    </select>

    <label>Date :</label>
    <input type="date" name="date" value="<?= date('Y-m-d') ?>" required>

    <label>Présence :</label>
    <label><input type="radio" name="present" value="1" checked> Présent</label>
    <label><input type="radio" name="present" value="0"> Absent</label>

    <button type="submit">Enregistrer</button>
</form>

</body>
</html>
