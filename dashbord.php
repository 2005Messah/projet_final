<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Tableau de Bord - Suivi Stagiaires</title>
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
      background: URL("uploads/les stagiaires/fond.png") no-repeat center center/cover;
      
    }

    .hero {
        height: 100vh;
        text-align: center;
        color: var(violet clair);    
    }
    .hero .bienvenue {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        min-width: 100%; 
        width: 100%;
        height: 100%;
    }
    .hero h2 {
        color: var(--violet-fonce);
    }
    .hero p {
        color: var(--violet-fonce);
    }

    header {
      background-color: #0003;
      padding: 20px;
      color: white;
      text-align: center;
    }
   
    h1 {
       color: var(--violet-fonce);

    }
    .container p {
        color: #a10fa1ff;
        background-color: #0003;
        text-align: center;
        padding: 20px 0;
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
  <header>
    <h1> Suivi de Stagiaires</h1>
  </header>
  <section class="hero">
   <div class="bienvenue">
    <h2>BIENVENUE DANS MON SITE DE SUIVI DE PLANNING DE STAGIAIRES</h2>
    <p>inscrivez-vous afin de découvrez mon site!!</p>
    <a href="register.php" class="btn">s'inscrire</a>

   </div>
</section>
<section>
<footer>
    <div class="container">
        <p>&copy; 2025 SUIVI DE PLANNING DE STAGIAIRE</p>
    </div>
</footer>
</section>
</body>
</html>
