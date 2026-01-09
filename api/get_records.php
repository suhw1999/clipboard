<?php
/**
 * 分页获取历史记录 API
 *
 * GET /api/get_records.php?page=1&per_page=20
 *
 * Response:
 * {
 *   "success": true,
 *   "records": [...],
 *   "pagination": {
 *     "current_page": 1,
 *     "per_page": 20,
 *     "total": 150,
 *     "total_pages": 8,
 *     "has_more": true
 *   }
 * }
 */

require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/classes/AuthManager.php';
require_once dirname(__DIR__) . '/classes/HistoryManager.php';

header('Content-Type: application/json; charset=utf-8');

try {
    // 仅允许 GET 请求
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => '仅允许 GET 请求']);
        exit;
    }

    // 检查身份验证
    if (!AuthManager::isAuthenticated()) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => '未授权访问']);
        exit;
    }

    // 获取分页参数
    $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    $perPage = isset($_GET['per_page']) ? min(100, max(1, (int)$_GET['per_page'])) : 20;

    // 获取分页数据
    $historyManager = new HistoryManager();
    $result = $historyManager->getRecordsPaginated($page, $perPage);

    echo json_encode([
        'success' => true,
        'records' => $result['records'],
        'pagination' => $result['pagination']
    ]);

} catch (Exception $e) {
    error_log('Get records API error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => '服务器错误']);
}
?>
