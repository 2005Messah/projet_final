<?php
// generer_attestation.php
session_start();

// Vérifier que l'utilisateur est un encadrant
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'encadrant') {
    header("Location: login.php");
    exit;
}

require("tcpdf/tcpdf.php");

// Connexion DB
$conn = new mysqli("localhost", "root", "", "suivi_stagiaires");
if ($conn->connect_error) die("Erreur DB: " . $conn->connect_error);

// Vérifier si un stagiaire est sélectionné
if (!isset($_GET['id'])) {
    die("Stagiaire non spécifié.");
}

$id = intval($_GET['id']);
$res = $conn->query("SELECT * FROM stagiaires WHERE id=$id");
if (!$res || $res->num_rows == 0) {
    die("Stagiaire introuvable.");
}
$stagiaire = $res->fetch_assoc();

// --- Création du PDF ---
$pdf = new TCPDF("P", "mm", "A4", true, "UTF-8", false);
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor("Boulangerie - Service RH");
$pdf->SetTitle("Attestation de Fin de Stage");
$pdf->SetMargins(20, 20, 20);
$pdf->AddPage();

// Couleur violette personnalisée
$pdf->SetTextColor(106, 27, 154);

// --- En-tête ---
$pdf->SetFont("helvetica", "B", 18);
$pdf->Cell(0, 10, "ATTESTATION DE FIN DE STAGE", 0, 1, "C");

$pdf->Ln(10);

// Texte principal
$pdf->SetFont("helvetica", "", 12);
$pdf->SetTextColor(0, 0, 0);

$nomComplet = strtoupper($stagiaire['nom']) . " " . ucfirst($stagiaire['prenom']);
$filiere = !empty($stagiaire['filiere']) ? $stagiaire['filiere'] : "_________";
$lieu = !empty($stagiaire['lieu']) ? $stagiaire['lieu'] : "_________";

$contenu = "
Nous soussignés, la direction de la Fondation Tchuente, certifions que :

<b>$nomComplet</b>, inscrit(e) dans la filière <b>$filiere</b>, a effectué un stage au sein de notre établissement situé à <b>$lieu</b>.

Ce stage s'est déroulé avec assiduité et sérieux, et a permis au stagiaire de développer ses compétences professionnelles.

Fait pour servir et valoir ce que de droit.
";

$pdf->writeHTML($contenu, true, false, true, false, "J");

// --- Signature ---
$pdf->Ln(30);
$pdf->SetFont("helvetica", "", 12);
$pdf->Cell(0, 10, "Fait à " . $lieu . ", le " . date("d/m/Y"), 0, 1, "R");

$pdf->Ln(20);
$pdf->Cell(0, 10, "Signature de l’encadrant :", 0, 1, "L");
$pdf->Ln(20);
$pdf->Line(20, $pdf->GetY(), 100, $pdf->GetY()); // Ligne pour signature

// --- Sortie du PDF ---
$pdf->Output("attestation_" . $stagiaire['nom'] . ".pdf", "I");
?>

