<?php
include_once __DIR__ . '/../config/db.php';

class News {
    private $conn;

    public function __construct() {
        global $conn;
        $this->conn = $conn;
    }
    

    public function getAllNews() {
        $stmt = $this->conn->prepare("
            SELECT uuid, title, body, category, created_at, author_uuid 
            FROM news 
            ORDER BY created_at DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPaginatedNews($page, $pageSize, $search = null) {
        try {
            $offset = ($page - 1) * $pageSize; // Calculate offset based on the current page
            
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
    
    

    public function getNewsByUuid($uuid) {
        $stmt = $this->conn->prepare("
            SELECT uuid, title, body, category, created_at, author_uuid 
            FROM news 
            WHERE uuid = :uuid
        ");
        $stmt->execute([':uuid' => $uuid]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    

    public function createNews($newsData) {
        try {
            $stmt = $this->conn->prepare("
                INSERT INTO news (uuid, title, body, category, created_at, author_uuid)
                VALUES (:uuid, :title, :body, :category, NOW(), :author_uuid)
            ");
            $stmt->execute($newsData);
            return true;
        } catch (PDOException $e) {
            error_log("Error in createNews: " . $e->getMessage());
            return false;
        }
    }
   
    

    public function deleteNews($newsUuid, $authorUuid) {
        try {
            $stmt = $this->conn->prepare("
                DELETE FROM news
                WHERE uuid = :uuid AND author_uuid = :author_uuid
            ");
            $stmt->execute([':uuid' => $newsUuid, ':author_uuid' => $authorUuid]);
            return $stmt->rowCount() > 0; // Return true if a row was deleted
        } catch (PDOException $e) {
            error_log("Error in deleteNews: " . $e->getMessage());
            throw $e;
        }
    }
    

    public function newsExists($uuid) {
        $stmt = $this->conn->prepare("
            SELECT COUNT(*) 
            FROM news 
            WHERE uuid = :uuid
        ");
        $stmt->execute([':uuid' => $uuid]);
        return $stmt->fetchColumn() > 0;
    }
}
?>
