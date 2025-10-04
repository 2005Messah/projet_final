<?php
session_start();

// Vérifier que l'utilisateur est un encadrant
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'encadrant') {
    header("Location: login.html");
    exit;
}

// Connexion à la base de données
$conn = new mysqli("localhost", "root", "", "suivi_stagiaires");
if ($conn->connect_error) {
    die("Erreur de connexion à la base : " . $conn->connect_error);
}

// Gestion de la réponse à un message
if (isset($_POST['reponse'], $_POST['message_id'])) {
    $message_id = intval($_POST['message_id']);
    $reponse = trim($_POST['reponse']);

    if (!empty($reponse)) {
        // Créer la table reponses si elle n'existe pas
        $createReponsesTable = "CREATE TABLE IF NOT EXISTS reponses (
            id INT AUTO_INCREMENT PRIMARY KEY,
            id_message INT NOT NULL,
            reponse TEXT NOT NULL,
            date_reponse TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

        if (!$conn->query($createReponsesTable)) {
            die("Erreur création table réponses : " . $conn->error);
        }

        $stmt = $conn->prepare("INSERT INTO reponses (id_message, reponse) VALUES (?, ?)");
        $stmt->bind_param("is", $message_id, $reponse);
        $stmt->execute();
        $stmt->close();

        // Récupérer le numéro du stagiaire pour WhatsApp
        $numQuery = $conn->prepare("SELECT s.telephone FROM messages m JOIN stagiaires s ON m.id_stagiaire = s.id WHERE m.id = ?");
        $numQuery->bind_param("i", $message_id);
        $numQuery->execute();
        $numQuery->bind_result($telephone);
        $numQuery->fetch();
        $numQuery->close();

        if (!empty($telephone)) {
            // Préparer le message WhatsApp
            $texteWA = urlencode("Bonjour, votre encadrant a répondu à votre message sur la plateforme de suivi des stages :\n\n" . $reponse);
            $whatsappLink = "https://wa.me/" . preg_replace('/\D/', '', $telephone) . "?text=" . $texteWA;

            echo "<p style='color:green;'>Réponse envoyée avec succès ! <a href='$whatsappLink' target='_blank'>Envoyer la notification WhatsApp</a></p>";
        } else {
            echo "<p style='color:orange;'>Réponse envoyée, mais le numéro WhatsApp du stagiaire est manquant.</p>";
        }
    }
}

// Récupérer tous les messages
$sql = "SELECT m.id, m.id_stagiaire, m.message, m.date_envoi, m.lu, 
               s.nom, s.prenom, s.telephone
        FROM messages m 
        JOIN stagiaires s ON m.id_stagiaire = s.id
        ORDER BY m.date_envoi DESC";

$result = $conn->query($sql);
if (!$result) {
    die("Erreur lors de la récupération des messages : " . $conn->error);
}

// Marquer tous les messages comme lus
$conn->query("UPDATE messages SET lu = 1 WHERE lu = 0");

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Messages des stagiaires</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background-color: #f0f0f0; }
        .message { background: #fff; border-radius: 10px; padding: 15px; margin-bottom: 20px; box-shadow: 0 2px 6px rgba(0,0,0,0.1); }
        .message h3 { margin: 0; color: #4b0082; }
        .message p { margin: 10px 0; }
        .status { font-weight: bold; color: #fff; padding: 3px 8px; border-radius: 5px; }
        .lu { background-color: green; }
        .non-lu { background-color: red; }
        textarea { width: 100%; padding: 8px; margin-top: 5px; border-radius: 5px; border: 1px solid #ccc; }
        button { background-color: #9370DB; color: #fff; padding: 8px 15px; border: none; border-radius: 5px; cursor: pointer; margin-top: 5px; }
        button:hover { background-color: #4b0082; }
    </style>
</head>
<body>
    <h1>Messages des stagiaires</h1>
    <?php if ($result->num_rows > 0): ?>
        <?php while($row = $result->fetch_assoc()): ?>
            <div class="message">
                <h3><?= htmlspecialchars($row['nom'] . ' ' . $row['prenom']); ?> 
                    <span class="status <?= $row['lu'] ? 'lu' : 'non-lu'; ?>">
                        <?= $row['lu'] ? 'Lu' : 'Non lu'; ?>
                    </span>
                </h3>
                <p><strong>Envoyé le :</strong> <?= $row['date_envoi']; ?></p>
                <p><?= nl2br(htmlspecialchars($row['message'])); ?></p>

                <form method="post">
                    <textarea name="reponse" placeholder="Répondre au stagiaire..." required></textarea>
                    <input type="hidden" name="message_id" value="<?= $row['id']; ?>">
                    <button type="submit">Envoyer la réponse</button>
                </form>

                <?php
                // Afficher les réponses existantes
                $respQuery = $conn->prepare("SELECT reponse, date_reponse FROM reponses WHERE id_message = ? ORDER BY date_reponse ASC");
                $respQuery->bind_param("i", $row['id']);
                $respQuery->execute();
                $respResult = $respQuery->get_result();
                if ($respResult->num_rows > 0) {
                    echo "<h4>Réponses :</h4>";
                    while ($r = $respResult->fetch_assoc()) {
                        echo "<p><em>".htmlspecialchars($r['date_reponse']).": </em>".nl2br(htmlspecialchars($r['reponse']))."</p>";
                    }
                }
                $respQuery->close();
                ?>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p>Aucun message pour le moment.</p>
    <?php endif; ?>
</body>
</html>

<?php
$conn->close();
?>
