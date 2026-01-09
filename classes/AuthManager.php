<?php
require_once dirname(__DIR__) . '/config.php';

class AuthManager {
    private static $sessionKey = 'clipboard_authenticated';
    private static $sessionTimeout = 'clipboard_auth_time';
    private static $loginAttemptsKey = 'clipboard_login_attempts';
    private static $lockoutTimeKey = 'clipboard_lockout_time';

    // 登录保护配置
    const MAX_LOGIN_ATTEMPTS = 5;      // 最大尝试次数
    const LOCKOUT_DURATION = 300;      // 锁定时间（秒）
    
    /**
     * 验证密码（使用 password_verify 验证哈希）
     */
    public static function verifyPassword($password) {
        if (empty(APP_PASSWORD_HASH)) {
            return false;
        }
        return password_verify($password, APP_PASSWORD_HASH);
    }
    
    /**
     * 设置认证状态
     */
    public static function setAuthenticated($authenticated = true) {
        // 防止会话固定攻击：认证成功时重新生成会话ID
        if ($authenticated) {
            session_regenerate_id(true);
            // 清除登录尝试计数
            unset($_SESSION[self::$loginAttemptsKey]);
            unset($_SESSION[self::$lockoutTimeKey]);
        }

        $_SESSION[self::$sessionKey] = $authenticated;
        $_SESSION[self::$sessionTimeout] = time();

        // 生成新的CSRF token
        generateCSRFToken();
    }
    
    /**
     * 检查是否已认证
     */
    public static function isAuthenticated() {
        if (!isset($_SESSION[self::$sessionKey]) || !$_SESSION[self::$sessionKey]) {
            return false;
        }
        
        // 检查会话是否超时
        if (isset($_SESSION[self::$sessionTimeout])) {
            $elapsed = time() - $_SESSION[self::$sessionTimeout];
            if ($elapsed > SESSION_TIMEOUT) {
                self::logout();
                return false;
            }
            
            // 更新最后访问时间
            $_SESSION[self::$sessionTimeout] = time();
        }
        
        return true;
    }
    
    /**
     * 检查是否被锁定
     */
    public static function isLockedOut() {
        if (isset($_SESSION[self::$lockoutTimeKey])) {
            $lockoutTime = $_SESSION[self::$lockoutTimeKey];
            if (time() - $lockoutTime < self::LOCKOUT_DURATION) {
                return true;
            }
            // 锁定已过期，清除
            unset($_SESSION[self::$lockoutTimeKey]);
            unset($_SESSION[self::$loginAttemptsKey]);
        }
        return false;
    }

    /**
     * 获取剩余锁定时间
     */
    public static function getLockoutRemaining() {
        if (isset($_SESSION[self::$lockoutTimeKey])) {
            $remaining = self::LOCKOUT_DURATION - (time() - $_SESSION[self::$lockoutTimeKey]);
            return max(0, $remaining);
        }
        return 0;
    }

    /**
     * 记录失败的登录尝试
     */
    private static function recordFailedAttempt() {
        if (!isset($_SESSION[self::$loginAttemptsKey])) {
            $_SESSION[self::$loginAttemptsKey] = 0;
        }
        $_SESSION[self::$loginAttemptsKey]++;

        // 达到最大尝试次数，锁定账户
        if ($_SESSION[self::$loginAttemptsKey] >= self::MAX_LOGIN_ATTEMPTS) {
            $_SESSION[self::$lockoutTimeKey] = time();
        }
    }

    /**
     * 获取剩余尝试次数
     */
    public static function getRemainingAttempts() {
        $attempts = $_SESSION[self::$loginAttemptsKey] ?? 0;
        return max(0, self::MAX_LOGIN_ATTEMPTS - $attempts);
    }

    /**
     * 处理登录请求
     */
    public static function handleLogin($password) {
        // 检查是否被锁定
        if (self::isLockedOut()) {
            $remaining = self::getLockoutRemaining();
            return [
                'success' => false,
                'message' => "登录已被锁定，请在 {$remaining} 秒后重试",
                'locked' => true,
                'lockout_remaining' => $remaining
            ];
        }

        if (self::verifyPassword($password)) {
            self::setAuthenticated(true);
            return ['success' => true, 'message' => '认证成功'];
        }

        // 记录失败尝试
        self::recordFailedAttempt();
        $remaining = self::getRemainingAttempts();

        if ($remaining > 0) {
            return [
                'success' => false,
                'message' => "密码错误，还剩 {$remaining} 次尝试机会"
            ];
        } else {
            $lockTime = self::LOCKOUT_DURATION;
            return [
                'success' => false,
                'message' => "尝试次数过多，账户已锁定 {$lockTime} 秒",
                'locked' => true
            ];
        }
    }
    
    /**
     * 登出
     */
    public static function logout() {
        unset($_SESSION[self::$sessionKey]);
        unset($_SESSION[self::$sessionTimeout]);
        unset($_SESSION[CSRF_TOKEN_NAME]);
    }
    
    /**
     * 验证CSRF token
     */
    public static function validateCSRF($token) {
        if (!self::isAuthenticated()) {
            return false;
        }
        return validateCSRFToken($token);
    }
    
    /**
     * 获取CSRF token
     */
    public static function getCSRFToken() {
        if (self::isAuthenticated()) {
            return generateCSRFToken();
        }
        return null;
    }
    
    /**
     * 检查是否需要重新认证
     */
    public static function requiresReauth() {
        return !self::isAuthenticated();
    }
    
    /**
     * 获取剩余会话时间（秒）
     */
    public static function getRemainingTime() {
        if (!self::isAuthenticated()) {
            return 0;
        }
        
        if (isset($_SESSION[self::$sessionTimeout])) {
            $elapsed = time() - $_SESSION[self::$sessionTimeout];
            return max(0, SESSION_TIMEOUT - $elapsed);
        }
        
        return SESSION_TIMEOUT;
    }
}
?>