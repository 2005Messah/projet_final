<?php
session_start();

// Vérifier que l'utilisateur est un encadrant
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'encadrant') {
    header("Location: login.php");
    exit;
}

$conn = new mysqli("localhost", "root", "", "suivi_stagiaires");
if ($conn->connect_error) die("Erreur DB: " . $conn->connect_error);

// Nom utilisateur connecté
$username = isset($_SESSION['username']) ? $_SESSION['username'] : "Encadrant";

// --- Gestion des actions Accepter / Refuser ---
if (isset($_GET['id']) && isset($_GET['action'])) {
    $id = intval($_GET['id']);
    $action = $_GET['action']; // "accepter" ou "refuser"

    if ($action == 'accepter') {
        // Récupérer la demande
        $result = $conn->query("SELECT * FROM demandes_stage WHERE id=$id");
        if ($result && $result->num_rows > 0) {
            $demande = $result->fetch_assoc();

            // Récupérer infos
            $nom = $conn->real_escape_string($demande['nom']);
            $prenom = $conn->real_escape_string($demande['prenom']);
            $sexe = $conn->real_escape_string($demande['sexe']);
            $email = $conn->real_escape_string($demande['email']);
            $telephone = $conn->real_escape_string($demande['telephone']);
            $lieu = $conn->real_escape_string($demande['lieu']);
            // Récupérer filière avec vérification si la colonne existe
            $filiere = isset($demande['filiere']) ? $conn->real_escape_string($demande['filiere']) : '';
            $photo = !empty($demande['photo']) ? $conn->real_escape_string($demande['photo']) : "";
            
            // Corriger le chemin de la photo si nécessaire
            if (!empty($photo) && strpos($photo, 'uploads/') === false) {
                $photo = "uploads/" . $photo;
            }

            // Mot de passe saisi (ou défaut)
            $password = isset($_GET['password']) ? $_GET['password'] : "12345";
            $hashedPwd = password_hash($password, PASSWORD_DEFAULT);

            // Vérifier si déjà stagiaire
            $check = $conn->query("SELECT id FROM stagiaires WHERE email='$email'");
            if ($check && $check->num_rows == 0) {
                // Insertion avec sexe, lieu et filière
                $sql = "INSERT INTO stagiaires (sexe, nom, prenom, telephone, email, mot_de_passe, photo, lieu, filiere) 
                        VALUES ('$sexe', '$nom', '$prenom', '$telephone', '$email', '$hashedPwd', '$photo', '$lieu', '$filiere')";
                if ($conn->query($sql)) {
                    // Succès - le stagiaire est maintenant dans la table stagiaires
                } else {
                    die("Erreur lors de l'ajout du stagiaire : " . $conn->error);
                }
            } else {
                // Déjà existant → mise à jour mot de passe, sexe, lieu et filière
                $conn->query("UPDATE stagiaires SET mot_de_passe='$hashedPwd', sexe='$sexe', lieu='$lieu', filiere='$filiere' WHERE email='$email'");
            }

            // CRÉATION DU COMPTE UTILISATEUR POUR LA CONNEXION
            // Générer un matricule unique
            $matricule = "STG" . date('Y') . str_pad($id, 4, '0', STR_PAD_LEFT);
            
            // Vérifier si l'utilisateur existe déjà
            $check_user = $conn->query("SELECT id FROM utilisateurs WHERE email='$email' OR matricule='$matricule'");
            if ($check_user && $check_user->num_rows == 0) {
                // Insérer dans la table utilisateurs
                $sql_user = "INSERT INTO utilisateurs (nom, matricule, mot_de_passe, role, email) 
                            VALUES ('$nom $prenom', '$matricule', '$hashedPwd', 'stagiaire', '$email')";
                if (!$conn->query($sql_user)) {
                    die("Erreur lors de la création de l'utilisateur : " . $conn->error);
                }
            } else {
                // Mettre à jour l'utilisateur existant
                $conn->query("UPDATE utilisateurs SET mot_de_passe='$hashedPwd', nom='$nom $prenom' WHERE email='$email'");
            }

            // Mettre à jour la demande
            $conn->query("UPDATE demandes_stage SET etat='Validée' WHERE id=$id");
        }
    } elseif ($action == 'refuser') {
        $conn->query("UPDATE demandes_stage SET etat='Refusée' WHERE id=$id");
    }

    header("Location: dashboard_encadrant.php");
    exit;
}

// --- Récupérer toutes les demandes ---
$demandes = $conn->query("SELECT * FROM demandes_stage ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Dashboard Encadrant</title>
<style>
body{font-family:Arial;margin:0;padding:0;background:#f4f4f4;}
header{background:#6a1b9a;color:#fff;padding:15px;text-align:center;}
.container{width:95%;margin:20px auto;}
.card{background:#fff;padding:20px;border-radius:10px;margin-bottom:20px;box-shadow:0 2px 6px rgba(0,0,0,0.1);}
.card h3{margin-top:0;}
a{text-decoration:none;color:#6a1b9a;font-weight:bold;}
table{width:100%;border-collapse:collapse;margin-top:10px;}
th,td{border:1px solid #ccc;padding:8px;text-align:left;}
th{background:#9c27b0;color:#fff;}
img{max-width:80px;border-radius:8px;}
.btn{background:#6a1b9a;color:#fff;padding:5px 10px;border-radius:5px;text-decoration:none;font-weight:bold;margin:2px;}
.btn:hover{background:#9c27b0;}
#modal{display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);align-items:center;justify-content:center;}
#modal div{background:#fff;padding:20px;border-radius:10px;width:300px;text-align:center;}
#modal input{width:80%;padding:5px;margin:10px 0;}
#modal button{padding:5px 10px;border:none;border-radius:5px;color:#fff;background:#6a1b9a;font-weight:bold;}
#modal button.cancel{background:#ccc;color:#000;margin-left:10px;}
.cards-container {
    display: flex;
    justify-content: space-between;
    gap: 20px;
    flex-wrap: wrap;
}

.cards-container .card {
    flex: 1;
    min-width: 250px;
    text-align: center;
}

/* Styles pour la ligne horizontale des liens rapides */
.links-container {
    display: flex;
    justify-content: space-between;
    gap: 15px;
    margin-bottom: 20px;
    flex-wrap: wrap;
}

.links-container .card {
    flex: 1;
    min-width: 200px;
    text-align: center;
    padding: 15px;
}

.links-container .btn {
    display: inline-block;
    margin-top: 10px;
}
</style>
</head>
<body>

<header>
<h1>Dashboard Encadrant</h1>
<p>Bienvenue, <?php echo htmlspecialchars($username); ?> | <a href="logout.php" style="color:white;">Se déconnecter</a></p>
</header>

<div class="container">
    <!-- Ligne horizontale des liens rapides -->
    <div class="links-container">
        <div class="card">
            <h3>📋 Attribuer un stage</h3>
            <p><a href="ajouter_stage.php" class="btn">Attribuer un stage</a></p>
        </div>
        <div class="card">
            <h3>📍 Pointer les présences</h3>
            <p><a href="pointer_presence.php" class="btn">Pointer les présences</a></p>
        </div>
        <div class="card">
            <h3>📊 Récapitulatif des présences</h3>
            <p><a href="recap_presences.php" class="btn">Voir le récapitulatif</a></p>
        </div>
        <!-- Ajoutez cette carte dans le container après les liens rapides existants -->
<div class="card">
    <h3>📄 Générer une attestation</h3>
    <p><a href="choisir_stagiaire_attestation.php" class="btn">Générer attestation</a>
</a></p>
</div>
    </div>

    <!-- Liste des demandes -->
    <div class="card">
        <h3>📋 Demandes de stage reçues</h3>
        <table>
            <tr>
                <th>Photo</th><th>Nom</th><th>Prénom</th><th>Email</th><th>Sexe</th>
                <th>Lieu</th><th>Filière</th><th>Téléphone</th><th>CV</th><th>Lettre</th><th>Certificat</th><th>Dates</th><th>État</th><th>Action</th>
            </tr>

            <?php if($demandes && $demandes->num_rows>0){
                while($row = $demandes->fetch_assoc()){ 
                    $etat = isset($row['etat']) ? $row['etat'] : 'En attente';
                    // CORRECTION COMPLÈTE : Format WhatsApp avec indicatif Cameroun
                    $phone = preg_replace('/\D/','',$row['telephone']);
                    
                    // Conversion pour WhatsApp
                    if (strlen($phone) == 9 && substr($phone, 0, 1) == '6') {
                        // Format: 695203731 -> 237695203731
                        $whatsapp_phone = '237' . $phone;
                    } elseif (strlen($phone) == 10 && substr($phone, 0, 2) == '65') {
                        // Format: 6952037310 -> 237695203731
                        $whatsapp_phone = '237' . substr($phone, 0, 9);
                    } elseif (strlen($phone) == 12 && substr($phone, 0, 3) == '237') {
                        // Déjà au bon format
                        $whatsapp_phone = $phone;
                    } else {
                        // Format par défaut
                        $whatsapp_phone = '237' . substr($phone, -9);
                    }
                    
                    // Vérifier si la colonne filiere existe
                    $filiere = isset($row['filiere']) ? $row['filiere'] : 'Non spécifiée';
                ?>
            <tr>
                <td><?php if(!empty($row['photo'])){ ?><img src="uploads/<?php echo $row['photo']; ?>"><?php } ?></td>
                <td><?php echo htmlspecialchars($row['nom']); ?></td>
                <td><?php echo htmlspecialchars($row['prenom']); ?></td>
                <td><?php echo htmlspecialchars($row['email']); ?></td>
                <td><?php echo htmlspecialchars($row['sexe']); ?></td>
                <td><?php echo htmlspecialchars($row['lieu']); ?></td>
                <td><?php echo htmlspecialchars($filiere); ?></td>
                <td><?php echo htmlspecialchars($row['telephone']); ?></td>
                <td><?php if(!empty($row['cv'])){ ?><a href="uploads/<?php echo $row['cv']; ?>" target="_blank">CV</a><?php } ?></td>
                <td><?php if(!empty($row['lettre'])){ ?><a href="uploads/<?php echo $row['lettre']; ?>" target="_blank">Lettre</a><?php } ?></td>
                <td><?php if(!empty($row['certificat'])){ ?><a href="uploads/<?php echo $row['certificat']; ?>" target="_blank">Certificat</a><?php } ?></td>
                <td><?php echo $row['date_debut'].' → '.$row['date_fin']; ?></td>
                <td><?php echo $etat; ?></td>
                <td>
                    <?php if($etat=='En attente'){ ?>
                    <a href="dashboard_encadrant.php?id=<?php echo $row['id']; ?>&action=accepter" 
                       class="btn" 
                       data-phone="<?php echo $whatsapp_phone; ?>" 
                       data-nom="<?php echo htmlspecialchars($row['prenom'].' '.$row['nom']); ?>"
                       data-email="<?php echo htmlspecialchars($row['email']); ?>"
                       data-id="<?php echo $row['id']; ?>">
                       ✅ Accepter
                    </a>
                    <a href="dashboard_encadrant.php?id=<?php echo $row['id']; ?>&action=refuser" class="btn">❌ Refuser</a>
                    <?php } else {
                        echo "-";
                    } ?>
                </td>
            </tr>
            <?php } } else { ?>
            <tr><td colspan="14" style="text-align:center;">Aucune demande reçue</td></tr>
            <?php } ?>
        </table>
    </div>

    <div class="card">
        <h3>🔗 Liens rapides</h3>
        <p><a href="test2.php">📋 Voir la liste des stagiaires validés</a></p>
    </div>

</div>

<!-- Modale mot de passe -->
<div id="modal">
  <div>
    <h3>Attribuer un mot de passe</h3>
    <input type="text" id="newPassword" placeholder="Mot de passe stagiaire">
    <br>
    <button id="submitPassword">Envoyer</button>
    <button class="cancel" onclick="closeModal()">Annuler</button>
  </div>
</div>

<script>
document.querySelectorAll('.btn').forEach(function(btn){
    if(btn.textContent.includes('Accepter')){
        btn.addEventListener('click', function(e){
            e.preventDefault();
            window.currentAcceptUrl = this.href;
            document.querySelectorAll('.btn').forEach(b=>b.removeAttribute('data-current'));
            btn.setAttribute('data-current','true');
            document.getElementById('modal').style.display = 'flex';
        });
    }
});

document.getElementById('submitPassword').addEventListener('click', function(){
    const pwd = document.getElementById('newPassword').value.trim();
    if(pwd===''){ alert('Veuillez saisir un mot de passe'); return; }

    const btn = document.querySelector('.btn[data-current="true"]');
    const phone = btn ? btn.getAttribute('data-phone') : '';
    const nom = btn ? btn.getAttribute('data-nom') : '';
    const email = btn ? btn.getAttribute('data-email') : '';
    const demandeId = btn ? btn.getAttribute('data-id') : '';
    
    // Générer un matricule similaire à celui qui sera créé côté serveur
    const matricule = "STG" + new Date().getFullYear() + String(demandeId).padStart(4, '0');

    if(phone){
        // Message pour WhatsApp
        const message = `Bonjour ${nom},

Votre compte stagiaire a été validé.

📋 VOS IDENTIFIANTS DE CONNEXION :
Matricule : ${matricule}
Email : ${email}
Mot de passe : ${pwd}

Vous pouvez maintenant vous connecter sur la plateforme avec votre email ou matricule.`;
        
        // CORRECTION : Utiliser l'URL qui fonctionne pour pré-remplir le message
        // Cette URL ouvre WhatsApp avec le numéro et le message déjà saisi
        const whatsappUrl = `https://wa.me/${phone}?text=${encodeURIComponent(message)}`;
        
        console.log('Numéro WhatsApp:', phone);
        console.log('URL WhatsApp:', whatsappUrl);
        
        // Essayer d'abord avec l'URL standard
        let newWindow = window.open(whatsappUrl, '_blank');
        
        // Si ça ne fonctionne pas, essayer avec l'URL alternative
        if (!newWindow) {
            const alternativeUrl = `https://api.whatsapp.com/send?phone=${phone}&text=${encodeURIComponent(message)}`;
            newWindow = window.open(alternativeUrl, '_blank');
        }
        
        // Si toujours pas, essayer avec le protocole WhatsApp
        if (!newWindow) {
            const whatsappProtocol = `whatsapp://send?phone=${phone}&text=${encodeURIComponent(message)}`;
            window.location.href = whatsappProtocol;
            
            // Retourner à la page après un délai
            setTimeout(function() {
                const url = window.currentAcceptUrl + '&password=' + encodeURIComponent(pwd);
                window.location.href = url;
            }, 3000);
            return;
        }
        
        // Si une fenêtre s'est ouverte, continuer normalement
        setTimeout(function() {
            const url = window.currentAcceptUrl + '&password=' + encodeURIComponent(pwd);
            window.location.href = url;
        }, 2000);
        
    } else {
        // Si pas de numéro, continuer normalement
        const url = window.currentAcceptUrl + '&password=' + encodeURIComponent(pwd);
        window.location.href = url;
    }
});

function closeModal(){
    document.getElementById('modal').style.display = 'none';
    document.getElementById('newPassword').value = '';
}

// Fonction de débogage pour vérifier les numéros
function debugPhoneNumbers() {
    document.querySelectorAll('.btn[data-phone]').forEach(btn => {
        console.log('Numéro:', btn.getAttribute('data-phone'), 'Nom:', btn.getAttribute('data-nom'));
    });
}

// Appeler la fonction de débogage au chargement
window.addEventListener('load', debugPhoneNumbers);
</script>

</body>
</html>

<?php $conn->close(); ?>