<?php
// 安全配置文件 - 请确保此文件不能通过Web访问
// 建议将此文件放在Web根目录之外，或使用.htaccess保护

// 应用配置
define('APP_PASSWORD', '####your_passwd####'); // 请修改为更强的密码
define('MAX_CONTENT_LENGTH', 10000); // 最大内容长度（字符）
define('CLIPBOARD_FILE', 'copy.txt');
define('HISTORY_FILE', 'history.txt');
define('DATABASE_FILE', 'clipboard.db');
define('BASE_URL', 'https://suhw1999.cn/clipboard');

// 安全配置
define('CSRF_TOKEN_NAME', 'csrf_token');
define('SESSION_TIMEOUT', 3600); // 会话超时时间（秒）

// 文件权限
define('FILE_PERMISSIONS', 0644);

// 启用会话
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// CSRF Token生成函数
function generateCSRFToken() {
    if (!isset($_SESSION[CSRF_TOKEN_NAME])) {
        $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
    }
    return $_SESSION[CSRF_TOKEN_NAME];
}

// CSRF Token验证函数
function validateCSRFToken($token) {
    return isset($_SESSION[CSRF_TOKEN_NAME]) && 
           hash_equals($_SESSION[CSRF_TOKEN_NAME], $token);
}

// 输入验证函数
function validateInput($content) {
    if (empty($content)) {
        return ['valid' => false, 'error' => '内容不能为空'];
    }
    
    if (strlen($content) > MAX_CONTENT_LENGTH) {
        return ['valid' => false, 'error' => '内容长度超出限制（最大' . MAX_CONTENT_LENGTH . '字符）'];
    }
    
    return ['valid' => true, 'error' => null];
}

// 安全文件写入函数
function secureFileWrite($filename, $content, $append = false) {
    $flags = $append ? FILE_APPEND | LOCK_EX : LOCK_EX;
    
    if (file_put_contents($filename, $content, $flags) === false) {
        return false;
    }
    
    chmod($filename, FILE_PERMISSIONS);
    return true;
}
?>