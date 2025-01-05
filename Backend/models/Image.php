<?php
include_once __DIR__ . '/../config/db.php';

class Image {
    private $conn;

    public function __construct() {
        global $conn;
        $this->conn = $conn;
    }

    /**
     * Proverava da li URL slike već postoji u bazi
     */
    public function urlExists($url) {
        try {
            $stmt = $this->conn->prepare("SELECT COUNT(*) FROM system_images WHERE url = :url");
            $stmt->bindParam(':url', $url);
            $stmt->execute();

            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log("Greška pri proveri URL-a slike $url: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Briše staru sliku za dati module_uuid
     */
    public function deleteOldImage($moduleUuid) {
        try {
            // Fetch all images for the module_uuid
            $stmt = $this->conn->prepare("SELECT * FROM system_images WHERE module_uuid = :module_uuid");
            $stmt->bindParam(':module_uuid', $moduleUuid);
            $stmt->execute();
            $images = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
            // Delete the images from the database
            $stmt = $this->conn->prepare("DELETE FROM system_images WHERE module_uuid = :module_uuid");
            $stmt->bindParam(':module_uuid', $moduleUuid);
            $stmt->execute();
    
            // Remove the files from the uploads directory
            foreach ($images as $image) {
                $filePath = __DIR__ . '/../' . ltrim($image['url'], '/');
                if (file_exists($filePath)) {
                    unlink($filePath); // Delete the file
                    error_log("Deleted file: $filePath");
                } else {
                    error_log("File not found: $filePath");
                }
            }
    
            error_log("Deleted all images for module_uuid $moduleUuid.");
        } catch (PDOException $e) {
            error_log("Error in deleteOldImage(): " . $e->getMessage());
            throw $e;
        }
    }
    
    

    /**
     * Dodaje novu sliku u bazu
     */
    public function addImage($data) {
        try {
            $stmt = $this->conn->prepare("
                INSERT INTO system_images (uuid, module_uuid, module_for, url, description, date_created) 
                VALUES (:uuid, :module_uuid, :module_for, :url, :description, :date_created)
            ");
            $stmt->bindParam(':uuid', $data['uuid']);
            $stmt->bindParam(':module_uuid', $data['module_uuid']);
            $stmt->bindParam(':module_for', $data['module_for']);
            $stmt->bindParam(':url', $data['url']);
            $stmt->bindParam(':description', $data['description']);
            $stmt->bindParam(':date_created', $data['date_created']);
            $stmt->execute();
    
            error_log("Nova slika dodana za module_uuid {$data['module_uuid']} i module_for {$data['module_for']}.");
        } catch (PDOException $e) {
            error_log("Greška pri dodavanju nove slike: " . $e->getMessage());
            throw $e;
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
