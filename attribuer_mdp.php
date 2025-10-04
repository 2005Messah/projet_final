<?php
session_start();

// Vérifier rôle encadrant
if(!isset($_SESSION['role']) || $_SESSION['role']!='encadrant'){
    header("Location: login.php");
    exit;
}

$conn = new mysqli("localhost","root","","suivi_stagiaires");
if($conn->connect_error) die("Erreur DB: ".$conn->connect_error);

// Récupérer l'ID de la demande
if(!isset($_GET['id'])){
    die("ID de la demande manquant");
}
$id = intval($_GET['id']);

// Récupérer les infos du stagiaire
$stmt = $conn->prepare("SELECT * FROM demandes_stage WHERE id=?");
$stmt->bind_param("i",$id);
$stmt->execute();
$result = $stmt->get_result();
if($result->num_rows==0) die("Demande introuvable");
$stagiaire = $result->fetch_assoc();

// Si le formulaire est soumis
if(isset($_POST['mdp'])){
    $mdp = $_POST['mdp'];
    $tel = preg_replace('/\D/','',$stagiaire['telephone']); // enlever les caractères non numériques
    
    // Mettre à jour la demande et enregistrer le mot de passe hashé
    $mdp_hash = password_hash($mdp, PASSWORD_DEFAULT);
    $conn->query("UPDATE demandes_stage SET etat='Validée', mot_de_passe='".$conn->real_escape_string($mdp_hash)."' WHERE id=$id");

    // Préparer message WhatsApp
    $message = urlencode("Bonjour ".$stagiaire['prenom'].", votre compte est validé. Votre mot de passe est : $mdp");
    $whatsapp_url = "https://wa.me/$tel?text=$message";

    // Rediriger automatiquement vers WhatsApp
    echo "<script>window.open('$whatsapp_url', '_blank'); alert('Mot de passe attribué et WhatsApp ouvert.'); window.location='dashboard_encadrant.php';</script>";
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Attribuer mot de passe</title>
    <style>
        body{font-family:Arial;background:#f4f4f4;padding:20px;}
        .card{background:#fff;padding:20px;border-radius:10px;box-shadow:0 2px 6px rgba(0,0,0,0.1);width:400px;margin:auto;margin-top:50px;}
        input[type=text]{width:100%;padding:8px;margin:10px 0;}
        button{background:#6a1b9a;color:#fff;padding:8px 12px;border-radius:5px;font-weight:bold;border:none;cursor:pointer;}
        button:hover{background:#9c27b0;}
    </style>
</head>
<body>
<div class="card">
    <h3>Attribuer un mot de passe à <?php echo htmlspecialchars($stagiaire['prenom'].' '.$stagiaire['nom']); ?></h3>
    <form method="post">
        <label>Mot de passe :</label>
        <input type="text" name="mdp" required>
        <button type="submit">Valider et envoyer WhatsApp</button>
    </form>
</div>
</body>
</html>
