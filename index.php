<?php
require_once 'config.php';
require_once 'classes/AuthManager.php';

$existing_content = "";
try {
    require_once 'classes/HistoryManager.php';
    $historyManager = new HistoryManager();
    $records = $historyManager->getRecords();
    
    if (!empty($records)) {
        $latest_record = reset($records);
        $existing_content = htmlspecialchars($latest_record['content']);
    }
} catch (Exception $e) {
    error_log('Index.php error: ' . $e->getMessage());
}

// 检查是否已认证，决定是否需要密码输入
$isAuthenticated = AuthManager::isAuthenticated();
$csrf_token = $isAuthenticated ? AuthManager::getCSRFToken() : generateCSRFToken();
$page_title = 'Clipboard';
$additional_head_content = '
    <script src="js/common.js"></script>
    <script src="js/clipboard.js"></script>
    <script>
        ClipboardApp.init({
            isAuthenticated: ' . ($isAuthenticated ? 'true' : 'false') . ',
            csrfToken: "' . $csrf_token . '",
            maxContentLength: ' . MAX_CONTENT_LENGTH . ',
            baseUrl: "' . BASE_URL . '",
            submitUrl: "api/submit.php"
        });
    </script>';
require_once 'includes/header.php';
?>

<textarea class="clipboard-display" id="clipboard-display" placeholder="剪切板为空！" readonly><?php echo $existing_content; ?></textarea>
<textarea class="clipboard-input" id="content" placeholder="请输入要复制的内容" maxlength="<?php echo MAX_CONTENT_LENGTH; ?>"></textarea>
<nav class="submit-nav">
    <ul>
        <li data-action="submit">
            提交
            <span></span><span></span><span></span><span></span>
        </li>
        <a href="<?php echo BASE_URL; ?>/history.php">
            <li>
                历史记录
                <span></span><span></span><span></span><span></span>
            </li>
        </a>
    </ul>
</nav>

<?php require_once 'includes/footer.php'; ?>
