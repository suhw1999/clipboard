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
    <script>
        const isAuthenticated = ' . ($isAuthenticated ? 'true' : 'false') . ';
        
        function submitContent() {
            const content = document.getElementById(\'content\').value;
            
            if (!content.trim()) {
                alert("内容不能为空！");
                return;
            }
            
            if (content.length > ' . MAX_CONTENT_LENGTH . ') {
                alert("内容长度超出限制（最大' . MAX_CONTENT_LENGTH . '字符）！");
                return;
            }
            
            const formData = new FormData();
            formData.append(\'copy\', content);
            formData.append(\'' . CSRF_TOKEN_NAME . '\', \'' . $csrf_token . '\');
            
            // 如果未认证，需要输入密码
            if (!isAuthenticated) {
                const password = prompt("请输入密码：");
                if (!password) {
                    alert("验证失败。");
                    return;
                }
                formData.append(\'password\', password);
            }

            fetch(\'api/submit.php\', {
                method: \'POST\',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.href = \'' . BASE_URL . '\';
                } else {
                    alert(data.message);
                }
            })
            .catch(error => {
                console.error(\'Error:\', error);
                alert(\'提交失败，请稍后重试。\');
            });
        }

        document.addEventListener(\'DOMContentLoaded\', function() {
            const contentInput = document.getElementById(\'content\');
            
            // 自动聚焦到输入框
            contentInput.focus();
            
            contentInput.addEventListener(\'keydown\', function(event) {
                if (event.shiftKey && event.key === \'Enter\') {
                    event.preventDefault();
                    submitContent();
                }
            });
        });
    </script>';
require_once 'includes/header.php';
?>

<textarea style="margin-bottom: 0px;" placeholder="剪切板为空！" readonly><?php echo $existing_content; ?></textarea>
<textarea style="margin-bottom: 0px;" id="content" placeholder="请输入要复制的内容" maxlength="<?php echo MAX_CONTENT_LENGTH; ?>"></textarea>
<nav class="submit-nav">
    <ul>
        <li onclick="submitContent()" style="margin-bottom: 0px;">
            提交
            <span></span><span></span><span></span><span></span>
        </li>
    </ul>
</nav>

<nav>
    <ul>
        <a href="<?php echo BASE_URL; ?>/history.php">
            <li>
                历史记录
                <span></span><span></span><span></span><span></span>
            </li>
        </a>
    </ul>
</nav>

<?php require_once 'includes/footer.php'; ?>
