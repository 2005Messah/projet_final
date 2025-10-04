<?php
if (!isset($_GET['id'])) {
    die("ID du stagiaire manquant !");
}

$id = $_GET['id'];

try {
    $pdo = new PDO("mysql:host=localhost;dbname=suivi_stagiaires", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Récupérer la photo pour la supprimer du dossier
    $stmt = $pdo->prepare("SELECT photo FROM stagiaires WHERE id = ?");
    $stmt->execute([$id]);
    $stagiaire = $stmt->fetch();

    if ($stagiaire && !empty($stagiaire['photo']) && file_exists($stagiaire['photo'])) {
        unlink($stagiaire['photo']); // Supprimer la photo du disque
    }

    // Supprimer le stagiaire
    $stmt = $pdo->prepare("DELETE FROM stagiaires WHERE id = ?");
    $stmt->execute([$id]);

    echo "<script>alert('Stagiaire supprimé avec succès !'); window.location.href='test2.php';</script>";
} catch (PDOException $e) {
    die("Erreur : " . $e->getMessage());
}
?>
