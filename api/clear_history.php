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
    if (empty($_POST['action']) || $_POST['action'] !== 'clear_all') {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => '无效的操作']);
        exit;
    }
    
    // 创建历史记录管理器
    $historyManager = new HistoryManager();
    
    // 获取清空前的记录数量
    $beforeCount = $historyManager->getRecordCount();
    
    // 执行清空操作
    if ($historyManager->clearAllRecords()) {
        echo json_encode([
            'success' => true, 
            'message' => '历史记录已清空',
            'cleared_count' => $beforeCount
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => '清空失败，请重试']);
    }
    
} catch (Exception $e) {
    error_log('Clear history error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => '服务器内部错误']);
}
?>