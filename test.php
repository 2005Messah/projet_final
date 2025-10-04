<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>
    :root {
      --violet-fonce: #6a0dad;
      --violet-clair: #d1b3ff;
      --bg-light: #f9f7fc;
    }

    body {
      margin: 0;
      font-family: 'Segoe UI', sans-serif;
      background-color: var(--bg-light);
      color: #333;
    }

    header {
      background-color: var(--violet-fonce);
      padding: 20px;
      color: white;
      text-align: center;
    }

    .container {
      padding: 20px;
      display: grid;
      gap: 20px;
    }

    .card {
      background-color: white;
      border-left: 5px solid var(--violet-fonce);
      padding: 20px;
      border-radius: 10px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    .card h3 {
      margin: 0 0 10px;
      color: var(--violet-fonce);
    }

    .grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 20px;
    }

    .btn {
      background-color: var(--violet-clair);
      color: #fff;
      padding: 10px 15px;
      border: none;
      border-radius: 5px;
      text-decoration: none;
      display: inline-block;
      font-weight: bold;
    }

    .btn:hover {
      background-color: var(--violet-fonce);
    }

    @media screen and (max-width: 600px) {
      header {
        font-size: 18px;
      }
    }
  </style>
</head>
<body>
  
  <div class="container">
    <div class="grid">
      <div class="card">
        <h3>Ajouter un stagiaire</h3>
        <p>Créer une nouvelle fiche stagiaire.</p>
        <a href="ajouter_stagiaire.php" class="btn">Ajouter</a>
      </div>

      <div class="card">
        <h3>Liste des stagiaires</h3>
        <p>Voir, modifier ou supprimer les stagiaires.</p>
        <a href="liste_stagiaires.php" class="btn">Afficher</a>
      </div>

      <div class="card">
        <h3>Ajouter un stage</h3>
        <p>Enregistrer un nouveau stage.</p>
        <a href="ajouter_stage.php" class="btn">Ajouter</a>
      </div>

      <div class="card">
        <h3>Générer une carte</h3>
        <p>Créer et télécharger une carte de stagiaire.</p>
        <a href="generer_carte.php?id=1" class="btn">Générer</a>
      </div>
    </div>
  </div>
</body>
</html>
