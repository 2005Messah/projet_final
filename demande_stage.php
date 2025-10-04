<?php
session_start();

// Récupérer le nom du partenaire depuis l'URL si présent
$lieu_souhaite = isset($_GET['partenaire']) ? htmlspecialchars($_GET['partenaire']) : '';

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Tableau de bord Stagiaire</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #9370DB;
            color: white;
            text-align: center;
            padding: 50px;
        }

        .container {
            background: white;
            color: #4b0082;
            padding: 30px;
            border-radius: 15px;
            display: inline-block;
            width: 80%;
            max-width: 700px;
        }

        input, select {
            width: 95%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #4b0082;
            border-radius: 8px;
        }

        label {
            font-weight: bold;
            float: left;
            margin-left: 2%;
        }

        .btn {
            background-color: #9370DB;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-weight: bold;
        }

        .btn:hover {
            background-color: #4b0082;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Bienvenue</h1>
        <p>Soumettez votre demande de stage ci-dessous :</p>

        <form action="traitement_demande.php" method="post" enctype="multipart/form-data">
            <label>Nom :</label>
            <input type="text" name="nom" required>

            <label>Prénom :</label>
            <input type="text" name="prenom" required>

            <label>Email :</label>
            <input type="email" name="email" required>

           <label>Sexe :</label>
            <label><input type="radio" name="sexe" value="F" required> F</label>
            <label><input type="radio" name="sexe" value="M"> M</label>
            <br><br>
        
            <label>Photo (JPG, PNG) :</label>
            <input type="file" name="photo" accept="image/*" required>

            <label>Lieu de stage souhaité :</label>
            <select name="lieu" required>
                <option value="">Sélectionnez un lieu</option>
                <option value="Hôpital Régional de Bafoussam" <?php if($lieu_souhaite == 'Hôpital Régional de Bafoussam') echo 'selected'; ?>>Hôpital Régional de Bafoussam</option>
                <option value="Hôpital de District de Bafoussam" <?php if($lieu_souhaite == 'Hôpital de District de Bafoussam') echo 'selected'; ?>>Hôpital de District de Bafoussam</option>
                <option value="CMA TYO de Baleng" <?php if($lieu_souhaite == 'CMA TYO de Baleng') echo 'selected'; ?>>CMA TYO de Baleng</option>
            </select>

            <label>Filière :</label>
            <select name="filiere" required>
                <option value="">Sélectionnez votre filière</option>
                <option value="IDE1">IDE1</option>
                <option value="IDE2">IDE2</option>
                <option value="IDE3">IDE3</option>
                <option value="AS">AS</option>
            </select>

            <label>Numéro de téléphone :</label>
            <input type="text" name="telephone" required>

            <label>Date de début :</label>
            <input type="date" name="date_debut" required>

            <label>Date de fin :</label>
            <input type="date" name="date_fin" required>

            <label>CV (PDF) :</label>
            <input type="file" name="cv" accept="application/pdf" required>

            <label>Lettre de motivation (PDF) :</label>
            <input type="file" name="lettre" accept="application/pdf" required>

            <label>Certificat de scolarité (PDF) :</label>
            <input type="file" name="certificat" accept="application/pdf" required>

            <button type="submit" class="btn">Soumettre la demande</button>
        </form>

        <br>
        <a href="logout.php">Se déconnecter</a>
    </div>
</body>
</html>