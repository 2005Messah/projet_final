<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'encadrant') {
    header("Location: login.html");
    exit;
}

// Connexion DB
$conn = new mysqli("localhost", "root", "", "suivi_stagiaires");
if ($conn->connect_error) {
    die("Erreur connexion DB: " . $conn->connect_error);
}

// Vérifier si formulaire soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id']);
    $motdepasse = $_POST['password'];

    if (!$id || !$motdepasse) {
        die("Erreur : ID ou mot de passe manquant.");
    }

    // Récupérer la demande
    $sql = "SELECT * FROM demandes_stage WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $demande = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$demande) {
        die("Demande introuvable.");
    }

    // Hacher le mot de passe
    $hashedPassword = password_hash($motdepasse, PASSWORD_BCRYPT);

    // Insertion dans stagiaires
   // Insertion dans stagiaires avec photo et sexe
$sql = "INSERT INTO stagiaires (sexe, nom, prenom, telephone, email, mot_de_passe, photo, date_inscription) 
        VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
$stmt = $conn->prepare($sql);
$stmt->bind_param(
    "sssssss",
    $demande['sexe'],    // récupère le sexe de demandes_stage
    $demande['nom'],
    $demande['prenom'],
    $demande['telephone'],
    $demande['email'],
    $hashedPassword,
    $demande['photo']    // récupère la photo de demandes_stage
);


    if ($stmt->execute()) {
        // ✅ Stagiaire ajouté
        $stmt->close();

        // Préparer le message WhatsApp
        $message = "Bonjour " . $demande['nom'] . ", votre demande de stage a été ACCEPTÉE ✅. 
Voici vos informations de connexion :
Email : " . $demande['email'] . "
Mot de passe : " . $motdepasse;

        $whatsappUrl = "https://wa.me/" . $demande['telephone'] . "?text=" . urlencode($message);

        // Rediriger vers WhatsApp
        header("Location: $whatsappUrl");
        exit;
    } else {
        die("Erreur insertion stagiaire : " . $stmt->error);
    }
}
?>
