<?php
require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/classes/AuthManager.php';
require_once dirname(__DIR__) . '/classes/HistoryManager.php';

// 设置响应头
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, must-revalidate');

// 只允许POST请求
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => '仅允许POST请求']);
    exit;
}

try {
    // 检查身份验证
    if (!AuthManager::isAuthenticated()) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => '未授权访问']);
        exit;
    }
    
    // 验证CSRF token
    $csrfToken = $_POST['csrf_token'] ?? '';
    if (!AuthManager::validateCSRF($csrfToken)) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'CSRF验证失败']);
        exit;
    }
    
    // 验证请求参数
    if (empty($_POST['action']) || $_POST['action'] !== 'delete') {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => '无效的操作']);
        exit;
    }
    
    if (!isset($_POST['record_id']) || !is_numeric($_POST['record_id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => '无效的记录ID', 'received_id' => $_POST['record_id'] ?? 'null']);
        exit;
    }
    
    $recordId = (int)$_POST['record_id'];
    
    // 创建历史记录管理器
    $historyManager = new HistoryManager();
    
    // 检查记录是否存在
    if (!$historyManager->recordExists($recordId)) {
        http_response_code(404);
        echo json_encode([
            'success' => false, 
            'message' => '记录不存在', 
            'record_id' => $recordId,
            'total_records' => $historyManager->getRecordCount(),
            'available_ids' => array_keys($historyManager->getRecords())
        ]);
        exit;
    }
    
    // 执行删除操作
    if ($historyManager->deleteRecord($recordId)) {
        echo json_encode([
            'success' => true, 
            'message' => '删除成功',
            'remaining_count' => $historyManager->getRecordCount()
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => '删除失败，请重试']);
    }
    
} catch (Exception $e) {
    error_log('Delete record error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => '服务器内部错误']);
}
?>