<?php
session_start();

// Vérifier que l'utilisateur est un encadrant
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'encadrant') {
    header("Location: login.php");
    exit;
}

$conn = new mysqli("localhost", "root", "", "suivi_stagiaires");
if ($conn->connect_error) die("Erreur DB: " . $conn->connect_error);

// Récupérer tous les stagiaires validés
$result = $conn->query("SELECT id, sexe, nom, prenom, telephone, email, photo, date_inscription 
                        FROM stagiaires 
                        ORDER BY date_inscription DESC");
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Liste des stagiaires validés</title>
<style>
body{font-family:Arial;margin:0;padding:0;background:#f4f4f4;}
header{background:#6a1b9a;color:#fff;padding:15px;text-align:center;}
.container{width:95%;margin:20px auto;}
table{width:100%;border-collapse:collapse;}
th,td{border:1px solid #ccc;padding:8px;text-align:left;}
th{background:#9c27b0;color:#fff;}
img{max-width:80px;border-radius:8px;}
</style>
</head>
<body>

<header>
<h1>Liste des stagiaires validés</h1>
<p><a href="dashboard_encadrant.php" style="color:white;">← Retour au dashboard</a></p>
</header>

<div class="container">
<table>
<tr>
<th>Photo</th><th>Nom</th><th>Prénom</th><th>Email</th><th>Sexe</th><th>Téléphone</th><th>Date inscription</th>
</tr>

<?php
if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>";
        if(!empty($row['photo'])) {
            echo "<img src='uploads/".$row['photo']."'>";
        }
        echo "</td>";
        echo "<td>".htmlspecialchars($row['nom'])."</td>";
        echo "<td>".htmlspecialchars($row['prenom'])."</td>";
        echo "<td>".htmlspecialchars($row['email'])."</td>";
        echo "<td>".htmlspecialchars($row['sexe'])."</td>";
        echo "<td>".htmlspecialchars($row['telephone'])."</td>";
        echo "<td>".$row['date_inscription']."</td>";
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='7' style='text-align:center;'>Aucun stagiaire validé</td></tr>";
}
?>
</table>
</div>

</body>
</html>

<?php $conn->close(); ?>
