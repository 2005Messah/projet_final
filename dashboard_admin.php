<?php
session_start();




$conn = new mysqli("localhost","root","","suivi_stagiaires");
if($conn->connect_error) die("Erreur DB: ".$conn->connect_error);

// Nom utilisateur
$username = isset($_SESSION['username']) ? $_SESSION['username'] : "Administrateur";

// --- Nombre total d'utilisateurs ---
$res_users = $conn->query("SELECT COUNT(*) AS total_users FROM utilisateurs");
$total_users = ($res_users)? $res_users->fetch_assoc()['total_users'] : 0;

// --- Taux de présence global ---
$total_presence = 0; $total_jours = 0;
$res_stagiaires = $conn->query("SELECT id, nom, prenom FROM stagiaires");
$presences_data = array();
if($res_stagiaires){
    while($s = $res_stagiaires->fetch_assoc()){
        $sid = $s['id'];
        $res_pres = $conn->query("SELECT COUNT(*) AS present_count FROM presences WHERE stagiaire_id=$sid AND present=1");
        $res_days = $conn->query("SELECT COUNT(*) AS days_count FROM presences WHERE stagiaire_id=$sid");
        $p = ($res_pres)? $res_pres->fetch_assoc()['present_count'] : 0;
        $d = ($res_days)? $res_days->fetch_assoc()['days_count'] : 0;
        $total_presence += $p; $total_jours += $d;
        $rate = ($d>0)? round(($p/$d)*100,2) : 0;
        $presences_data[] = array('nom'=>$s['nom'].' '.$s['prenom'],'rate'=>$rate);
    }
}
$attendance_rate = ($total_jours>0)? round(($total_presence/$total_jours)*100,2) : 0;

// --- Statistiques demandes de stage ---
$demandes = $conn->query("SELECT * FROM demandes_stage");
$total_demandes = 0; $validées=0; $refusées=0; $attente=0;
if($demandes && $demandes->num_rows>0){
    $total_demandes = $demandes->num_rows;
    while($d = $demandes->fetch_assoc()){
        $etat = isset($d['etat']) ? $d['etat'] : 'En attente';
        if($etat=='Validée') $validées++;
        elseif($etat=='Refusée') $refusées++;
        else $attente++;
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Dashboard Administrateur</title>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
body{font-family:Arial;margin:0;padding:0;background:#f4f4f4;}
header{background:#6a1b9a;color:#fff;padding:15px;text-align:center;}
.container{width:95%;margin:20px auto;}
.card{background:#fff;padding:20px;border-radius:10px;margin-bottom:20px;box-shadow:0 2px 6px rgba(0,0,0,0.1);}
.card h3{margin-top:0;}
a{text-decoration:none;color:#6a1b9a;font-weight:bold;}
.btn{background: #6a1b9a;color: #fff;padding: 8px 12px;border-radius: 5px;text-decoration: none;font-weight: bold;}
.btn:hover{background: #9c27b0;}
</style>
</head>
<body>

<header>
<h1>Dashboard Administrateur</h1>
<p>Bienvenue, <?php echo htmlspecialchars($username); ?> | <a href="logout.php" style="color:white;">Déconnexion</a></p>
</header>

<div class="container">

    <!-- Total utilisateurs -->
    <div class="card">
        <h3>Total d'utilisateurs : <?php echo $total_users; ?></h3>
        <p><a href="liste_utilisateurs.php" class="btn">Voir la liste complète</a></p>
    </div>

    <!-- Taux de présence -->
    <div class="card">
        <h3>Taux de présence global : <?php echo $attendance_rate; ?>%</h3>
        <canvas id="attendanceChart" style="width:100%;height:300px;"></canvas>
        <p><a href="recap_presences.php?mois=<?php echo date('m'); ?>&annee=<?php echo date('Y'); ?>" class="btn">Voir le récapitulatif</a></p>
    </div>

    <!-- Graphique demandes de stage -->
    <div class="card">
        <h3>📊 Statistiques des demandes de stage</h3>
        <canvas id="demandesChart" style="width:100%;height:300px;"></canvas>
        <p>Total des demandes : <?php echo $total_demandes; ?></p>
        <p><a href="dashboard_encadrant.php" class="btn">Consulter les demandes reçues</a></p>
    </div>

</div>

<script>
// Graphique présence par stagiaire
var ctx1 = document.getElementById('attendanceChart').getContext('2d');
var attendanceChart = new Chart(ctx1,{
    type:'bar',
    data:{
        labels: <?php echo json_encode(array_column($presences_data,'nom')); ?>,
        datasets:[{
            label:'Taux de présence (%)',
            data: <?php echo json_encode(array_column($presences_data,'rate')); ?>,
            backgroundColor:'rgba(106,27,154,0.6)'
        }]
    },
    options:{scales:{y:{beginAtZero:true,max:100}}}
});

// Graphique demandes de stage
var ctx2 = document.getElementById('demandesChart').getContext('2d');
var demandesChart = new Chart(ctx2,{
    type:'pie',
    data:{
        labels: ['Validées','Refusées','En attente'],
        datasets:[{
            data: [<?php echo $validées; ?>, <?php echo $refusées; ?>, <?php echo $attente; ?>],
            backgroundColor:['#28a745','#dc3545','#6c757d']
        }]
    },
    options:{responsive:true}
});
</script>

</body>
</html>

<?php $conn->close(); ?>
