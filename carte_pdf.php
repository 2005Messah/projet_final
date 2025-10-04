<?php
// Connexion à la base de données
try {
    $pdo = new PDO("mysql:host=localhost;dbname=suivi_stagiaires", "root", "");
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}



if (isset($_POST['id'])) {
    $id = $_POST['id'];
    $stmt = $pdo->prepare("SELECT * FROM stagiaires WHERE id = ?");
    $stmt->execute([$id]);
    $stagiaire = $stmt->fetch();

    if ($stagiaire) {
        $pdf = new tcpdf();
        $pdf->AddPage();

        // Titre
        $pdf->SetFont('Arial', 'B', 16);
        $pdf->Cell(0, 10, 'Carte Stagiaire', 0, 1, 'C');
        $pdf->Ln(10);

        // Image
        if (!empty($stagiaire['photo']) && file_exists($stagiaire['photo'])) {
            $pdf->Image($stagiaire['photo'], 10, 30, 40, 50);
        }

        $pdf->SetFont('Arial', '', 12);
        $pdf->SetY(30);
        $pdf->SetX(60);
        $pdf->Cell(0, 10, 'Nom : ' . $stagiaire['nom'], 0, 1);
        $pdf->SetX(60);
        $pdf->Cell(0, 10, 'Prénom : ' . $stagiaire['prenom'], 0, 1);
        $pdf->SetX(60);
        $pdf->Cell(0, 10, 'Sexe : ' . $stagiaire['sexe'], 0, 1);
        $pdf->SetX(60);
        $pdf->Cell(0, 10, 'Date et lieu de naissance : ' . $stagiaire['naissance'] . ' à ' . $stagiaire['lieu_naissance'], 0, 1);
        $pdf->SetX(60);
        $pdf->Cell(0, 10, 'Téléphone : ' . $stagiaire['telephone'], 0, 1);
        $pdf->SetX(60);
        $pdf->Cell(0, 10, 'Email : ' . $stagiaire['email'], 0, 1);

        $pdf->Output();
    } else {
        echo "Stagiaire non trouvé.";
    }
}
