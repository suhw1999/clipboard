<?php
require_once dirname(__DIR__) . '/config.php';

class DatabaseManager {
    private static $instance = null;
    private $db;
    
    private function __construct() {
        $this->initDatabase();
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function initDatabase() {
        try {
            $dbPath = dirname(__DIR__) . '/' . DATABASE_FILE;
            $this->db = new PDO('sqlite:' . $dbPath);
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            
            $this->createTables();
            chmod($dbPath, FILE_PERMISSIONS);
        } catch (PDOException $e) {
            error_log('Database connection failed: ' . $e->getMessage());
            throw new Exception('数据库连接失败');
        }
    }
    
    private function createTables() {
        $sql = "
            CREATE TABLE IF NOT EXISTS clipboard_records (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                content TEXT NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ";
        
        $this->db->exec($sql);
    }
    
    public function getConnection() {
        return $this->db;
    }
    
    public function insertRecord($content) {
        $sql = "INSERT INTO clipboard_records (content) VALUES (?)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$content]);
    }
    
    public function getAllRecords() {
        $sql = "SELECT id, content, created_at FROM clipboard_records ORDER BY created_at DESC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * 获取分页记录
     * @param int $offset 偏移量
     * @param int $limit 每页数量
     * @return array
     */
    public function getRecordsPaginated($offset, $limit) {
        $sql = "SELECT id, content, created_at FROM clipboard_records ORDER BY created_at DESC LIMIT ? OFFSET ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$limit, $offset]);
        return $stmt->fetchAll();
    }
    
    public function getRecordById($id) {
        $sql = "SELECT id, content, created_at FROM clipboard_records WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    public function deleteRecord($id) {
        $sql = "DELETE FROM clipboard_records WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id]);
    }
    
    public function getRecordCount() {
        $sql = "SELECT COUNT(*) as count FROM clipboard_records";
        $stmt = $this->db->query($sql);
        $result = $stmt->fetch();
        return $result['count'];
    }
    
    public function recordExists($id) {
        $sql = "SELECT COUNT(*) as count FROM clipboard_records WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        $result = $stmt->fetch();
        return $result['count'] > 0;
    }
    
    public function clearAllRecords() {
        $sql = "DELETE FROM clipboard_records";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute();
    }
}
?>