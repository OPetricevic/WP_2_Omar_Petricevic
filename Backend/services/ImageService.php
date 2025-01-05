<?php
include_once __DIR__ . '/../models/Image.php';

class ImageService {
    private $imageModel;

    public function __construct() {
        $this->imageModel = new Image();
    }

    public function addImage($file, $moduleUuid, $moduleFor, $description = null) {
        try {
            // Validate the file upload
            if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
                return ['status' => 400, 'message' => 'Invalid file upload.'];
            }
    
            // Define the upload directory
            $uploadDir = __DIR__ . '/../uploads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
    
            // Generate a unique filename
            $fileName = uniqid() . '-' . basename($file['name']);
            $targetFilePath = $uploadDir . $fileName;
    
            // Validate file type
            $fileType = pathinfo($targetFilePath, PATHINFO_EXTENSION);
            $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
            if (!in_array(strtolower($fileType), $allowedTypes)) {
                return ['status' => 400, 'message' => 'Unsupported file type.'];
            }
    
            // Move the uploaded file to the target directory
            if (!move_uploaded_file($file['tmp_name'], $targetFilePath)) {
                return ['status' => 500, 'message' => 'Failed to save the uploaded file.'];
            }
    
            // Prepare image data for the database
            $imageData = [
                'uuid' => generateUuid(),
                'module_uuid' => $moduleUuid,
                'module_for' => $moduleFor, // Include module_for
                'url' => '/uploads/' . $fileName,
                'description' => $description,
                'date_created' => date('Y-m-d H:i:s')
            ];
    
            // Delete the old image if it exists
            $this->deleteOldImage($moduleUuid, $moduleFor);
    
            // Insert the new image into the database
            $this->imageModel->addImage($imageData);
    
            return [
                'status' => 201,
                'message' => 'Image uploaded successfully.',
                'file_url' => $imageData['url']
            ];
        } catch (Exception $e) {
            error_log("Error in addImage(): " . $e->getMessage());
            return ['status' => 500, 'message' => 'Internal server error.'];
        }
    }
    

    public function deleteOldImage($moduleUuid, $moduleFor) {
        try {
            $this->imageModel->deleteOldImage($moduleUuid, $moduleFor);
            error_log("Old image for module_uuid $moduleUuid and module_for $moduleFor deleted successfully.");
        } catch (Exception $e) {
            error_log("Error in deleteOldImage(): " . $e->getMessage());
            throw $e;
        }
    }

    public function getAllImages($moduleUuid, $moduleFor) {
        return $this->imageModel->getAllImages($moduleUuid, $moduleFor);
    }

    public function getImageByUuid($uuid) {
        return $this->imageModel->getImageByUuid($uuid);
    }

    public function updateImageDescription($uuid, $description) {
        try {
            $this->imageModel->updateImageDescription($uuid, $description);

            return ['status' => 200, 'message' => 'Image description updated successfully.'];
        } catch (Exception $e) {
            error_log("Error in updateImageDescription(): " . $e->getMessage());
            return ['status' => 500, 'message' => 'Internal server error.'];
        }
    }

    public function deleteImage($uuid) {
        try {
            // Retrieve the image details to get the file path
            $image = $this->imageModel->getImageByUuid($uuid);
    
            if (!$image) {
                error_log("Image not found for UUID: $uuid.");
                return ['status' => 404, 'message' => 'Image not found.'];
            }
    
            // Delete the file from the filesystem
            $filePath = __DIR__ . '/../' . ltrim($image['url'], '/');
            if (file_exists($filePath)) {
                unlink($filePath);
                error_log("Deleted file: $filePath");
            } else {
                error_log("File not found: $filePath");
            }
    
            // Delete the image from the database
            $this->imageModel->deleteImage($uuid);
    
            return ['status' => 200, 'message' => 'Image deleted successfully.'];
        } catch (Exception $e) {
            error_log("Error in deleteImage(): " . $e->getMessage());
            return ['status' => 500, 'message' => 'Internal server error.'];
        }
    }
    
}
?>
