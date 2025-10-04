<?php
session_start();

// Connexion PDO
try {
    $pdo = new PDO("mysql:host=localhost;dbname=suivi_stagiaires;charset=utf8", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e){
    die("Erreur de connexion : " . $e->getMessage());
}

// Récupérer le mois et l'année depuis GET ou utiliser le mois courant
$mois = isset($_GET['mois']) ? intval($_GET['mois']) : intval(date('m'));
$annee = isset($_GET['annee']) ? intval($_GET['annee']) : intval(date('Y'));
$nbJours = cal_days_in_month(CAL_GREGORIAN, $mois, $annee);

// Tous les stagiaires
$stmt = $pdo->query("SELECT id, nom, prenom FROM stagiaires ORDER BY nom");
$stagiaires = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les présences pour le mois
$sql = "SELECT stagiaire_id, DATE(date) AS jour, present 
        FROM presences 
        WHERE MONTH(date) = :mois AND YEAR(date) = :annee";
$res = $pdo->prepare($sql);
$res->execute(['mois' => $mois, 'annee' => $annee]);

$presences = [];
while($row = $res->fetch(PDO::FETCH_ASSOC)){
    $presences[$row['stagiaire_id']][$row['jour']] = $row['present'];
}

// Pré-remplissage absences
$presencesComplet = [];
foreach($stagiaires as $s){
    for($d=1; $d<=$nbJours; $d++){
        $day = $annee . '-' . str_pad($mois,2,'0',STR_PAD_LEFT) . '-' . str_pad($d,2,'0',STR_PAD_LEFT);
        $presencesComplet[$s['id']][$day] = isset($presences[$s['id']][$day]) ? $presences[$s['id']][$day] : 0;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Récapitulatif des présences</title>
    <style>
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ddd; padding: 5px; text-align: center; font-size: 12px; }
        th { background-color: #6a0dad; color: white; }
        .nom { background-color: #d1b3ff; text-align: left; font-weight: bold; }
        .present { background-color: #c6efce; font-weight: bold; }
        .absent { background-color: #f2e6ff; font-weight: bold; }
        tr:nth-child(even) td.nom { background-color: #ffffff; }
    </style>
</head>
<body>
    <h2>Récapitulatif des présences - <?php echo $mois.'/'.$annee; ?></h2>
    <p><a href="export_presences_pdf.php?mois=<?php echo $mois; ?>&annee=<?php echo $annee; ?>">📄 Exporter en PDF</a></p>

    <table>
        <tr>
            <th>Stagiaire</th>
            <?php for($j=1;$j<=$nbJours;$j++): ?>
                <th><?php echo $j; ?></th>
            <?php endfor; ?>
        </tr>
        <?php foreach($stagiaires as $s): ?>
            <tr>
                <td class="nom"><?php echo htmlspecialchars($s['nom'].' '.$s['prenom'], ENT_QUOTES, 'UTF-8'); ?></td>
                <?php for($d=1;$d<=$nbJours;$d++):
                    $date = $annee.'-'.str_pad($mois,2,'0',STR_PAD_LEFT).'-'.str_pad($d,2,'0',STR_PAD_LEFT);
                    $val = $presencesComplet[$s['id']][$date];
                    $class = $val ? 'present' : 'absent';
                    $txt = $val ? 'P' : 'A';
                ?>
                    <td class="<?php echo $class; ?>"><?php echo $txt; ?></td>
                <?php endfor; ?>
            </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>
