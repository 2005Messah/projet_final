<?php
// carte_stagiaire.php
// Génère une carte au format carte bancaire (85.6 x 54 mm) avec titre violet foncé

// --- Configuration d'environnement ---
ini_set('display_errors', 0);
error_reporting(0);
ob_start(); // démarrer buffering pour éviter toute sortie avant le PDF

// --- Inclure TCPDF ---
require_once __DIR__ . '/tcpdf/tcpdf.php';

// --- Connexion à la base de données ---
$mysqli = new mysqli('localhost', 'root', '', 'suivi_stagiaires');
if ($mysqli->connect_errno) {
    error_log('DB connect error: ' . $mysqli->connect_error);
    exit;
}

// --- Récupérer l'ID du stagiaire ---
$stagiaire_id = isset($_GET['id']) && ctype_digit($_GET['id']) ? (int) $_GET['id'] : 1;

// --- Requête préparée ---
$stmt = $mysqli->prepare('SELECT id, nom, prenom, sexe, telephone, email, photo, filiere FROM stagiaires WHERE id = ? LIMIT 1');
if (!$stmt) { exit; }
$stmt->bind_param('i', $stagiaire_id);
$stmt->execute();
$res = $stmt->get_result();
if (!$res || $res->num_rows === 0) { exit; }
$stagiaire = $res->fetch_assoc();

// --- Couleurs et format ---
$violetFonce = [106, 27, 154];  // couleur titre
$violetClair = [209, 196, 233]; // couleur fond
$bleuTexte = [40, 40, 40];
$formatCarte = [85.6, 54]; // largeur x hauteur en mm (carte bancaire)
$pad = 2; // marge interne

// --- Création du PDF ---
$pdf = new TCPDF('L', 'mm', $formatCarte, true, 'UTF-8', false);
$pdf->SetPrintHeader(false);
$pdf->SetPrintFooter(false);
$pdf->SetAutoPageBreak(false);
$pdf->SetMargins(0, 0, 0);
$pdf->AddPage();

// --- Fond ---
$pdf->SetFillColor($violetClair[0], $violetClair[1], $violetClair[2]);
$pdf->RoundedRect($pad, $pad, $formatCarte[0] - ($pad * 2), $formatCarte[1] - ($pad * 2), 3, 'F', array('all' => array('width' => 0)), array());

// Bordure subtile
$pdf->SetDrawColor(200, 200, 200);
$pdf->SetLineWidth(0.2);
$pdf->RoundedRect($pad + 0.7, $pad + 0.7, $formatCarte[0] - ($pad * 2) - 1.4, $formatCarte[1] - ($pad * 2) - 1.4, 3, 'D', array('all' => array('width' => 0.2)), array());

// --- TITRE en violet foncé ---
$pdf->SetFont('helvetica', 'B', 12);
$pdf->SetTextColor($violetFonce[0], $violetFonce[1], $violetFonce[2]);
$pdf->SetXY(0, $pad + 2);
$pdf->Cell($formatCarte[0], 6, 'CARTE DE STAGIAIRE', 0, 1, 'C', 0, '', 0);

// --- Photo (gauche) ---
$photoPath = isset($stagiaire['photo']) ? urldecode($stagiaire['photo']) : '';
if (!empty($photoPath) && !file_exists($photoPath)) {
    $tryPath = __DIR__ . '/uploads/' . basename($photoPath);
    if (file_exists($tryPath)) $photoPath = $tryPath;
}
$photoX = $pad + 4;
$photoY = $pad + 10; // juste sous le titre
$photoW = 22;
$photoH = 28;

if (!empty($photoPath) && file_exists($photoPath)) {
    $pdf->SetFillColor(255, 255, 255);
    $pdf->Rect($photoX - 0.6, $photoY - 0.6, $photoW + 1.2, $photoH + 1.2, 'F');
    $pdf->Image($photoPath, $photoX, $photoY, $photoW, $photoH, '', '', '', false, 300, '', false, false, 1);
} else {
    $pdf->SetFillColor(255, 255, 255);
    $pdf->Rect($photoX - 0.6, $photoY - 0.6, $photoW + 1.2, $photoH + 1.2, 'F');
    $pdf->SetXY($photoX, $photoY + ($photoH / 2) - 5);
    $pdf->SetFont('helvetica', 'I', 7);
    $pdf->SetTextColor(200, 0, 0);
    $pdf->MultiCell($photoW, 10, "Photo\nnon\ntrouvée", 0, 'C', 0, 1, '', '', true);
}

// --- Bloc infos à droite de la photo ---
$infosX = $photoX + $photoW + 3;
$infosY = $photoY;
$infosW = $formatCarte[0] - $infosX - ($pad + 4);

$nom = htmlspecialchars($stagiaire['nom']);
$prenom = htmlspecialchars($stagiaire['prenom']);
$sexe = htmlspecialchars($stagiaire['sexe']);
$tel = htmlspecialchars($stagiaire['telephone']);
$email = htmlspecialchars($stagiaire['email']);
$filiere = htmlspecialchars($stagiaire['filiere']);

$pdf->SetXY($infosX, $infosY);
$pdf->SetFont('helvetica', '', 7.2);
$pdf->SetTextColor($bleuTexte[0], $bleuTexte[1], $bleuTexte[2]);

$html = '<table cellpadding="2" cellspacing="0" border="0" style="font-size:7.2px; line-height:1.0;">';
$html .= '<tr><td style="font-weight:bold; width:32%;">Nom :</td><td style="width:68%;">' . $nom . '</td></tr>';
$html .= '<tr><td style="font-weight:bold;">Prénom :</td><td>' . $prenom . '</td></tr>';
$html .= '<tr><td style="font-weight:bold;">Sexe :</td><td>' . $sexe . '</td></tr>';
$html .= '<tr><td style="font-weight:bold;">Filière :</td><td>' . $filiere . '</td></tr>';
$html .= '<tr><td style="font-weight:bold;">Téléphone :</td><td>' . $tel . '</td></tr>';
$html .= '<tr><td style="font-weight:bold;">Email :</td><td>' . $email . '</td></tr>';
$html .= '</table>';

$pdf->writeHTMLCell($infosW, 0, $infosX, $infosY, $html, 0, 1, false, true, 'L', true);

// --- Footer texte centré ---
$pdf->SetFont('helvetica', 'I', 6);
$pdf->SetTextColor($violetFonce[0], $violetFonce[1], $violetFonce[2]);
$footerY = $formatCarte[1] - ($pad + 6);
$pdf->SetXY(0, $footerY);
$pdf->Cell($formatCarte[0], 4, "Émis par la Plateforme de suivi des stagiaires", 0, 0, 'C', 0, '', 0);

// --- Vider buffers avant envoi ---
while (ob_get_level()) {
    ob_end_clean();
}

// --- Générer PDF ---
$pdf->Output('carte_stagiaire_' . $stagiaire['id'] . '.pdf', 'I');
exit;
?>