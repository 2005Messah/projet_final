<?php
$pdo = new PDO("mysql:host=localhost;dbname=suivi_stagiaires", "root", "");
$data = json_decode(file_get_contents("php://input"), true);

if ($data && isset($data['nom'], $data['prenom'], $data['latitude'], $data['longitude'])) {
    $stmt = $pdo->prepare("INSERT INTO stagiaires (nom, prenom, latitude, longitude) VALUES (?, ?, ?, ?)");
    $stmt->execute([
        $data['nom'],
        $data['prenom'],
        $data['latitude'],
        $data['longitude']
    ]);
    echo "Position enregistrée avec succès.";
} else {
    echo "Données invalides.";
}
