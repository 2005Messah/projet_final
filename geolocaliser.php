<?php
// Connexion à la base
try {
    $pdo = new PDO("mysql:host=localhost;dbname=suivi_stagiaires", "root", "");
} catch (PDOException $e) {
    die("Erreur : " . $e->getMessage());
}

// Enregistrement des coordonnées
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ; // Exemple avec ID 1
    $lat = $_POST['latitude'];
    $lng = $_POST['longitude'];

    $stmt = $pdo->prepare("UPDATE stagiaire SET latitude = ?, longitude = ? WHERE id = ?");
    $stmt->execute([$lat, $lng, $id]);

    echo "Coordonnées enregistrées avec succès.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Géolocalisation du stagiaire</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        :root {
            --violet-fonce: #6a0dad;
            --violet-clair: #e1bee7;
            --blanc: #fff;
        }

        body {
            font-family: Arial, sans-serif;
            background-color: var(--violet-clair);
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 30px;
        }

        h2 {
            color: var(--violet-fonce);
        }

        .box {
            background: var(--blanc);
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px #ccc;
            text-align: center;
            width: 100%;
            max-width: 400px;
        }

        button {
            background-color: var(--violet-fonce);
            color: white;
            padding: 12px 18px;
            border: none;
            border-radius: 6px;
            margin-top: 20px;
            cursor: pointer;
        }

        .result {
            margin-top: 20px;
            font-weight: bold;
        }
    </style>
</head>
<body>

<h2>Localiser le stagiaire</h2>

<div class="box">
    <p>Appuyez sur le bouton pour localiser le stagiaire et enregistrer sa position.</p>
    <button onclick="geolocaliser()">📍 Géolocaliser</button>

    <div class="result" id="result"></div>
</div>

<script>
function geolocaliser() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function(position) {
            const lat = position.coords.latitude;
            const lng = position.coords.longitude;

            document.getElementById('result').textContent = `Latitude: ${lat}, Longitude: ${lng}`;

            // Envoi au serveur PHP
            const formData = new FormData();
            formData.append('latitude', lat);
            formData.append('longitude', lng);
            formData.append('id', 1); // Remplacez par l'ID réel

            fetch('geolocaliser.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.text())
            .then(msg => {
                alert(msg);
            });
        }, function(error) {
            alert("Erreur de géolocalisation : " + error.message);
        });
    } else {
        alert("La géolocalisation n'est pas supportée par ce navigateur.");
    }
}
</script>

</body>
</html>
