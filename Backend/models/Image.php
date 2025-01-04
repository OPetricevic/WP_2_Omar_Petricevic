<?php
include_once __DIR__ . '/../config/db.php';

class Image {
    private $conn;

    public function __construct() {
        global $conn;
        $this->conn = $conn;
    }

    /**
     * Briše staru sliku za dati module_uuid
     */
    public function deleteOldImage($moduleUuid) {
        try {
            $stmt = $this->conn->prepare("DELETE FROM system_images WHERE module_uuid = :module_uuid");
            $stmt->bindParam(':module_uuid', $moduleUuid);
            $stmt->execute();

            // Log poruka za uspešno brisanje
            error_log("Stara slika za module_uuid $moduleUuid je obrisana.");
        } catch (PDOException $e) {
            error_log("Greška pri brisanju stare slike: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Dodaje novu sliku u bazu
     */
    public function addImage($data) {
        try {
            $imageData = [
                'uuid' => generateUuid(),
                'module_uuid' => $data['module_uuid'],
                'url' => $data['url'],
                'description' => $data['description'] ?? null,
                'date_created' => date('Y-m-d H:i:s'),
            ];
    
            // Delete any existing image for the same module_uuid
            $this->imageModel->deleteOldImage($data['module_uuid']);
    
            // Add the new image
            $this->imageModel->addImage($imageData);
    
            return ['status' => 201, 'message' => 'Image created successfully.'];
        } catch (Exception $e) {
            error_log("Error in addImage(): " . $e->getMessage());
            return ['status' => 500, 'message' => 'Internal server error.'];
        }
    }
    

    /**
     * Proverava da li postoji slika za dati module_uuid
     */
    public function imageExists($moduleUuid) {
        try {
            $stmt = $this->conn->prepare("SELECT COUNT(*) FROM system_images WHERE module_uuid = :module_uuid");
            $stmt->bindParam(':module_uuid', $moduleUuid);
            $stmt->execute();

            // Vraća true ako postoji slika, false inače
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log("Greška pri proveri slike za module_uuid $moduleUuid: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Vraća sve slike za određeni module_uuid
     */
    public function getAllImages($moduleUuid) {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM system_images WHERE module_uuid = :module_uuid");
            $stmt->bindParam(':module_uuid', $moduleUuid);
            $stmt->execute();

            // Vraća niz sa svim slikama
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Greška pri dobavljanju slika za module_uuid $moduleUuid: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Ažurira opis slike
     */
    public function updateImageDescription($imageUuid, $description) {
        try {
            $stmt = $this->conn->prepare("
                UPDATE system_images 
                SET description = :description, date_updated = NOW()
                WHERE uuid = :uuid
            ");
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':uuid', $imageUuid);
            $stmt->execute();

            // Log poruka za uspešno ažuriranje
            error_log("Opis slike za uuid $imageUuid je ažuriran.");
        } catch (PDOException $e) {
            error_log("Greška pri ažuriranju opisa slike za uuid $imageUuid: " . $e->getMessage());
            throw $e;
        }
    }

    public function getImageByUuid($uuid) {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM system_images WHERE uuid = :uuid");
            $stmt->bindParam(':uuid', $uuid);
            $stmt->execute();
    
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Greška pri dobavljanju slike za uuid $uuid: " . $e->getMessage());
            throw $e;
        }
    }
    
    public function deleteImage($uuid) {
        try {
            $stmt = $this->conn->prepare("DELETE FROM system_images WHERE uuid = :uuid");
            $stmt->bindParam(':uuid', $uuid);
            $stmt->execute();
    
            error_log("Slika sa uuid $uuid je obrisana.");
        } catch (PDOException $e) {
            error_log("Greška pri brisanju slike: " . $e->getMessage());
            throw $e;
        }
    }
    
}
?>
