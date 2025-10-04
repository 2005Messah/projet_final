<?php
require_once('tcpdf/tcpdf.php');

try {
    $pdo = new PDO("mysql:host=localhost;dbname=suivi_stagiaires;charset=utf8", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Mois et année
    $mois = isset($_GET['mois']) ? intval($_GET['mois']) : intval(date('m'));
    $annee = isset($_GET['annee']) ? intval($_GET['annee']) : intval(date('Y'));
    $nbJours = cal_days_in_month(CAL_GREGORIAN, $mois, $annee);

    // Tous les stagiaires
    $stmt = $pdo->query("SELECT id, nom, prenom FROM stagiaires ORDER BY nom");
    $stagiaires = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Récupérer les présences
    $sql = "SELECT stagiaire_id, DATE(date) AS jour, present 
            FROM presences 
            WHERE date != '0000-00-00' 
              AND MONTH(date) = :mois 
              AND YEAR(date) = :annee";
    $res = $pdo->prepare($sql);
    $res->execute(['mois' => $mois, 'annee' => $annee]);

    $presences = array();
    while ($row = $res->fetch(PDO::FETCH_ASSOC)) {
        $presences[$row['stagiaire_id']][$row['jour']] = $row['present'];
    }

    // Pré-remplissage absences
    $presencesComplet = array();
    foreach ($stagiaires as $s) {
        for ($d = 1; $d <= $nbJours; $d++) {
            $day = $annee . '-' . str_pad($mois,2,'0',STR_PAD_LEFT) . '-' . str_pad($d,2,'0',STR_PAD_LEFT);
            $presencesComplet[$s['id']][$day] = 0;
        }
        if (isset($presences[$s['id']])) {
            foreach ($presences[$s['id']] as $date => $val) {
                $presencesComplet[$s['id']][$date] = $val;
            }
        }
    }

} catch (PDOException $e) {
    die("Erreur : ".$e->getMessage());
}

// Création du PDF
$pdf = new TCPDF('L', 'mm', 'A4', true, 'UTF-8', false);
$pdf->SetCreator('Suivi Stagiaires');
$pdf->SetAuthor('Encadrant');
$pdf->SetTitle('Récapitulatif Présences');
$pdf->SetMargins(5,5,5);
$pdf->SetAutoPageBreak(TRUE,5);
$pdf->AddPage();
$pdf->SetFont('helvetica','',8);

// Couleurs
$violetFonce = '#6a0dad';
$violetClair = '#d1b3ff';
$vert = '#c6efce';
$blanc = '#ffffff';

// Début HTML
$html = '<h2 style="text-align:center;color:'.$violetFonce.';">Récapitulatif des présences - '.$mois.'/'.$annee.'</h2>';
$html .= '<table border="1" cellpadding="3" cellspacing="0" style="border-collapse:collapse;">';

// Entêtes
$html .= '<tr style="background-color:'.$violetFonce.';color:white;font-weight:bold;text-align:center;font-size:8pt;">';
$html .= '<th style="width:45mm;">Stagiaire</th>';
for($j=1;$j<=$nbJours;$j++){
    $html .= '<th style="width:8mm;">'.$j.'</th>';
}
$html .= '</tr>';

// Contenu avec alternance de couleurs pour chaque ligne
$ligne = 0;
foreach($stagiaires as $s){
    $bg = ($ligne % 2 == 0) ? $violetClair : $blanc;
    $html .= '<tr>';
    $html .= '<td style="background-color:'.$bg.';font-size:8pt;font-weight:bold;white-space:nowrap;">'
           .htmlspecialchars($s['nom'].' '.$s['prenom'], ENT_QUOTES, 'UTF-8').'</td>';
    for($d=1;$d<=$nbJours;$d++){
        $date = $annee.'-'.str_pad($mois,2,'0',STR_PAD_LEFT).'-'.str_pad($d,2,'0',STR_PAD_LEFT);
        $val = $presencesComplet[$s['id']][$date];
        $txt = $val ? 'P' : 'A';
        $color = $val ? $vert : $bg; // Absences reprennent le fond de la ligne
        $html .= '<td style="background-color:'.$color.';text-align:center;font-weight:bold;font-size:7pt;">'.$txt.'</td>';
    }
    $html .= '</tr>';
    $ligne++;
}

$html .= '</table>';

// Générer le PDF
$pdf->writeHTML($html,true,false,true,false,'');
$pdf->Output('recap_presences_'.$mois.'_'.$annee.'.pdf','I');
