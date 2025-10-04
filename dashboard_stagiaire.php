<?php
session_start();
// Vérifier que l'utilisateur est un stagiaire
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'stagiaire') {
    header("Location: login.php");
    exit;
}
// Connexion PDO
try {
    $pdo = new PDO("mysql:host=localhost;dbname=suivi_stagiaires;charset=utf8", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e){
    die("Erreur de connexion : " . $e->getMessage());
}

// Récupérer le mois et l'année depuis GET ou utiliser le mois courant
$mois = isset($_GET['mois']) ? intval($_GET['mois']) : intval(date('m'));
$annee = isset($_GET['annee']) ? intval($_GET['annee']) : intval(date('Y'));
$nbJours = cal_days_in_month(CAL_GREGORIAN, $mois, $annee);

// Tous les stagiaires
$stmt = $pdo->query("SELECT id, nom, prenom FROM stagiaires ORDER BY nom");
$stagiaires = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les présences pour le mois
$sql = "SELECT stagiaire_id, DATE(date) AS jour, present 
        FROM presences 
        WHERE MONTH(date) = :mois AND YEAR(date) = :annee";
$res = $pdo->prepare($sql);
$res->execute(['mois' => $mois, 'annee' => $annee]);

$presences = [];
while($row = $res->fetch(PDO::FETCH_ASSOC)){
    $presences[$row['stagiaire_id']][$row['jour']] = $row['present'];
}

// Pré-remplissage absences
$presencesComplet = [];
foreach($stagiaires as $s){
    for($d=1; $d<=$nbJours; $d++){
        $day = $annee . '-' . str_pad($mois,2,'0',STR_PAD_LEFT) . '-' . str_pad($d,2,'0',STR_PAD_LEFT);
        $presencesComplet[$s['id']][$day] = isset($presences[$s['id']][$day]) ? $presences[$s['id']][$day] : 0;
    }
}


// Connexion à la base de données
$conn = new mysqli("localhost", "root", "", "suivi_stagiaires");
if ($conn->connect_error) {
    die("Erreur de connexion : " . $conn->connect_error);
}

// Récupérer les infos du stagiaire connecté
$stagiaire_id = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 0;
$nom = isset($_SESSION['nom']) ? $_SESSION['nom'] : '';
$prenom = isset($_SESSION['prenom']) ? $_SESSION['prenom'] : '';
$photo = isset($_SESSION['photo']) ? $_SESSION['photo'] : '';

if ($stagiaire_id === 0) {
    die("Erreur : stagiaire introuvable.");
}

// Créer table messages si non existante
$createTable = "CREATE TABLE IF NOT EXISTS messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_stagiaire INT NOT NULL,
    message TEXT NOT NULL,
    date_envoi TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    lu TINYINT(1) DEFAULT 0,
    FOREIGN KEY (id_stagiaire) REFERENCES stagiaires(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
$conn->query($createTable);

// Envoi d’un message
if (isset($_POST['message']) && !empty(trim($_POST['message']))) {
    $message = trim($_POST['message']);
    $stmt = $conn->prepare("INSERT INTO messages (id_stagiaire, message) VALUES (?, ?)");
    $stmt->bind_param("is", $stagiaire_id, $message);
    $stmt->execute();
    $stmt->close();
}

// --- Récupération des présences pour le mois courant ---
$mois = date('m');
$annee = date('Y');
$nbJours = cal_days_in_month(CAL_GREGORIAN, $mois, $annee);

// Début et fin du mois
$debutMois = "$annee-$mois-01";
$finMois = "$annee-$mois-$nbJours";

// Récupérer toutes les présences du stagiaire
$stmt = $conn->prepare("SELECT DATE(date) AS jour, present, statut 
    FROM presences 
    WHERE stagiaire_id = ? AND date BETWEEN ? AND ? 
    ORDER BY date ASC");
$stmt->bind_param("iss", $stagiaire_id, $debutMois, $finMois);
$stmt->execute();
$result = $stmt->get_result();

$presences = [];
while ($row = $result->fetch_assoc()) {
    $dateOnly = date('Y-m-d', strtotime($row['jour']));
    $presences[$dateOnly] = [
        'present' => intval($row['present']),
        'statut'  => $row['statut']
    ];
}
$stmt->close();

// Récupérer les messages envoyés par le stagiaire
$messages = $conn->query("SELECT * FROM messages WHERE stagiaire_id = $stagiaire_id ORDER BY date_envoi DESC");

?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Dashboard Stagiaire</title>
<style>
body { font-family: Arial, sans-serif; background:#f0f0f0; margin:0; padding:0; }
header { background:#4b0082; color:#fff; padding:15px; text-align:center; }
.container { width:90%; max-width:1000px; margin:20px auto; }
h1 { color: #ccc; }
.profile { display:flex; align-items:center; margin-bottom:20px; background:#fff; padding:15px; border-radius:10px; }
.profile img { width:80px; height:80px; border-radius:50%; object-fit:cover; margin-right:15px; border:2px solid #9370DB; }
table { width:100%; border-collapse: collapse; margin-bottom:20px; font-size:14px;}
th, td { border:1px solid #4b0082; padding:6px; text-align:center; }
th { background-color:#9370DB; color:white; }
.present { background-color:#c6efce; font-weight:bold; }
.absent { background-color:#f2e6ff; font-weight:bold; }
textarea { width:100%; padding:8px; border-radius:5px; border:1px solid #ccc; }
button { background:#9370DB; color:#fff; padding:8px 15px; border:none; border-radius:5px; cursor:pointer; margin-top:5px; }
button:hover { background:#4b0082; }
.message-box { border:1px solid #ccc; padding:10px; margin-bottom:10px; border-radius:5px; background:#fff; }
.logout { display:block; text-align:center; margin:20px auto; color:#fff; background:#4b0082; padding:10px 15px; border-radius:5px; text-decoration:none; width:150px; }
.logout:hover { background:#9370DB; }
 :root { --violet-fonce: #6a0dad; --violet-clair: #d1b3ff; }
        body { font-family: Arial, sans-serif; background: #f4f4f9; margin: 0; }
        header { background: var(--violet-fonce); padding: 20px; text-align: center; color: white; }
        .container { background: white; margin: 20px auto; padding: 20px; border-radius: 15px; width: 95%; max-width: 900px; }
        textarea { width: 100%; padding: 10px; margin-top: 10px; border-radius: 6px; border: 1px solid #ccc; }
        button { background: var(--violet-fonce); color: white; padding: 10px 15px; border: none; border-radius: 6px; cursor: pointer; margin-top: 10px; }
        button:hover { background: #4b0082; }
        ul { list-style: none; padding: 0; }
        li { padding: 10px; margin: 5px 0; background: #eee; border-radius: 6px; }
</style>
</head>
<body>

<header>
    <h1>Bienvenue <?= htmlspecialchars($prenom . ' ' . $nom) ?></h1>
</header>

<div class="container">

    <div class="profile">
        <?php if (!empty($photo)): ?>
            <img src="uploads/<?= htmlspecialchars($photo) ?>" alt="Photo">
        <?php else: ?>
            <img src="default.png" alt="Photo">
        <?php endif; ?>
        <div>
            <p><strong>Nom :</strong> <?= htmlspecialchars($nom) ?></p>
        
            <p><strong>Mois affiché :</strong> <?= $mois ?>/<?= $annee ?></p>
        </div>
    </div>

    <!-- Présences -->
    <h2>Récapitulatif des présences - <?php echo $mois.'/'.$annee; ?></h2>
    <p><a href="export_presences_pdf.php?mois=<?php echo $mois; ?>&annee=<?php echo $annee; ?>">📄 Exporter en PDF</a></p>

    <table>
        <tr>
            <th>Stagiaire</th>
            <?php for($j=1;$j<=$nbJours;$j++): ?>
                <th><?php echo $j; ?></th>
            <?php endfor; ?>
        </tr>
        <?php foreach($stagiaires as $s): ?>
            <tr>
                <td class="nom"><?php echo htmlspecialchars($s['nom'].' '.$s['prenom'], ENT_QUOTES, 'UTF-8'); ?></td>
                <?php for($d=1;$d<=$nbJours;$d++):
                    $date = $annee.'-'.str_pad($mois,2,'0',STR_PAD_LEFT).'-'.str_pad($d,2,'0',STR_PAD_LEFT);
                    $val = $presencesComplet[$s['id']][$date];
                    $class = $val ? 'present' : 'absent';
                    $txt = $val ? 'P' : 'A';
                ?>
                    <td class="<?php echo $class; ?>"><?php echo $txt; ?></td>
                <?php endfor; ?>
            </tr>
        <?php endforeach; ?>
    </table>
    <!-- Messagerie -->
    

    <div class="container">
        <h2>💬 Envoyer un message à votre encadrant</h2>
        <form method="post" action="envoyer_message.php">
            <textarea name="message" rows="4" placeholder="Écrivez votre message..." required></textarea>
            <button type="submit">Envoyer</button>
        </form>
    </div>

    <div class="container">
        <h2>📨 Réponses de votre encadrant</h2>
        <ul>
        <?php
        $res = $conn->query("SELECT * FROM messages WHERE stagiaire_id=$stagiaire_id AND expediteur='encadrant' ORDER BY date_envoi DESC");
        while ($row = $res->fetch_assoc()) {
            echo "<li><strong>".$row['date_envoi']."</strong><br>".$row['message']."</li>";
        }
        ?>
        </ul>
    </div>
    <a href="logout.php" class="logout">Se déconnecter</a>


</body>
</html>
<?php $conn->close(); ?>
