```php
<?php
// choisir_stagiaire_attestation.php
session_start();

// Vérifier que l'utilisateur est un encadrant
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'encadrant') {
    header("Location: login.php");
    exit;
}

// Connexion DB
$conn = new mysqli("localhost", "root", "", "suivi_stagiaires");
if ($conn->connect_error) die("Erreur DB: " . $conn->connect_error);

// Récupérer les stagiaires validés
$res = $conn->query("SELECT id, nom, prenom, filiere FROM stagiaires ORDER BY nom ASC");
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Choisir un stagiaire - Génération Attestation</title>
    <style>
        body{font-family:Arial;background:#f4f4f4;margin:0;padding:0;}
        .container{max-width:600px;margin:50px auto;background:#fff;padding:20px;
                   border-radius:10px;box-shadow:0 2px 6px rgba(0,0,0,0.2);}
        h2{text-align:center;color:#6a1b9a;}
        label{font-weight:bold;}
        select{width:100%;padding:10px;margin:15px 0;border:1px solid #ccc;
               border-radius:5px;}
        button{background:#6a1b9a;color:#fff;padding:10px 20px;border:none;
               border-radius:5px;cursor:pointer;font-weight:bold;}
        button:hover{background:#9c27b0;}
        .back{display:block;margin-top:20px;text-align:center;}
        .back a{color:#6a1b9a;text-decoration:none;font-weight:bold;}
    </style>
</head>
<body>

<div class="container">
    <h2>📄 Génération d'attestation</h2>
    <form method="get" action="generer_attestation.php">
        <label for="id">Sélectionnez un stagiaire :</label>
        <select name="id" id="id" required>
            <option value="">-- Choisir --</option>
            <?php while($row = $res->fetch_assoc()){ ?>
                <option value="<?php echo $row['id']; ?>">
                    <?php echo strtoupper($row['nom'])." ".$row['prenom']." (".$row['filiere'].")"; ?>
                </option>
            <?php } ?>
        </select>
        <div style="text-align:center;">
            <button type="submit">Générer l'attestation</button>
        </div>
    </form>
    <div class="back">
        <a href="dashboard_encadrant.php">⬅ Retour au Dashboard</a>
    </div>
</div>

</body>
</html>
<?php $conn->close(); ?>