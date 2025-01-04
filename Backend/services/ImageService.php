<?php
include_once __DIR__ . '/../models/Image.php';

class ImageService {
    private $imageModel;

    public function __construct() {
        $this->imageModel = new Image();
    }

    public function addImage($data) {
        try {
            // Provjera da li URL već postoji
            if ($this->imageModel->urlExists($data['url'])) {
                return ['status' => 409, 'message' => 'Image with this URL already exists.'];
            }

            // Dodavanje slike
            $imageData = [
                'uuid' => generateUuid(),
                'module_uuid' => $data['module_uuid'],
                'url' => $data['url'],
                'description' => $data['description'] ?? null,
                'date_created' => date('Y-m-d H:i:s')
            ];

            $this->imageModel->addImage($imageData);

            return ['status' => 201, 'message' => 'Image created successfully.'];
        } catch (Exception $e) {
            error_log("Error in addImage(): " . $e->getMessage());
            return ['status' => 500, 'message' => 'Internal server error.'];
        }
    }

    public function getAllImages($moduleUuid) {
        $imageModel = new Image();
        return $imageModel->getAllImages($moduleUuid);
    }
    
    public function getImageByUuid($uuid) {
        $imageModel = new Image();
        return $imageModel->getImageByUuid($uuid);
    }
    
    public function updateImageDescription($uuid, $description) {
        try {
            $imageModel = new Image();
            $imageModel->updateImageDescription($uuid, $description);
    
            return ['status' => 200, 'message' => 'Image description updated successfully.'];
        } catch (Exception $e) {
            return ['status' => 500, 'message' => 'Internal server error.'];
        }
    }
    
    public function deleteImage($uuid) {
        try {
            $imageModel = new Image();
            $imageModel->deleteImage($uuid);
    
            return ['status' => 200, 'message' => 'Image deleted successfully.'];
        } catch (Exception $e) {
            return ['status' => 500, 'message' => 'Internal server error.'];
        }
    }
    
}
?>
