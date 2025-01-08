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
            SELECT uuid, title, body, category, image_url, created_at, author_uuid 
            FROM news 
            ORDER BY created_at DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPaginatedNews($offset, $limit, $search = null) {
        $query = "
            SELECT SQL_CALC_FOUND_ROWS uuid, title, body, category, image_url, created_at, author_uuid
            FROM news
        ";
    
        if ($search) {
            $query .= " WHERE title LIKE :search ";
        }
    
        $query .= " ORDER BY created_at DESC LIMIT :offset, :limit";
    
        $stmt = $this->conn->prepare($query);
        if ($search) {
            $stmt->bindValue(':search', '%' . $search . '%');
        }
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    
        $stmt->execute();
        $news = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
        $totalRecords = $this->conn->query("SELECT FOUND_ROWS()")->fetchColumn();
    
        return ['news' => $news, 'total_records' => $totalRecords];
    }
    

    public function getNewsByUuid($uuid) {
        $stmt = $this->conn->prepare("
            SELECT uuid, title, body, category, image_url, created_at, author_uuid 
            FROM news 
            WHERE uuid = :uuid
        ");
        $stmt->execute([':uuid' => $uuid]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function createNews($newsData) {
        try {
            $stmt = $this->conn->prepare("
                INSERT INTO news (uuid, title, body, category, image_url, created_at, author_uuid)
                VALUES (:uuid, :title, :body, :category, :image_url, NOW(), :author_uuid)
            ");
            $stmt->execute($newsData);
            return true;
        } catch (PDOException $e) {
            error_log("Error in createNews: " . $e->getMessage());
            return false;
        }
    }

    public function deleteNews($uuid, $authorUuid) {
        try {
            // Provjera vlasništva nad vijesti
            $stmt = $this->conn->prepare("
                SELECT COUNT(*) 
                FROM news 
                WHERE uuid = :uuid AND author_uuid = :author_uuid
            ");
            $stmt->execute([':uuid' => $uuid, ':author_uuid' => $authorUuid]);
            if ($stmt->fetchColumn() === 0) {
                return false; // Nije vlasnik
            }

            // Brisanje vijesti
            $stmt = $this->conn->prepare("DELETE FROM news WHERE uuid = :uuid");
            $stmt->execute([':uuid' => $uuid]);
            return true;
        } catch (PDOException $e) {
            error_log("Error in deleteNews: " . $e->getMessage());
            return false;
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
