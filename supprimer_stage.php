<?php
// Connexion à la base de données
$pdo = new PDO("mysql:host=localhost;dbname=suivi_stagiaires", "root", "");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $pdo->prepare("DELETE FROM stages WHERE id_stages = ?");
    $stmt->execute([$id]);
}

header("Location: liste_stages.php");
exit();
