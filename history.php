<?php
require_once 'config.php';
require_once __DIR__ . '/classes/AuthManager.php';
require_once __DIR__ . '/classes/HistoryManager.php';

// 处理AJAX删除和清空请求
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && ($_POST['action'] === 'delete' || $_POST['action'] === 'clear_all')) {
    header('Content-Type: application/json; charset=utf-8');
    
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
        
        $historyManager = new HistoryManager();
        
        // 处理清空所有记录
        if ($_POST['action'] === 'clear_all') {
            $beforeCount = $historyManager->getRecordCount();
            if ($historyManager->clearAllRecords()) {
                echo json_encode([
                    'success' => true, 
                    'message' => '历史记录已清空',
                    'cleared_count' => $beforeCount
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => '清空失败']);
            }
        }
        // 处理删除单条记录
        else if ($_POST['action'] === 'delete') {
            // 验证记录ID
            if (!isset($_POST['record_id']) || !is_numeric($_POST['record_id'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => '无效的记录ID']);
                exit;
            }
            
            $recordId = (int)$_POST['record_id'];
            
            // 检查记录是否存在
            if (!$historyManager->recordExists($recordId)) {
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'message' => '记录不存在'
                ]);
                exit;
            }
            
            // 执行删除
            if ($historyManager->deleteRecord($recordId)) {
                echo json_encode(['success' => true, 'message' => '删除成功']);
            } else {
                echo json_encode(['success' => false, 'message' => '删除失败']);
            }
        }
        
    } catch (Exception $e) {
        error_log('Delete error: ' . $e->getMessage());
        echo json_encode(['success' => false, 'message' => '服务器错误']);
    }
    exit;
}

// 处理登录请求
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password'])) {
    $result = AuthManager::handleLogin($_POST['password']);
    if (!$result['success']) {
        $errorMessage = $result['message'];
    }
}

// 检查是否已认证
if (AuthManager::isAuthenticated()) {
    // 创建历史记录管理器
    $historyManager = new HistoryManager();

    // 获取分页数据（首页）
    $perPage = 20;
    $result = $historyManager->getRecordsPaginated(1, $perPage);
    $records = [];
    // 转换为以ID为键的格式（兼容旧模板）
    foreach ($result['records'] as $record) {
        $records[$record['id']] = $record;
    }
    $pagination = $result['pagination'];
    $csrfToken = AuthManager::getCSRFToken();

    // 包含历史记录显示模板
    include __DIR__ . '/templates/history_view.php';
    exit;
}

// 显示登录表单
include __DIR__ . '/templates/auth_form.php';
?>