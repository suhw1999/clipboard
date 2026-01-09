<?php
require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/classes/AuthManager.php';
require_once dirname(__DIR__) . '/classes/HistoryManager.php';

header('Content-Type: application/json; charset=utf-8');

try {
    // 验证CSRF Token
    if (!isset($_POST[CSRF_TOKEN_NAME]) || !validateCSRFToken($_POST[CSRF_TOKEN_NAME])) {
        echo json_encode(["success" => false, "message" => "安全验证失败，请刷新页面重试。"]);
        exit();
    }

    // 检查身份验证状态
    if (!AuthManager::isAuthenticated()) {
        // 未认证，需要验证密码
        if (!isset($_POST['password'])) {
            echo json_encode(["success" => false, "message" => "需要输入密码进行验证。"]);
            exit();
        }

        $authResult = AuthManager::handleLogin($_POST['password']);
        if (!$authResult['success']) {
            echo json_encode($authResult);
            exit();
        }
        // 密码验证成功，session已设置，继续处理
    }

    // 验证输入内容
    $content = $_POST['copy'] ?? '';
    $validation = validateInput($content);
    
    if (!$validation['valid']) {
        echo json_encode(["success" => false, "message" => $validation['error']]);
        exit();
    }

    // 过滤和清理输入内容
    $content = trim($content);
    // 移除控制字符，保留换行和制表符
    $content = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $content);

    // 写入数据库
    $historyManager = new HistoryManager();
    if (!$historyManager->addRecord($content)) {
        throw new Exception("写入数据库失败");
    }

    echo json_encode(["success" => true, "message" => "提交成功！"]);

} catch (Exception $e) {
    error_log("Clipboard error: " . $e->getMessage());
    echo json_encode(["success" => false, "message" => "服务器错误，请稍后重试。"]);
}
?>