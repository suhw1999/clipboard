<?php
require_once dirname(__DIR__) . '/config.php';
require_once __DIR__ . '/DatabaseManager.php';

class HistoryManager {
    private $db;
    
    public function __construct() {
        $this->db = DatabaseManager::getInstance();
    }
    
    /**
     * 获取所有历史记录
     */
    public function getRecords() {
        $dbRecords = $this->db->getAllRecords();
        $records = [];
        
        foreach ($dbRecords as $record) {
            $records[$record['id']] = [
                'id' => $record['id'],
                'timestamp' => $record['created_at'],
                'content' => $record['content']
            ];
        }
        
        return $records;
    }
    
    /**
     * 删除指定记录
     */
    public function deleteRecord($recordId) {
        return $this->db->deleteRecord($recordId);
    }
    
    /**
     * 获取记录总数
     */
    public function getRecordCount() {
        return $this->db->getRecordCount();
    }
    
    /**
     * 检查记录是否存在
     */
    public function recordExists($recordId) {
        return $this->db->recordExists($recordId);
    }
    
    /**
     * 获取单条记录
     */
    public function getRecord($recordId) {
        $dbRecord = $this->db->getRecordById($recordId);
        if (!$dbRecord) {
            return null;
        }
        
        return [
            'id' => $dbRecord['id'],
            'timestamp' => $dbRecord['created_at'],
            'content' => $dbRecord['content']
        ];
    }
    
    /**
     * 添加新记录
     */
    public function addRecord($content) {
        return $this->db->insertRecord($content);
    }
    
    /**
     * 清空所有历史记录
     */
    public function clearAllRecords() {
        return $this->db->clearAllRecords();
    }
}
?>