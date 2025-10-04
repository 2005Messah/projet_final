<?php
use PHPUnit\Framework\TestCase;

class StageTest extends TestCase
{
    public function testAjoutStageReussi()
    {
        $stage = new StageManager();
        $result = $stage->ajouterStage(1, "2025-09-01", "2025-12-01", "Entreprise X", "Développement Web");
        $this->assertTrue($result, "L'ajout du stage aurait dû réussir.");
    }

    public function testAjoutStageEchec()
    {
        $stage = new StageManager();
        $result = $stage->ajouterStage(1, "2025-09-01", "", "Entreprise X", "Développement Web");
        $this->assertFalse($result, "L'ajout du stage aurait dû échouer.");
    }
}
?>
<?php
public function ajouterStage($id_stagiaire, $date_debut, $date_fin, $etablissement, $theme) {
    if (empty($id_stagiaire) || empty($date_debut) || empty($date_fin) || empty($etablissement) || empty($theme)) {
        return false; // Échec si un champ obligatoire est vide
    }

    // Simulation d'insertion dans la base
    return true; // Succès si tous les champs sont remplis
}
?>