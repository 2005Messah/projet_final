<?php
session_start();

$debug = false;

// Paramètres BD
$host = 'localhost';
$dbname = 'suivi_stagiaires';
$db_user = 'root';
$db_pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    if ($debug) { die("Erreur connexion DB: " . $e->getMessage()); }
    $error = "Erreur serveur, contactez l'administrateur.";
}

// Traitement du formulaire si POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifier = isset($_POST['matricule']) ? trim($_POST['matricule']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    if ($identifier === '' || $password === '') {
        $error = "Veuillez remplir tous les champs.";
    } else {
        // Vérifier si la colonne email existe
        $cols = $pdo->query("SHOW COLUMNS FROM utilisateurs")->fetchAll(PDO::FETCH_COLUMN);
        $emailExists = in_array('email', $cols);

        // Chercher utilisateur par matricule OU email
        $sql = "SELECT * FROM utilisateurs WHERE matricule = :id" . ($emailExists ? " OR email = :id" : "") . " LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id' => $identifier]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            $error = "Aucun compte trouvé pour cet identifiant.";
        } else {
            // Vérifier mot de passe
            $stored_password = null;
            foreach (['mot_de_passe','password','pass','pwd'] as $col) {
                if (isset($user[$col])) { 
                    $stored_password = $user[$col]; 
                    break; 
                }
            }

            if ($stored_password === null) {
                $error = "Erreur serveur, contactez l'administrateur.";
            } else {
                // Vérification du mot de passe
                $login_ok = false;
                
                // Si le mot de passe est haché
                if (function_exists('password_verify') && password_verify($password, $stored_password)) {
                    $login_ok = true;
                } 
                // Si le mot de passe est en texte brut (pour compatibilité)
                elseif ($stored_password === $password) {
                    $login_ok = true;
                }

                if ($login_ok) {
                    // Créer session
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['nom'] = isset($user['nom']) ? $user['nom'] : '';
                    $_SESSION['role'] = isset($user['role']) ? strtolower($user['role']) : '';
                    $_SESSION['username'] = $_SESSION['nom']; // Ajout pour compatibilité

                    // Redirection selon rôle
                    switch ($_SESSION['role']) {
                        case 'stagiaire':
                            header("Location: dashboard_stagiaire.php");
                            exit;
                        case 'encadrant':
                            header("Location: dashboard_encadrant.php");
                            exit;
                        case 'admin':
                        case 'administrateur':
                            header("Location: dashboard_admin.php");
                            exit;
                        default:
                            $error = "Rôle inconnu pour cet utilisateur.";
                    }
                } else {
                    $error = "Identifiant ou mot de passe incorrect.";
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Connexion | Plateforme des stagiaires</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet">
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
    body {
      background: linear-gradient(to right, #4b0082, #9370DB);
      display: flex; align-items: center; justify-content: center; height: 100vh;
    }
    .login-box {
      background: #fff; padding: 30px; border-radius: 12px; width: 100%; max-width: 420px;
      box-shadow: 0 8px 24px rgba(0,0,0,0.18);
    }
    .login-box h2 { text-align:center; color:#4b0082; margin-bottom:18px; }
    .login-box label { display:block; margin-bottom:6px; color:#333; font-size:14px; }
    .login-box input { width:100%; padding:10px; margin-bottom:14px; border-radius:8px; border:1px solid #ccc; font-size:14px; }
    .login-box button { width:100%; padding:12px; background:#4b0082; color:#fff; border:none; border-radius:8px; font-weight:700; cursor:pointer; }
    .login-box button:hover { background:#5e0acc; }
    .msg { margin-bottom:12px; padding:10px; border-radius:6px; font-size:14px; }
    .msg.error { background:#ffe6e6; color:#8b0000; border:1px solid #f5c2c2; }
    .small { font-size:12px; color:#666; text-align:center; margin-top:8px; }
    @media (max-width:500px){ .login-box{ padding:20px; } }
  </style>
</head>
<body>
  <div class="login-box">
    <h2>Connexion</h2>

    <?php if (isset($error)): ?>
        <div class="msg error"><?= htmlspecialchars($error, ENT_QUOTES) ?></div>
    <?php endif; ?>

    <form action="login.php" method="POST" autocomplete="off">
      <label for="matricule">Matricule ou Email</label>
      <input type="text" id="matricule" name="matricule" placeholder="Entrez votre matricule ou email"
             value="<?= isset($identifier) ? htmlspecialchars($identifier, ENT_QUOTES) : '' ?>" required>

      <label for="password">Mot de passe</label>
      <input type="password" id="password" name="password" placeholder="Entrez votre mot de passe" required>

      <button type="submit">Se connecter</button>
    </form>

    <p class="small">Si vous n'avez pas de compte, demandez à l'administrateur de créer votre utilisateur.</p>
  </div>
</body>
</html>