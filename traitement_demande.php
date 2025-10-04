<?php
session_start();


// Connexion à la base de données
$conn = new mysqli("localhost", "root", "", "suivi_stagiaires");
if ($conn->connect_error) {
    die("Erreur de connexion : " . $conn->connect_error);
}

// Dossier d’upload
$uploadDir = "uploads/";
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// Fonction pour uploader un fichier PDF
function uploadPDF($fileInput, $uploadDir) {
    if (isset($_FILES[$fileInput]) && $_FILES[$fileInput]['error'] == 0) {
        $fileName = time() . "_" . basename($_FILES[$fileInput]['name']);
        $filePath = $uploadDir . $fileName;

        $fileExt = strtolower(pathinfo($_FILES[$fileInput]['name'], PATHINFO_EXTENSION));
        if ($fileExt !== "pdf") {
            die("Erreur : Le fichier " . $fileInput . " doit être au format PDF.");
        }

        if (move_uploaded_file($_FILES[$fileInput]['tmp_name'], $filePath)) {
            return $fileName;
        } else {
            die("Erreur lors de l'upload du fichier " . $fileInput);
        }
    }
    return null;
}

// Fonction pour uploader une image
function uploadImage($fileInput, $uploadDir) {
    if (isset($_FILES[$fileInput]) && $_FILES[$fileInput]['error'] == 0) {
        $fileName = time() . "_" . basename($_FILES[$fileInput]['name']);
        $filePath = $uploadDir . $fileName;

        $fileExt = strtolower(pathinfo($_FILES[$fileInput]['name'], PATHINFO_EXTENSION));
        $allowed = ["jpg", "jpeg", "png"];
        if (!in_array($fileExt, $allowed)) {
            die("Erreur : La photo doit être au format JPG ou PNG.");
        }

        if (move_uploaded_file($_FILES[$fileInput]['tmp_name'], $filePath)) {
            return $fileName;
        } else {
            die("Erreur lors de l'upload de la photo.");
        }
    }
    return null;
}

// Récupération des données du formulaire
$nom        = $conn->real_escape_string($_POST['nom']);
$prenom     = $conn->real_escape_string($_POST['prenom']);
$email      = $conn->real_escape_string($_POST['email']);
$sexe       = $conn->real_escape_string($_POST['sexe']);
$lieu       = $conn->real_escape_string($_POST['lieu']);
$filiere    = $conn->real_escape_string($_POST['filiere']);
$telephone  = $conn->real_escape_string($_POST['telephone']);
$date_debut = $conn->real_escape_string($_POST['date_debut']);
$date_fin   = $conn->real_escape_string($_POST['date_fin']);



// Upload des fichiers
$photo      = uploadImage("photo", $uploadDir);
$cv         = uploadPDF("cv", $uploadDir);
$lettre     = uploadPDF("lettre", $uploadDir);
$certificat = uploadPDF("certificat", $uploadDir);

// Insertion en BDD
$sql = "INSERT INTO demandes_stage 
        (nom, prenom, email, sexe, photo, lieu, filiere, telephone, date_debut, date_fin, cv, lettre, certificat) 
        VALUES 
        ('$nom', '$prenom', '$email', '$sexe', '$photo', '$lieu', '$filiere', '$telephone', '$date_debut', '$date_fin', '$cv', '$lettre', '$certificat')";

if ($conn->query($sql) === TRUE) {
    echo "<script>alert('✅ Votre demande a été soumise avec succès !'); window.location.href='demande_stage.php';</script>";
} else {
    echo "Erreur : " . $conn->error;
}

$conn->close();
?>
