<?php
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "suivi_stagiaires";

// Connexion à la base de données
$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("Erreur de connexion : " . $conn->connect_error);
}

$stagiaire = null;
$msg = "";

// Récupérer les données existantes du stagiaire
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $result = $conn->query("SELECT * FROM stagiaires WHERE id = $id");
    $stagiaire = $result->fetch_assoc();
}

// Mise à jour des données après soumission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id']);
    $sexe = $_POST['sexe'];
    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
    $telephone = $_POST['telephone'];
    $email = $_POST['email'];

    // Hachage du mot de passe uniquement s'il est rempli
    $password = !empty($_POST['mot_de_passe']) ? password_hash($_POST['mot_de_passe'], PASSWORD_DEFAULT) : null;

    // Upload de la photo
    $photo = $stagiaire['photo'];
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === 0) {
        $file_tmp = $_FILES['photo']['tmp_name'];
        $file_name = basename($_FILES['photo']['name']);
        $destination = "uploads/" . $file_name;

        if (move_uploaded_file($file_tmp, $destination)) {
            $photo = $destination;
        }
    }

    // Mise à jour SQL
    $sql = "UPDATE stagiaires SET sexe=?, nom=?, prenom=?,  telephone=?, email=?, photo=?";
    $params = [$sexe, $nom, $prenom, $telephone, $email, $photo];
    $types = "ssssss";

    if ($password) {
        $sql .= ", mot_de_passe=?";
        $params[] = $password;
        $types .= "s";
    }

    $sql .= " WHERE id=?";
    $params[] = $id;
    $types .= "i";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);

    if ($stmt->execute()) {
     echo "<script>alert('Stagiaire modifié avec succès !'); window.location.href='test2.php';</script>";
    exit(); // Important !
}

}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier Stagiaire</title>
    <style>
        body {
    background-color: #f3e5f5;
    font-family: Arial, sans-serif;
    margin: 0;
    height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
}

.form-container {
    display: flex;
    align-items: center;
    justify-content: center;
    height: 100%;
    width: 100%;
}

form {
    background-color: #6a1b9a;
    color: white;
    padding: 20px;
    border-radius: 10px;
    width: 400px;
    box-shadow: 0 0 15px rgba(0,0,0,0.3);
}

        body {
            background-color: #f3e5f5;
            font-family: Arial, sans-serif;
            padding: 40px;
        }

        form {
            background-color: #6a1b9a;
            color: white;
            padding: 20px;
            border-radius: 10px;
            width: 400px;
        }

        input, select {
            width: 100%;
            padding: 8px;
            margin: 6px 0;
            border: none;
            border-radius: 5px;
        }

        input[type="submit"] {
            background-color: #9c27b0;
            color: white;
            cursor: pointer;
        }

        img {
            max-width: 100px;
            margin-bottom: 10px;
            border-radius: 5px;
        }
    </style>
</head>
<body>



<?php if ($stagiaire): ?>
    <div class="form-container">
    <?= $msg ?>
    <form method="POST" enctype="multipart/form-data">
           <h2>Modifier le stagiaire</h2>
    <input type="hidden" name="id" value="<?= $stagiaire['id'] ?>">

    <label>Sexe :</label>
    <select name="sexe" required>
        <option value="F" <?= ($stagiaire['sexe'] == 'F') ? 'selected' : '' ?>>Feminin</option>
        <option value="M" <?= ($stagiaire['sexe'] == 'M') ? 'selected' : '' ?>>Masculin</option>

    </select>

    <label>Nom :</label>
    <input type="text" name="nom" value="<?= htmlspecialchars($stagiaire['nom']) ?>" required>

    <label>Prénom :</label>
    <input type="text" name="prenom" value="<?= htmlspecialchars($stagiaire['prenom']) ?>" required>

    <label>telephone :</label>
    <input type="text" name="telephone" value="<?= htmlspecialchars($stagiaire['telephone']) ?>" required>

    <label>Email :</label>
    <input type="email" name="email" value="<?= htmlspecialchars($stagiaire['email']) ?>" required>

    <label>Nouveau mot de passe (laisser vide si inchangé) :</label>
    <input type="password" name="mot_de_passe">

    <label>Photo :</label><br>
    <?php if (!empty($stagiaire['photo'])): ?>
        <img src="<?= $stagiaire['photo'] ?>" alt="Photo"><br>
    <?php endif; ?>
    <input type="file" name="photo">

    <input type="submit" value="Modifier">
</form>
</div>


<?php else: ?>
    <p style="color:red;">Stagiaire introuvable.</p>
<?php endif; ?>

</body>
</html>
