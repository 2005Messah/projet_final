<?php
$pdo = new PDO("mysql:host=localhost;dbname=suivi_stagiaires", "root", "");

$search = $_GET['q'] ?? '';
$sql = "SELECT id, nom, prenom, latitude, longitude FROM stagiaires WHERE latitude IS NOT NULL AND longitude IS NOT NULL";
if ($search) {
    $sql .= " AND (nom LIKE :search OR prenom LIKE :search)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['search' => "%$search%"]);
} else {
    $stmt = $pdo->query($sql);
}
$stagiaires = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Carte interactive des stagiaires</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
  <style>
    body { margin: 0; font-family: Arial; background: #f5f5f5; }
    h2 { color: #6a0dad; text-align: center; margin: 20px 0; }
    #map { height: 80vh; margin: 20px; border-radius: 10px; }
    .search-container {
      text-align: center;
      margin-top: 10px;
    }
    input[type="text"] {
      padding: 8px;
      width: 250px;
      border-radius: 5px;
      border: 1px solid #ccc;
    }
    button {
      padding: 8px 15px;
      background: #6a0dad;
      color: white;
      border: none;
      border-radius: 5px;
    }
  </style>
</head>
<body>

<h2>Carte interactive des stagiaires</h2>

<div class="search-container">
  <form method="get">
    <input type="text" name="q" placeholder="Rechercher nom ou prénom..." value="<?= htmlspecialchars($search) ?>">
    <button type="submit">Rechercher</button>
  </form>
</div>

<div id="map"></div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
  var map = L.map('map').setView([7.3697, 12.3547], 6); // Cameroun

  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; OpenStreetMap contributors'
  }).addTo(map);

  const stagiaires = <?= json_encode($stagiaires); ?>;

  stagiaires.forEach(s => {
    L.marker([s.latitude, s.longitude])
     .addTo(map)
     .bindPopup(`<strong>${s.nom} ${s.prenom}</strong><br>ID: ${s.id}`);
  });

  // Cliquez sur la carte pour enregistrer une nouvelle position (optionnel)
  map.on('click', function(e) {
    const lat = e.latlng.lat;
    const lon = e.latlng.lng;
    const nom = prompt("Nom du stagiaire ?");
    const prenom = prompt("Prénom du stagiaire ?");
    if (nom && prenom) {
      fetch('sauvegarder_position.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({nom, prenom, latitude: lat, longitude: lon})
      })
      .then(res => res.text())
      .then(msg => alert(msg))
      .catch(err => alert("Erreur : " + err));
    }
  });
</script>
</body>
</html>
