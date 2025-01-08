<?php
include_once __DIR__ . '/../models/News.php';

class NewsService {
    private $conn;
    private $newsModel;

    public function __construct() {
        global $conn; // Use the globally defined $conn
        $this->conn = $conn;
        $this->newsModel = new News(); // Initialize the News model
    }

    public function getAllNews() {
        try {
            return ['status' => 200, 'data' => $this->newsModel->getAllNews()];
        } catch (Exception $e) {
            error_log("Error in getAllNews: " . $e->getMessage());
            return ['status' => 500, 'message' => 'Internal server error.'];
        }
    }

    public function getPaginatedNews($page, $pageSize, $search = null) {
        try {
            $offset = ($page - 1) * $pageSize;
    
            $query = "
                SELECT uuid, title, body, category, created_at, author_uuid 
                FROM news 
            ";
    
            if ($search) {
                $query .= "WHERE title LIKE :search ";
            }
    
            $query .= "ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
    
            $stmt = $this->conn->prepare($query);
    
            if ($search) {
                $stmt->bindValue(':search', '%' . $search . '%', PDO::PARAM_STR);
            }
    
            $stmt->bindValue(':limit', $pageSize, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    
            $stmt->execute();
            $newsList = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
            foreach ($newsList as &$news) {
                $news['image'] = $this->getNewsImage($news['uuid']);
            }
    
            $totalCount = $this->getTotalCount($search);
    
            return [
                'total_records' => $totalCount,
                'page_size' => $pageSize,
                'current_page' => $page,
                'news' => $newsList
            ];
        } catch (Exception $e) {
            error_log("Error in getPaginatedNews: " . $e->getMessage());
            return ['error' => 'Internal server error.'];
        }
    }
    
    

    public function createNews($authorUuid, $title, $body, $category) {
        try {
            // Use the generateUuid function to create a proper UUID
            $uuid = generateUuid(); 
            $newsData = [
                ':uuid' => $uuid,
                ':title' => $title,
                ':body' => $body,
                ':category' => $category,
                ':author_uuid' => $authorUuid,
            ];
    
            // Call the News model to insert the news
            if ($this->newsModel->createNews($newsData)) {
                return [
                    'uuid' => $uuid,
                    'message' => 'News created successfully.'
                ];
            }
    
            return [
                'status' => 500,
                'message' => 'Failed to create news.'
            ];
        } catch (Exception $e) {
            error_log("Error in createNews: " . $e->getMessage());
            return [
                'status' => 500,
                'message' => 'Internal server error.'
            ];
        }
    }
    
    

    private function getTotalCount($search = null) {
        $query = "SELECT COUNT(*) FROM news ";
        if ($search) {
            $query .= "WHERE title LIKE :search";
        }

        $stmt = $this->conn->prepare($query);

        if ($search) {
            $stmt->bindValue(':search', '%' . $search . '%', PDO::PARAM_STR);
        }

        $stmt->execute();
        return $stmt->fetchColumn();
    }

    private function getNewsImage($newsUuid) {
        $stmt = $this->conn->prepare("
            SELECT uuid, url, description 
            FROM system_images 
            WHERE module_uuid = :module_uuid AND module_for = 'news'
        ");
        $stmt->execute([':module_uuid' => $newsUuid]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getNewsByUuid($uuid) {
        try {
            $news = $this->newsModel->getNewsByUuid($uuid);
            if (!$news) {
                return null; // News not found
            }
    
            // Fetch associated image
            $news['image'] = $this->getNewsImage($uuid);
    
            return $news;
        } catch (Exception $e) {
            error_log("Error in getNewsByUuid: " . $e->getMessage());
            throw $e; 
        }
    }

    public function deleteNews($newsUuid, $authorUuid) {
        try {
            // Begin a transaction
            $this->conn->beginTransaction();
    
            // Check if the news item exists and belongs to the current user
            if (!$this->newsModel->newsExists($newsUuid)) {
                $this->conn->rollBack();
                return ['status' => 404, 'message' => 'News not found'];
            }
    
            // Check ownership
            $news = $this->newsModel->getNewsByUuid($newsUuid);
            if ($news['author_uuid'] !== $authorUuid) {
                $this->conn->rollBack();
                return ['status' => 403, 'message' => 'You do not have permission to delete this news'];
            }
    
            // Attempt to delete associated system image
            $stmt = $this->conn->prepare("
                SELECT uuid, url FROM system_images
                WHERE module_uuid = :module_uuid AND module_for = 'news'
            ");
            $stmt->execute([':module_uuid' => $newsUuid]);
            $image = $stmt->fetch(PDO::FETCH_ASSOC);
    
            if ($image) {
                // Delete the file locally
                $filePath = __DIR__ . '/../uploads/' . basename($image['url']);
                if (file_exists($filePath)) {
                    if (!unlink($filePath)) {
                        $this->conn->rollBack();
                        return ['status' => 500, 'message' => 'Failed to delete associated image file'];
                    }
                } else {
                    error_log("File not found: $filePath");
                }
    
                // Delete the record from the database
                $stmt = $this->conn->prepare("
                    DELETE FROM system_images
                    WHERE uuid = :uuid
                ");
                $stmt->execute([':uuid' => $image['uuid']]);
            }
    
            // Proceed to delete the news
            $deleted = $this->newsModel->deleteNews($newsUuid, $authorUuid);
            if (!$deleted) {
                $this->conn->rollBack();
                return ['status' => 500, 'message' => 'Failed to delete news'];
            }
    
            // Commit the transaction
            $this->conn->commit();
    
            return ['status' => 200, 'message' => 'News and associated image deleted successfully'];
        } catch (Exception $e) {
            // Rollback transaction on error
            $this->conn->rollBack();
            error_log("Error in deleteNews: " . $e->getMessage());
            return ['status' => 500, 'message' => 'Internal server error'];
        }
    }
    
    
    
    
}
