<?php

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nom = htmlspecialchars(trim($_POST['nom']));
    $matricule = htmlspecialchars(trim($_POST['matricule']));
    $password = trim($_POST['password']);
    $role = $_POST['role'];

    if (!empty($nom) && !empty($matricule) && !empty($password) && !empty($role)) {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        try {
            $pdo = new PDO("mysql:host=localhost;dbname=suivi_stagiaires;charset=utf8", "root", "");
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $stmt = $pdo->prepare("INSERT INTO utilisateurs (nom, matricule, mot_de_passe, role) VALUES (?, ?, ?, ?)");
            $stmt->execute([$nom, $matricule, $password_hash, $role]);

            echo "Inscription réussie ! <a href='login.php'>Se connecter</a>";
        } catch (PDOException $e) {
            echo "Erreur : " . $e->getMessage();
        }
    } else {
        echo "Tous les champs sont requis.";
    }
}
?>

<!-- Formulaire HTML -->
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Inscription</title>
    <style>
        body {
            background: linear-gradient(to right, #4b0082, #9370DB);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            font-family: 'Poppins', sans-serif;
        }

        .register-box {
            background: #fff;
            padding: 30px;
            border-radius: 15px;
            width: 100%;
            max-width: 450px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.2);
        }

        h2 {
            text-align: center;
            color: #4b0082;
        }

        label {
            display: block;
            margin-top: 15px;
            font-weight: bold;
        }

        input, select {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 8px;
        }

        button {
            width: 100%;
            padding: 12px;
            background-color: #4b0082;
            color: white;
            border: none;
            border-radius: 8px;
            margin-top: 20px;
            cursor: pointer;
        }

        button:hover {
            background-color: #5a00b3;
        }
    </style>
</head>
<body>
    <div class="register-box">
        <h2>Inscription</h2>
        <form method="post" action="">
            <label>Nom complet</label>
            <input type="text" name="nom" required>

            <label>Matricule</label>
            <input type="text" name="matricule" required>

            <label>Mot de passe</label>
            <input type="password" name="password" required>

            <label>Rôle</label>
            <select name="role" required>
                <option value="">-- Sélectionnez --</option>
                <option value="admin">administrateur</option>
                <option value="stagiaire">Stagiaire</option>
                <option value="encadrant">Encadrant</option>
            </select>

            <button type="submit">S'inscrire</button>
        </form>
    </div>
</body>
</html>
