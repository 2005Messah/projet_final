<?php
session_start();
$conn = new mysqli("localhost", "root", "", "suivi_stagiaires");

$id_encadrant = $_SESSION['id'];
$id_stagiaire = (int)$_POST['id_stagiaire'];
$message = $conn->real_escape_string($_POST['message']);

$conn->query("INSERT INTO messages (id_stagiaire, id_encadrant, expediteur, message) 
              VALUES ($id_stagiaire, $id_encadrant, 'encadrant', '$message')");

header("Location: dashboard_encadrant.php");
exit;
?>
