<?php
session_start();
$conn = new mysqli("localhost", "root", "", "suivi_stagiaires");

$id_stagiaire = $_SESSION['id'];
$id_encadrant = 1; // si un seul encadrant, sinon à gérer selon ton système
$message = $conn->real_escape_string($_POST['message']);

$conn->query("INSERT INTO messages (id_stagiaire, id_encadrant, expediteur, message) 
              VALUES ($id_stagiaire, $id_encadrant, 'stagiaire', '$message')");

header("Location: dashboard_stagiaire.php");
exit;
?>
