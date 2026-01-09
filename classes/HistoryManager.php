<?php
require_once dirname(__DIR__) . '/config.php';
require_once __DIR__ . '/DatabaseManager.php';

class HistoryManager {
    private $db;

    // 默认分页配置
    const DEFAULT_PER_PAGE = 20;

    public function __construct() {
        $this->db = DatabaseManager::getInstance();
    }

    /**
     * 转换数据库记录为应用格式
     * @param array $dbRecord 数据库记录
     * @return array 应用格式记录
     */
    private function transformRecord($dbRecord) {
        return [
            'id' => $dbRecord['id'],
            'timestamp' => $dbRecord['created_at'],
            'content' => $dbRecord['content']
        ];
    }

    /**
     * 批量转换记录
     * @param array $dbRecords 数据库记录数组
     * @param bool $useIdAsKey 是否使用ID作为键
     * @return array 转换后的记录数组
     */
    private function transformRecords($dbRecords, $useIdAsKey = false) {
        $records = [];
        foreach ($dbRecords as $record) {
            $transformed = $this->transformRecord($record);
            if ($useIdAsKey) {
                $records[$record['id']] = $transformed;
            } else {
                $records[] = $transformed;
            }
        }
        return $records;
    }

    /**
     * 获取所有历史记录
     * @return array 以ID为键的记录数组
     */
    public function getRecords() {
        $dbRecords = $this->db->getAllRecords();
        return $this->transformRecords($dbRecords, true);
    }

    /**
     * 获取分页历史记录
     * @param int $page 页码（从1开始）
     * @param int $perPage 每页数量
     * @return array 包含记录和分页信息的数组
     */
    public function getRecordsPaginated($page = 1, $perPage = null) {
        $perPage = $perPage ?? self::DEFAULT_PER_PAGE;
        $page = max(1, (int)$page);
        $offset = ($page - 1) * $perPage;

        $dbRecords = $this->db->getRecordsPaginated($offset, $perPage);
        $total = $this->db->getRecordCount();

        return [
            'records' => $this->transformRecords($dbRecords, false),
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'total_pages' => ceil($total / $perPage),
                'has_more' => ($offset + count($dbRecords)) < $total
            ]
        ];
    }

    /**
     * 删除指定记录
     * @param int $recordId 记录ID
     * @return bool 是否成功
     */
    public function deleteRecord($recordId) {
        return $this->db->deleteRecord($recordId);
    }

    /**
     * 获取记录总数
     * @return int 记录总数
     */
    public function getRecordCount() {
        return $this->db->getRecordCount();
    }

    /**
     * 检查记录是否存在
     * @param int $recordId 记录ID
     * @return bool 是否存在
     */
    public function recordExists($recordId) {
        return $this->db->recordExists($recordId);
    }

    /**
     * 获取单条记录
     * @param int $recordId 记录ID
     * @return array|null 记录数据或null
     */
    public function getRecord($recordId) {
        $dbRecord = $this->db->getRecordById($recordId);
        if (!$dbRecord) {
            return null;
        }
        return $this->transformRecord($dbRecord);
    }

    /**
     * 添加新记录
     * @param string $content 记录内容
     * @return bool 是否成功
     */
    public function addRecord($content) {
        return $this->db->insertRecord($content);
    }

    /**
     * 清空所有历史记录
     * @return bool 是否成功
     */
    public function clearAllRecords() {
        return $this->db->clearAllRecords();
    }
}
?>
