<?php
// 加载 .env 文件
function loadEnv($path) {
    if (!file_exists($path)) return;
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') === false) continue;
        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);
        if (!getenv($name)) {
            putenv("$name=$value");
        }
    }
}

loadEnv(__DIR__ . '/.env');

// 应用配置（从环境变量读取敏感配置）
define('APP_PASSWORD_HASH', getenv('APP_PASSWORD_HASH', ''));
define('MAX_CONTENT_LENGTH', 10000); // 最大内容长度（字符）
define('DATABASE_FILE', getenv('DATABASE_FILE', 'clipboard.db'));
define('BASE_URL', getenv('BASE_URL', '/clipboard'));

// 安全配置
define('CSRF_TOKEN_NAME', 'csrf_token');
define('SESSION_TIMEOUT', 604800); // 会话超时时间（秒）= 7天
define('SESSION_COOKIE_LIFETIME', 604800); // Cookie 保持时间（秒）= 7天

// 文件权限
define('FILE_PERMISSIONS', 0644);

// 启用会话
if (session_status() === PHP_SESSION_NONE) {
    // 设置 Session Cookie 参数：7天有效期 + 安全选项
    session_set_cookie_params([
        'lifetime' => SESSION_COOKIE_LIFETIME,
        'path' => '/',
        'httponly' => true,    // 防止 XSS 读取 Cookie
        'samesite' => 'Lax'    // 防止 CSRF 攻击
    ]);
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