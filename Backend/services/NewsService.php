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
}
